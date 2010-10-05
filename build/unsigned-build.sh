#! /bin/bash

SCRIPT=`dirname $(readlink -f $0)`
$SCRIPT/build-check --src ./src/ --exclude "~$ .*\.txt$ .*\.markdown$ .*\.md$"
RESULT=`cat $SCRIPT/rebuild`
if [ $RESULT = '1' ]; then
	echo 'updating phar file'
	phar-build --phar $SCRIPT/failnet.phar --src ./src/ --exclude "~$ .*\.txt$ .*\.markdown$ .*\.md$" --ns
	$SCRIPT/build-filestate-update --src ./src/ --exclude "~$ .*\.txt$ .*\.markdown$ .*\.md$"
	cp $SCRIPT/failnet.phar $SCRIPT/../failnet.phar
    rm $SCRIPT/failnet.phar
	rm $SCRIPT/rebuild
	if [ -d $SCRIPT/../.git/ ]; then
		git add $SCRIPT/../failnet.phar $SCRIPT/filestate.json
	fi
	echo 'success'
else
	rm $SCRIPT/rebuild
	echo 'no rebuild needed'
fi
