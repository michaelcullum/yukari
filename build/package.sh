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

# files and directories to include in the package creation
ZIPINCLUDES="/bin/* /data/* /docs/* /lib/* /LICENSE /README.markdown"
# name to use for the archive
ZIPNAME=yukari
# should we do a signed build, or an unsigned build?
SIGNBUILD=1

##########################################
# end script config
##########################################

# get this script's full path
SCRIPT=`dirname $(readlink -f $0)`

# get the binary build number
if [ -e $SCRIPT/bin_number.txt ]
then
	BINNUMBER=`cat $SCRIPT/bin_number.txt`
else
	BINNUMBER=1
fi

# build the latest phar archive
if [ $SIGNBUILD -eq 1 ]
then
	$SCRIPT/build.sh
else
	$SCRIPT/unsigned-build.sh
fi

# up a dir
cd $SCRIPT/../
# start packaging stuff
NAME=$ZIPNAME-$BINNUMBER
zip -r $NAME -i $ZIPINCLUDES

# update the build number
BINNUMBER=$((BINNUMBER+1))
echo $BINNUMBER > $SCRIPT/bin_number.txt
