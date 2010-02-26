#!/bin/bash
# build a mod package
#
# (c) 2010 eviL3
# obtained at http://github.com/evil3/phpbb-github_profile_link/blob/master/develop/build.sh
# -- no license specified within file, however project's license is GPLv2, so GPLv2 is assumed
#
# develop/build.sh [VERSION]
#
# depends on: git-archive, svn-export, zip
#
# must specify files and folders manually because git-archive fails to respect
# gitattributes
#
# make sure you are on the master branch when building

# set version to $1, default to "dev"
VERSION="dev"
if [ $# -gt 0 ];
then
	VERSION=$1
fi

ZIP_NAME="failnet-$VERSION.zip"
PREFIX="failnet"

git archive --format=zip --prefix=$PREFIX/ -o $ZIP_NAME HEAD data includes logs config.php failnet.cmd failnet.php failnet.sh LICENSE README
