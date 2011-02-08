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
ZIPINCLUDES="bin\/* data\/config\/* data\/config\/addons\/* data\/database\/.keep data\/language\/* docs\/* lib\/* lib\/addons\/* LICENSE README.markdown"
# addons to build phars out of
ADDONS=("commander" "sqlite" "whitelist")
# name to use for the archive
ZIPNAME=yukari

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
	BINNUMBER=0
fi
BINNUMBER=$((BINNUMBER+1))
echo $BINNUMBER > $SCRIPT/bin_number.txt
echo $BINNUMBER > "$SCRIPT/../src/VERSION"

# build the latest phar archive
$SCRIPT/build.sh

# build the addon phars
for i in "${ADDONS[@]}"
do
	$SCRIPT/addon_build.sh $i
done

# up a dir
cd $SCRIPT/../
# stow away any old builds
mv $ZIPNAME-build_*.zip $SCRIPT/../downloads/
# start packaging stuff
NAME="$ZIPNAME-build_$BINNUMBER"
zip -r $NAME . -i $ZIPINCLUDES

# pitch the version file, we don't need it now
rm "$SCRIPT/../src/VERSION"
