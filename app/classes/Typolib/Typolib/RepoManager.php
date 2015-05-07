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
 * RepoManager class
 *
 * This class provides methods to manage a Git repository: fork, clone, setup,
 * create a new branch, commit changes, push them to a remote branch on the fork, then create a
 * Pull-Request to the original repo. But it can also check for updates.
 *
 * @package Typolib
 */
class RepoManager
{
    private $repo;
    private $repo_url;
    private $path;
    private $update_file;
    private $config_file;
    private $user_config;
    private $our_remote = 'origin';
    private $client = null;
    private $client_remote = 'github';
    private $remote_url;
    private $branch;
    private $branch_prefix = 'typolib-';
    private $commit_msg;
    private $git;
    private $logger;

    /**
     * Constructor that initialize all the arguments then call the method to clone
     * and setup the Git repo.
     *
     * @param array $args Array containing one or more class attributes, like for instance:
     *                    ['repo' => 'typolib', 'path' => '~/typolib/data/typolib/']
     */
    public function __construct(array $args = null)
    {
        $this->repo  = isset($args['repo']) ? $args['repo'] : RULES_REPO;

        $github_url  = 'https://github.com/' . urlencode(TYPOLIB_GITHUB_ACCOUNT)
                     . '/' . $this->repo . '.git';
        $remote_url  = 'https://' . urlencode(CLIENT_GITHUB_ACCOUNT)
                     . ':' . urlencode(CLIENT_GITHUB_PASSWORD)
                     . '@github.com/' . urlencode(CLIENT_GITHUB_ACCOUNT)
                     . '/' . $this->repo . '.git';
        $path        = DATA_ROOT . $this->repo . '/';
        $update_file = DATA_ROOT . 'lastupdate.txt';

        $this->branch      = isset($args['branch'])      ? $args['branch']      : '';
        $this->repo_url    = isset($args['repo_url'])    ? $args['repo_url']    : $github_url;
        $this->remote_url  = isset($args['remote_url'])  ? $args['remote_url']  : $remote_url;
        $this->path        = isset($args['path'])        ? $args['path']        : $path;
        $this->update_file = isset($args['update_file']) ? $args['update_file'] : $update_file;

        $this->config_file = $this->path . '.git/config';

        // FIXME: edit the config with GitPhp, allow custom email/name per Pull Request
        if (isset($args['email']) && isset($args['committer'])) {
            $email      = $args['email'];
            $committer  = $args['committer'];
        } else {
            $email      = CLIENT_GITHUB_EMAIL;
            $committer  = CLIENT_GITHUB_COMMITTER;
        }
        $this->user_config = "[user]\n"
                           . '    email = ' . $email . "\n"
                           . '    name = ' . $committer . "\n";

        // We use the Monolog library to log our events
        $this->logger = new Logger('RepoManager');
        $this->logger->pushHandler(new StreamHandler(INSTALL_ROOT . 'logs/repo-errors.log'));

        // Also log to error console in Debug mode
        if (DEBUG) {
            $this->logger->pushHandler(new ErrorLogHandler());
        }

        try {
            $this->git = new GitRepository($this->path);
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
        if (! is_dir($this->path)) {
            try {
                // First, make sure we have a fork
                $this->fork();

                $this->git->cloneRepository()->execute($this->repo_url);
                $this->git->remote()->add(
                                        $this->client_remote,
                                        $this->remote_url
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
     *
     * @return String $sha SHA of the latest commit on master branch that we've
     *                just updated.
     */
    public function updateMaster()
    {
        $this->git->fetch()->execute($this->our_remote, 'master');
        $this->git->checkout()->execute($this->our_remote . '/master');
        $sha = $this->getMasterSha();
        if (! file_put_contents($sha, $this->update_file)) {
            $this->logger->error('Can\'t write ' . $this->update_file . ' file');

            return false;
        }

        return $sha;
    }

    /**
     * Get the latest commit SHA from master remote branch.
     *
     * @return String $sha SHA of latest available commit, or false if we've not
     *                been able to retrieve it.
     */
    private function getMasterSha()
    {
        if ($this->client == null) {
            $this->authenticateClient();
        }

        //Get SHA
        $sha = $this->client->api('repo')->commits()->all(
                    urlencode(TYPOLIB_GITHUB_ACCOUNT),
                    $this->repo,
                    ['sha' => 'master']
                )[0]['sha'];

        return $sha || false;
    }

    /**
     * Check if our local clone is up-to-date
     *
     * @return boolean Returns true if our clone is up-to-date, false otherwise.
     */
    public function checkForUpdates()
    {
        $local_sha = file_get_contents($this->update_file);
        $remote_sha = $this->getMasterSha();

        if ($local_sha != $remote_sha) {
            $this->updateMaster();

            return true;
        }

        return false;
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
     * RepoManager instance creates the same branch before we invoke
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
     *
     * @param String $commit_msg The message that will become the commit message
     *                           and the Pull-Request title.
     */
    public function commitAndPush($commit_msg)
    {
        $this->commit_msg = $commit_msg;
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
     * Creates a pull request using the current branch committed and pushed.
     * Requires authentication to the client GitHub account.
     */
    private function createPullRequest()
    {
        if ($this->client == null) {
            $this->authenticateClient();
        }

        //Creates the pull request
        $this->client->api('pull_request')->create(
            urlencode(TYPOLIB_GITHUB_ACCOUNT),
            $this->repo,
            [
                'base'  => 'master',
                'head'  => $this->repo . ':' . $this->branch,
                'title' => $this->commit_msg,
                'body'  => '',
            ]
        );
    }
}
