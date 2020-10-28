
.PHONY: db-dump-schema
db-dump-schema:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --no-data --column-statistics=0 "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_schema.sql

.PHONY: db-dump-all
db-dump-all:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_all.sql

.PHONY: db-dump-data-only
db-dump-data-only:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 --skip-triggers --no-create-db --no-create-info "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_data.sql

.PHONY: test-download-images
test-download-images:
	cd tests/test_images && ./download_samples.sh

.PHONY: test
test:
	php composer.phar run test
	cd e2e_test && npm run test

.PHONY: clean
clean:
	-rm -r app/temp/installed.lock
	-rm -r app/temp/blog_template/*
	-rm -r public/uploads/*
	-rm -r tests/test_images/*.png
	-rm -r e2e_test/node_modules/
	-rm -r e2e_test/ss/*
	-tests/cli_drop_all_table.php
	-rm -r app/vendor/
	-rm -r node_modules/
	-rm composer.phar
	-rm tests/App/Lib/CaptchaImage/test_output.gif

composer.phar:
	curl -sSfL -o composer-setup.php https://getcomposer.org/installer
	php composer-setup.php --filename=composer.phar
	rm composer-setup.php

.PHONY: setup-unit-test
setup-unit-test:
	make setup-dev
	make setup-test-data

.PHONY: setup-dev
setup-dev: composer.phar
	php composer.phar install
	npm ci
	cd e2e_test && npm ci
	cd tests/test_images && ./download_samples.sh

.PHONY: setup-test-data
setup-test-data:
	cp -a tests/test_data/app/temp/blog_template/t app/temp/blog_template
	cp -a tests/test_data/public/uploads/t app/temp/blog_template
	-mkdir -p public/uploads/t/e/s/testblog2/file/
	cp -a tests/test_images/1.png public/uploads/t/e/s/testblog2/file/1.png
	cp -a tests/test_images/2.png public/uploads/t/e/s/testblog2/file/2.png
	touch app/temp/installed.lock
	tests/cli_load_fixture.php
	tests/cli_update_template.php testblog2
