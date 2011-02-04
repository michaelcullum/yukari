# Yukari

Yukari is a flexible IRC bot built in PHP 5.3 Object Oriented Programming.

## WARNING

Yukari is currently undergoing a complete codebase rewrite, and as such I have been picking the project up and setting it back down as I learn new coding techniques.

**Copyright**: *(c) 2009 - 2011 -- Damian Bushong*

**License**: *MIT License*

## Requirements

* PHP 5.3.0
* PHAR read access
* SQLite
* SQLite extension for PDO

## Dependencies

* sfYaml (packaged)

## Instructions

### Installation

Via git:
    git clone https://github.com/damianb/yukari.git
    cd ./yukari
    git submodule init
    git submodule update

Via gzip tarball package:
    tar xzf yukari-dev.tgz
    cd ./yukari

Optionally, you may verify the package if you have the phar-util package installed via PEAR.

### Compiling an updated PHAR package

Install the phar-util package <http://github.com/koto/phar-util> via PEAR, if you have not done so already
    $ sudo pear channel-discover pear.kotowicz.net
    $ sudo pear install kotowicz/PharUtil-beta

Make changes to the files in the **src/** directory, then build the package (without signing it)
    $ ./build/unsigned-build.sh

Using the compile-on-commit script (without signing it)
    $ cp build/hooks/autobuild-unsigned .git/hooks/pre-commit

### Running the script

@todo writeme
