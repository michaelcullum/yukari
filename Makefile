#
# Yukari makefile
#
PROJECT = yukari
RELEASE_DIR = .
STORE_DIR = ./downloads
EXCLUDE = ~$ \.txt$ \.xml$ \.markdown$ \.md$ stub\.php \.json$
EXCLUDEDIR = /\.git/ /\.svn/
PRIVKEY = ./build/cert/priv.pem
PUBKEY = ./build/cert/pub.pem
BINNUMBER=`cat ./build/bin_number.txt`
ZIPINCLUDES=bin\/* data\/config\/config.json.example data\/config\/addons\/*.example data\/database\/.keep data\/language\/* docs\/* lib\/* lib\/addons\/* LICENSE README.markdown
STRIP=.php$

# target: all - default target, does nothing
all :
	+@echo "no target specified, try 'make help'"

# target: package - builds main phar, builds all addon phars, creates zip package containing all needed files
package: version core alladdons
	@VAR=$(BINNUMBER); \
	BUILD=`expr $$VAR + 1`; \
	RELEASE_FILE=$(PROJECT)-build_$$BUILD;\
	mv $(PROJECT)-build_*.zip ./downloads; \
	zip -r $$RELEASE_FILE . -i $(ZIPINCLUDES); \
	rm ./src/VERSION; \
	echo $$BUILD > ./build/bin_number.txt; \
	echo "packaged build" $$BUILD

# target: core - builds main phar
core:
	phar-build --phar $(PROJECT).phar -s ./src/ -x "$(EXCLUDE)" -X "$(EXCLUDEDIR)" -S ./src/stub.php -p $(PRIVKEY) -P $(PUBKEY) --strip-files "$(STRIP)"; \
	mv $(PROJECT).phar* lib/; \
	echo "built core phar"

# target: alladdons - builds all addons
alladdons:
	echo "<?php __HALT_COMPILER();" > stub.php; \
	for f in `ls ./addons | grep -v '^_'`; do \
		phar-build --phar $$f.phar -s ./addons/$$f/ -x "$(EXCLUDE)" -X "$(EXCLUDEDIR)" -S stub.php -p $(PRIVKEY) -P $(PUBKEY) --strip-files "$(STRIP)"; \
		mv $$f.phar* lib/addons/; \
		echo "built addon phar: " $$f".phar"; \
	done; \
	rm stub.php

# target: listaddons - lists all present addons
listaddons:
	+@echo "addons present: " `ls ./addons | grep -v '^_'`;

version:
	@VAR=$(BINNUMBER); \
	BUILD=`expr $$VAR + 1`; \
	echo $$BUILD > ./src/VERSION

addon\:%:
	echo "<?php __HALT_COMPILER();" > stub.php; \
	ADDON=$(subst addon:,,$@); \
	phar-build --phar $$ADDON.phar -s ./addons/$$ADDON/ -x "$(EXCLUDE)" -X "$(EXCLUDEDIR)" -S stub.php -p $(PRIVKEY) -P $(PUBKEY) --strip-files "$(STRIP)"; \
	mv $$ADDON.phar* lib/addons/; \
	echo "built addon phar: " $$ADDON".phar"; \
	rm stub.php

# target: help - display callable targets
help:
	@+egrep "^# target:" [Mm]akefile
