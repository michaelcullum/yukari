# Failnet 3

Failnet is a flexible IRC bot built in PHP 5.3 Object Oriented Programming.

## WARNING

Failnet is currently undergoing a complete codebase rewrite, and as such I have been picking the project up and setting it back down as I learn new coding techniques.

**Version**:	*3.0.0-DEV*

**Copyright**: *(c) 2009 - 2010 -- Damian Bushong*

**License**: *MIT License*

## Failnet's Requirements

* PHP 5.3.0
* SQLite
* SQLite extension for PDO

## Instructions

### Installation

Via git:
    git clone http://github.com/Obsidian1510/Failnet3.git
	cd ./Failnet3
	git submodule init
	git submodule update

Via gzip tarball package:
	tar xzf failnet-3.0.0-dev.tgz
	cd ./Failnet3

Optionally, you may verify the package if you have the phar-util package installed via PEAR.

### Compiling an updated PHAR package

Install the phar-util package <http://github.com/koto/phar-util> via PEAR, if you have not done so already
	$ sudo pear channel-discover pear.kotowicz.net
	$ sudo pear install kotowicz/PharUtil-beta

Make changes to the files in the **src/** directory, then build the package (without signing it)
	$ ./build/unsigned-build.sh

Using the compile-on-commit script (without signing it)
	$ cp build/hooks/autobuild-unsigned .git/hooks/pre-commit

### Running Failnet

@todo writeme
