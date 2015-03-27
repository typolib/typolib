# Typolib’

Typolib’ is a project created by French students in MIAGE with the help of the [Mozilla Localization Drivers team][].


## Dependencies

- PHP >= 5.4
- Composer (Dependency Manager for PHP, [Composer][])


## Initial setup

1. Set up the config file. In ```app/config```, copy the ```config.ini-dist``` and rename it into ```config.ini```
Edit the file and provide the install and config path information.
Here is my config for reference:

```install=/home/theo/moz/github/typolib
config=/home/theo/moz/github/typolib/app/config```


2. Installing and running Composer to get dependencies:

In a terminal, change directory to the “Typolib” repository you just cloned, then run:

```curl -sS https://getcomposer.org/installer | php && php composer.phar install```


## Running Typolib’

With PHP integrated server, if you want to run Typolib’on 8080 port, run from typolib/web/ folder:

```php -S localhost:8080```


## Unit tests

Before committing your work, run Unit tests to check you’re not creating any regression.
From typolib root folder, run:

```php vendor/atoum/atoum/bin/atoum -d tests/units/```


You should see a green line in your terminal stating something like:


```Success (1 test, 9/9 methods, 0 void method, 0 skipped method, 55 assertions)!```


## Update dependencies with composer

```php composer.phar update``` (or ```composer update``` if installed globally)


[Composer]: http://getcomposer.org/
[Mozilla Localization Drivers team]: https://wiki.mozilla.org/L10n:Mozilla_Team
