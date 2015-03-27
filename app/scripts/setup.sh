#! /usr/bin/env bash

function interrupt_code()
# This code runs if user hits control-c
{
  echored "\n*** Operation interrupted ***\n"
  exit $?
}

# Trap keyboard interrupt (control-c)
trap interrupt_code SIGINT

# Pretty printing functions
NORMAL=$(tput sgr0)
GREEN=$(tput setaf 2; tput bold)
RED=$(tput setaf 1)

function echored() {
    echo -e "$RED$*$NORMAL"
}

function echogreen() {
    echo -e "$GREEN$*$NORMAL"
}

# Store current directory path to be able to call this script from anywhere
DIR=$(dirname "$0")

# Store typolib install path to use that to generate a config.ini file automatically
TYPOLIBDIR="$(cd "${DIR}/../../";pwd)"

# Check that we have a config.ini file
if [ ! -f $DIR/../config/config.ini ]
then
    echogreen "WARNING: There is no app/config/config.ini file. Creating one based based on app/config/config.ini-dev template."
    function render_template() {
      eval "echo \"$(cat $1)\""
    }
    render_template $DIR/../config/config.ini-dist > $DIR/../config/config.ini
fi

# Convert config.ini to bash variables
eval $(cat $DIR/../config/config.ini | $DIR/ini_to_bash.py)

# Check that $install variable points to a git repo
if [ ! -d $install/.git ]
then
    echored "ERROR: The 'install' variable in your config.ini file is probably wrong as there is no git repository at the location you provided."
    exit 1
fi

# Check that we have PHP installed on this machine
if ! command -v php >/dev/null 2>&1
then
    echored "ERROR: PHP is not installed on your machine, PHP >=5.4 is required to run Transvision."
    echo "If you are on Debian/Ubuntu you can install it with 'sudo apt-get install php5'."
    exit 1
fi


cd $install

# Install Composer if not installed
if ! command -v composer >/dev/null 2>&1
then
    echogreen "Installing Composer (PHP dependency manager)"
    php -r "readfile('https://getcomposer.org/installer');" | php
    if [ ! -d vendor ]
    then
        echogreen "Installing PHP dependencies with Composer (locally installed)"
        php composer.phar install
    fi
else
    if [ ! -d vendor ]
    then
        echogreen "Installing PHP dependencies with Composer (globally installed)"
        composer install
    fi
fi
