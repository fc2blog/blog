
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

.PHONY: clean
clean:
	-rm -r app/temp/installed.lock
	-rm -r app/temp/blog_template/*
	-rm -r public/uploads/*
	-rm -r tests/test_images/*.png
	-rm -r public/uploads/*
	-rm -r e2e_test/node_modules/
	-tests/cli_drop_all_table.php
	-rm -r app/vendor/
	-rm composer.phar

composer.phar:
	curl -sSfL -o composer-setup.php https://getcomposer.org/installer
	php composer-setup.php --filename=composer.phar
	rm composer-setup.php

.PHONY: dev-setup
dev-setup: composer.phar
	php composer.phar install
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
