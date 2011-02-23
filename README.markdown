# Yukari

Yukari is a flexible IRC bot built in PHP 5.3 Object Oriented Programming.

**Copyright**: *(c) 2009 - 2011 -- Damian Bushong*

**License**: *MIT License* - please see the provided file located in /LICENSE for the full license text

## Requirements

* PHP 5.3.x
* PHAR read access

## Dependencies

* sfYaml (packaged)

## Extras

More addons are available in the yukari-extras repository; for more details, look here: <https://github.com/damianb/yukari-extras>

## Instructions

### Installation

Via git:
    $ git clone https://github.com/damianb/yukari.git
    $ cd ./yukari
    $ git submodule init
    $ git submodule update

Via gzip tarball package of source:
    $ tar xzf yukari-master.tar.gz
    $ cd ./yukari

Via zip package of build (replacing {buildnumber} with the actual build number):
    $ mkdir ./yukari
    $ unzip yukari-build_{buildnumber}.zip -d ./yukari/
    $ cd ./yukari

Optionally, you may verify the phar(s) if you have the phar-util package installed via pear.

### Compiling an updated phar

Install the phar-util package <https://github.com/koto/phar-util> via pear, if you have not done so already
    $ sudo pear channel-discover pear.kotowicz.net
    $ sudo pear install kotowicz/PharUtil-beta

Make changes to the files in the **src/** directory, then build the phar (without signing it)
    $ ./build/unsigned-build.sh

Additionally, you can build your phars on-commit using the provided compile-on-commit script (without signing it)
    $ cp build/hooks/autobuild-unsigned .git/hooks/pre-commit

### Packaging a build

Make your changes to the files in the **src/** directory, then make changes to addons in the **addons/** directory.  When ready, use the provided packaging script to compile the next build.
    $ ./build/package.sh

Please note that this script will, by default, attempt to sign any phars it builds.  You will need to modify the script itself to prevent this if you don't want the phars signed.

### Building addons

Make changes to individual addons in the **addons/** directory (one directory per addon), then build the addon phar
    $ ./build/addon_build.sh

Note that this requires having already created OpenSSL certificates using phar-generate-cert (part of the phar-util pear package) and placing them in the directory **build/cert/**.

### Running the script

#### Windows

@todo writeme

#### Linux

Navigate to the root directory of Yukari, then use this command:
	$ ./bin/yukari

Yukari will immediately start up afterwards.

Protip: You can specify an alternative configuration file to use within the **data/config/** directory by using the commandline parameter "*--config=confignamehere*"

**@note**: A shell script providing easy manipulation of the bot through screen is planned.
