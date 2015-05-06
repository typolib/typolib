<?php
namespace Typolib;

use Bit3\GitPhp\GitException;
use Bit3\GitPhp\GitRepository;
use Github\Client;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Transvision\Strings;

/**
 * PullRequest class
 *
 * This class provides methods to clone a Git repository, create a new branch,
 * commit changes, push them to a remote branch on a fork, then create a
 * Pull-Request to the original repo.
 *
 * @package Typolib
 */
class PullRequest
{
    public $repo;
    public $repo_url;
    public $directory;
    public $config_file;
    public $user_config;
    public $typolib_remote = 'origin';
    public $client = null;
    public $client_remote = 'github';
    public $client_remote_url;
    public $branch;
    public $branch_prefix = 'typolib-';
    public $commit_msg;
    private $git;
    private $logger;

    /**
     * Constructor initializes all the arguments then call the method to clone
     * and setup the Git repo.
     *
     * @param String $commit_msg The message that will become the commit message
     *                           and the Pull-Request title.
     */
    public function __construct($commit_msg)
    {
        $this->commit_msg = $commit_msg;
        $this->branch = isset($branch) ? $branch : '';
        $this->repo = isset($repo) ? $repo : RULES_REPO;

        $this->repo_url = 'https://github.com/' . urlencode(TYPOLIB_GITHUB_ACCOUNT)
                        . '/' . $this->repo . '.git';

        $this->directory = DATA_ROOT . $this->repo . '/';
        $this->config_file = $this->directory . '.git/config';

        #FIXME: edit the config with GitPhp, allow custom email/name per Pull Request
        $this->user_config = "[user]\n"
                           . '    email = ' . CLIENT_GITHUB_EMAIL . "\n"
                           . '    name = ' . CLIENT_GITHUB_COMMITTER . "\n";

        $this->client_remote_url = 'https://' . urlencode(CLIENT_GITHUB_ACCOUNT)
                                 . ':' . urlencode(CLIENT_GITHUB_PASSWORD)
                                 . '@github.com/' . urlencode(CLIENT_GITHUB_ACCOUNT)
                                 . '/' . $this->repo . '.git';

        // We use the Monolog library to log our events
        $this->logger = new Logger('PullRequest');
        $this->logger->pushHandler(new StreamHandler(INSTALL_ROOT . 'logs/pr-errors.log'));

        // Also log to error console in Debug mode
        if (DEBUG) {
            $this->logger->pushHandler(new ErrorLogHandler());
        }

        try {
            $this->git = new GitRepository($this->directory);
        } catch (GitException $e) {
            $this->logger->error('Failed to initialize Git repository. Error: '
                                 . $e->getMessage());
        }
        $this->cloneAndConfig();
    }

    /**
     *  Forks the repo into client’s Github account if it doesn’t exists
     */
    private function fork()
    {
        if ($this->client == null) {
            $this->authenticateClient();
        }

        // Check if it's already there
        $repos = $this->client->api('user')->repositories(urlencode(CLIENT_GITHUB_ACCOUNT));
        $forked = false;

        foreach ($repos as $repo) {
            if ($repo['name'] == $this->repo && $repo['fork'] == true) {
                $forked = true;
                break;
            }
        }

        if (! $forked) {
            // Do the fork
            $this->client->api('repo')->forks()->create(
                urlencode(TYPOLIB_GITHUB_ACCOUNT),
                $this->repo
            );
        }
    }

    /**
     *  Clone and setup a fresh Git repo if the folder is empty.
     */
    private function cloneAndConfig()
    {
        if (! is_dir($this->directory)) {
            try {
                // First, make sure we have a fork
                $this->fork();

                $this->git->cloneRepository()->execute($this->repo_url);
                $this->git->remote()->add(
                                        $this->client_remote,
                                        $this->client_remote_url
                                    )->execute();

                $this->git->fetch()->execute($this->client_remote);

                if (! file_put_contents($this->config_file,
                                        $this->user_config, FILE_APPEND)) {
                    $this->logger->error('Can\'t write Git config file');
                }
            } catch (GitException $e) {
                $this->logger->error('Failed to clone or config Git repository. '
                                   . 'Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Pulls latest changes from client remote master branch
     */
    public function updateMaster()
    {
        $this->git->fetch()->execute($this->typolib_remote, 'master');
        $this->git->checkout()->execute($this->typolib_remote . '/master');
    }

    /**
     * Returns the latest commit SHA from master remote branch.
     */
    public function getMasterSha()
    {
        if ($this->client == null) {
            $this->authenticateClient();
        }

        //Get SHA
        $sha = $this->client->api('repo')->commits()->all(
            urlencode(TYPOLIB_GITHUB_ACCOUNT),
            $this->repo,
            ['sha' => 'master'])[0]['sha'];

        return $sha;
    }

    /**
     * Creates a new branch name using the prefix and checks if the branch
     * already exists on the remote repo.
     */
    private function generateBranchName()
    {
        $client_remote = $this->client_remote;
        $branch_prefix = $this->branch_prefix;
        $remotes = $this->git->branch()->remotes()->getNames();
        $remotes = array_filter(
            $remotes,
            function ($name) use ($client_remote, $branch_prefix) {
                return Strings::startsWith($name, $client_remote . '/'
                                                              . $branch_prefix);
            }
        );

        rsort($remotes);
        if (isset($remotes[0])) {
            $last_branch_number = substr(
                                    $remotes[0],
                                    strlen($client_remote . '/' . $branch_prefix)
                                );
        }

        $i = $last_branch_number > 0 ? $last_branch_number : 1;
        do {
            $branch = $branch_prefix . $i;
            $i++;
        } while (in_array($client_remote . '/' . $branch, $remotes));

        $this->branch = $branch;
    }

    /**
     * Updates master branch from origin then creates a new branch using
     * generateBranchName() ensuring the branch doesn't already exists both
     * locally and remotely.
     * Once the branch is created, we push right away to remote to avoid an other
     * PullRequest instance creates the same branch before we invoke
     * commitAndPush().
     */
    public function createNewBranch()
    {
        try {
            // Pull latest changes on master branch
            $this->updateMaster();

            // Generate the name after we fetch from remote to get all branches.
            $this->git->fetch()->execute($this->client_remote);
            $this->generateBranchName();

            // Remove branches both remotely and locally
            $remotes = $this->git->branch()->remotes()->getNames();
            if (in_array($this->client_remote . '/' . $this->branch, $remotes)) {
                $this->git->push()->execute($this->client_remote, ':' . $this->branch);
            }
            if (in_array($this->branch, $this->git->branch()->getNames())) {
                $this->git->branch()->delete()->execute($this->branch);
            }

            // Create a fresh branch
            $this->git->branch()->execute($this->branch);
            $this->git->checkout()->execute($this->branch);

            // Push to create the branch remotely
            $this->git->push()->execute($this->client_remote, $this->branch);
        } catch (GitException $e) {
            $this->logger->error('Failed to create a new branch. Error: '
                                 . $e->getMessage());
        }
    }

    /**
     * Commits all the changes made to the local clone on the current branch
     * then push the commit to the $client_remote remote.
     * Requires creating a new branch first using createNewBranch().
     */
    public function commitAndPush()
    {
        try {
            // Add files to git index, commit and push to client remote
            $this->git->add()->all()->execute();
            $this->git->commit()->message($this->commit_msg)->execute();
            $this->git->push()->execute($this->client_remote, $this->branch);
        } catch (GitException $e) {
            $this->logger->error('Failed to commit to Git repository. Error: '
                                 . $e->getMessage());
        }

        $this->createPullRequest();
    }

    /**
     * Client Github account authentication, so that we can use the API
     */
    private function authenticateClient()
    {
        $username = urlencode(CLIENT_GITHUB_ACCOUNT);
        $password = urlencode(CLIENT_GITHUB_PASSWORD);

        $client = new Client();

        //Authentification to the client GitHub account
        $client->authenticate($username, $password);

        $this->client = $client;
    }

    /**
     * Creates a pull request using the current branch committed and pushed
     * Requires authentication to the client GitHub account.
     */
    private function createPullRequest()
    {
        if ($this->client == null) {
            $this->authenticateClient();
        }

        //Creates the pull request
        $this->client->api('pull_request')->create(
            urlencode(TYPOLIB_GITHUB_ACCOUNT), $this->repo, [
            'base'  => 'master',
            'head'  => $this->repo . ':' . $this->branch,
            'title' => $this->commit_msg,
            'body'  => '',
        ]);
    }
}
