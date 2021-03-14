.PHONY:
build:
	make clean
	echo `git branch --contains |cut -d " " -f 2`
	git clone --depth=1 --branch=`git branch --contains |cut -d " " -f 2` https://github.com/uzulla/fc2blog.git fc2blog
	rm -rf fc2blog/.git
	cd fc2blog && php ../../composer.phar install --no-dev --optimize-autoloader -q
	# get tag from current commit id.
	git tag -l --contains `git show -s --format=%H` > fc2blog/app/version
	# Set "dev" to contents/version, If contents/version is zero size file.
	if [ ! -s fc2blog/app/version ]; then echo "dev:"`git branch --contains |cut -d " " -f 2 | grep -e '.'`":"`git rev-parse --short HEAD` > fc2blog/app/version; fi
	cd fc2blog && zip -q -r ../fc2blog_dist.zip app public

.PHONY:
build-no-pushed-branch:
	make clean
	mkdir fc2blog
	cp -a ../app ../public ../composer.json ../composer.lock fc2blog
	-rm fc2blog/app/temp/installed.lock
	cd fc2blog && php ../../composer.phar install --no-dev --optimize-autoloader
	rm fc2blog/composer.*
	echo "dev:"`git branch --contains |cut -d " " -f 2 | grep -e '.'`":"`git rev-parse --short HEAD` > fc2blog/app/version
	cd fc2blog && zip -r ../fc2blog_dist.zip app public

.PHONY:
clean:
	-rm -rf fc2blog
	-rm fc2blog_dist.zip
	-rm test_vm/fc2blog_dist.zip
	-rm test_vm/fc2blog_installer.php

.PHONY:
test:
	make build
	cp fc2blog_dist.zip test_vm/fc2blog_dist.zip
	cp installer/fc2blog_installer.php test_vm/fc2blog_installer.php
	cd test_vm && make image && make bash

.PHONY:
test-no-pushed-branch:
	make build-no-pushed-branch
	cp fc2blog_dist.zip test_vm/fc2blog_dist.zip
	cp installer/fc2blog_installer.php test_vm/fc2blog_installer.php
	cd test_vm && make image && make bash
