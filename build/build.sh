#! /bin/bash

#
#===================================================================
#  Yukari
#-------------------------------------------------------------------
# Copyright:    (c) 2009 - 2011 -- Damian Bushong
# License:      MIT License
#
#===================================================================
#
# This source file is subject to the MIT license that is bundled
# with this package in the file LICENSE.
#

##########################################
# begin script config
##########################################

# files to exclude in phar-build
EXCLUDE="~$ .*\.txt$ .*\.xml$ .*\.markdown$ .*\.md$ stub\.php .*\.json$"
# directories to exclude in phar-build
EXCLUDEDIR="/\.git/ /\.svn/"
# source directory
SRC="./src/"
# name of the phar archive
PHARNAME=yukari.phar

##########################################
# end script config
##########################################

# get this script's full path
SCRIPT=`dirname $(readlink -f $0)`
#cd $SCRIPT/../
phar-file-checksums -s $SRC -x "$EXCLUDE" -X "$EXCLUDEDIR" --checksumfile $SCRIPT/filestates/yukari.json
RESULT=$?
if [ $RESULT -eq 0 ]; then
	echo "no phar recompile needed"
	echo "to force recompile, delete the file $SCRIPT/filestates/yukari.json"
else
	echo "compiling phar for yukari"
	phar-build --phar $SCRIPT/$PHARNAME -s $SRC -x "$EXCLUDE" -X "$EXCLUDEDIR" -S ./../src/stub.php -p $SCRIPT/cert/priv.pem -P $SCRIPT/cert/pub.pem
	mv $SCRIPT/$PHARNAME $SCRIPT/../lib/$PHARNAME
	mv $SCRIPT/$PHARNAME.pubkey $SCRIPT/../lib/$PHARNAME.pubkey
	if [ -d $SCRIPT/../.git/ ]; then
		git add $SCRIPT/../lib/$PHARNAME $SCRIPT/filestates/yukari.json
	fi
	echo 'yukari phar compilation successful'
fi
