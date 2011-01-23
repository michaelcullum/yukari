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
EXCLUDE="~$ .*\.txt$ .*\.markdown$ .*\.md$ stub\.php .*\.json$"
# directories to exclude in phar-build
EXLUDEDIR="/Language/Package/*"
# source directory
SRC="./src/"
# name of the phar archive
PHARNAME=yukari.phar

##########################################
# end script config
##########################################

# get this script's full path
SCRIPT=`dirname $(readlink -f $0)`
phar-file-checksums --src $SRC --exclude $EXCLUDE --exclude-dir $EXCLUDEDIR --checksumfile ./filestate
RESULT=$?
if [ $RESULT -eq 0 ]; then
	echo 'no rebuild needed'
else
	echo 'updating phar file'
	phar-build --phar $SCRIPT/$PHARNAME --src $SRC --exclude $EXCLUDE --exclude-dir $EXCLUDEDIR --stub ./../src/stub.php --ns
	mv $SCRIPT/$PHARNAME $SCRIPT/../lib/$PHARNAME
	#mv $SCRIPT/$PHARNAME.pubkey $SCRIPT/../lib/$PHARNAME.pubkey
	if [ -d $SCRIPT/../.git/ ]; then
		git add $SCRIPT/../lib/$PHARNAME $SCRIPT/filestate
	fi
	echo 'success'
fi
