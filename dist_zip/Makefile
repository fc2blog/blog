.PHONY:
build:
	make clean
	git clone --depth=1 --branch=`git branch --contains |cut -d " " -f 2` https://github.com/uzulla/fc2blog.git fc2blog
	rm -rf fc2blog/.git
	cd fc2blog && php ../../composer.phar install --no-dev --optimize-autoloader
	cd fc2blog && zip -r ../fc2blog_dist_`git rev-parse --short HEAD`.zip app public

.PHONY:
clean:
	-rm -rf fc2blog
	-rm fc2blog_dist_*
	-rm test_vm/fc2blog_dist.zip

.PHONY:
test:
	make build
	cp fc2blog_dist_`git -C fc2blog  rev-parse --short HEAD`.zip test_vm/fc2blog_dist.zip
	cd test_vm && make image-no-cache && make bash
