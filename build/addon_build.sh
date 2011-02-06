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
# do we want to sign the addon's phar?  (0 to sign using private pem key, 1 to not sign)
NOSIGN=0

##########################################
# end script config
##########################################

# get this script's full path
SCRIPT=`dirname $(readlink -f $0)`

# the name of the addon, grabbed as our arg
ADDON_NAME="$1"
if [ $ADDON_NAME = "" ]
then
	echo "Addon name not defined"
	exit
fi

# source directory
SRC=$SCRIPT/../addons/$ADDON_NAME/
# name of the phar archive
PHARNAME=$ADDON_NAME.phar

#cd $SCRIPT/../
phar-file-checksums -s $SRC -x "$EXCLUDE" -X "$EXCLUDEDIR" --checksumfile $SCRIPT/filestates/addons/$ADDON_NAME.json
RESULT=$?
if [ $RESULT -eq 0 ]; then
	echo "no phar recompile needed"
	echo "to force recompile, delete the file $SCRIPT/filestates/addons/$ADDON_NAME.json"
else
	echo "compiling phar for addon $ADDON_NAME"

	# create temporary stub file
	echo '<?php __HALT_COMPILER();' > $SCRIPT/tmp/stub.php

	# build the bloody phar!
	if [ $NOSIGN -eq 0 ]
	then
		phar-build --phar $SCRIPT/$PHARNAME -s $SRC -x "$EXCLUDE" -X "$EXCLUDEDIR" -S $SCRIPT/tmp/stub.php -p $SCRIPT/cert/priv.pem -P $SCRIPT/cert/pub.pem
	else
		phar-build --phar $SCRIPT/$PHARNAME -s $SRC -x "$EXCLUDE" -X "$EXCLUDEDIR" -S $SCRIPT/tmp/stub.php --ns
	fi

	mv $SCRIPT/$PHARNAME $SCRIPT/../lib/addons/$PHARNAME

	# check for not signing the package P:
	if [ $NOSIGN -eq 0 ]
	then
		mv $SCRIPT/$PHARNAME.pubkey $SCRIPT/../lib/addons/$PHARNAME.pubkey
	fi

	# remove temporary stub file
	rm $SCRIPT/tmp/stub.php
	echo 'addon phar compilation successful'
fi
