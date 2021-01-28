.PHONY: bash
bash:
	docker-compose up -d
	docker-compose exec --user www-data php bash

.PHONY: root-bash
root-bash:
	docker-compose up -d
	docker-compose exec php bash

.PHONY: db-drop-all-table
db-drop-all-table:
	docker-compose up -d
	# e2e_testから呼び出される際のttyの都合上、ここはexecではなくrun
	docker-compose run --rm --user www-data php bash -c "php tests/cli_drop_all_table.php"

.PHONY:
docker-compose-build-no-cache:
	# alignment local UID/GID and docker UID/GID for Linux dev env.
	$(eval UID := $(shell id -u))
	$(eval GID := $(shell id -g))
	@docker-compose build --no-cache --build-arg PUID=$(UID) --build-arg PGID=$(GID) php

.PHONY:
docker-compose-build:
	# alignment local UID/GID and docker UID/GID for Linux dev env.
	$(eval UID := $(shell id -u))
	$(eval GID := $(shell id -g))
	@docker-compose build --build-arg PUID=$(UID) --build-arg PGID=$(GID) php

.PHONY: db-dump-schema
db-dump-schema:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --no-data --column-statistics=0 --no-tablespaces "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_schema.sql

.PHONY: db-dump-all
db-dump-all:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 --no-tablespaces "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_all.sql

.PHONY: db-dump-data-only
db-dump-data-only:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 --skip-triggers --no-create-db --no-create-info --no-tablespaces "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_data.sql

.PHONY: test
test: app/vendor e2e_test/node_modules tests/test_images/0.png
	docker-compose up -d
	make reload-test-data
	docker-compose exec --user www-data php bash -c "php composer.phar run test"
	cd e2e_test && npm run test

.PHONY: clean
clean:
	cd dist && make clean
	docker-compose up -d
	-docker-compose exec --user root php bash -c "rm -r app/temp/installed.lock"
	-docker-compose exec --user root php bash -c "rm -r app/temp/blog_template/*"
	-docker-compose exec --user root php bash -c "rm -r public/uploads/*"
	-rm -r tests/test_images/*.png
	-rm -r e2e_test/node_modules/
	-rm -r e2e_test/ss/*
	-docker-compose exec --user www-data php bash -c "tests/cli_drop_all_table.php"
	-docker-compose exec --user root php bash -c "rm -r app/vendor/"
	-rm -r node_modules/
	-rm composer.phar
	-docker-compose exec --user root php bash -c "rm tests/App/Lib/CaptchaImage/test_output.gif"

composer.phar:
	curl -sSfL -o composer-setup.php https://getcomposer.org/installer
	php composer-setup.php --filename=composer.phar
	rm composer-setup.php

app/vendor: composer.phar
	docker-compose up -d
	docker-compose exec --user www-data php bash -c "php composer.phar install"

e2e_test/node_modules:
	cd e2e_test && npm ci

tests/test_images/0.png:
	cd tests/test_images && ./download_samples.sh

.PHONY: setup-unit-test
setup-unit-test:
	make setup-dev
	make reload-test-data

.PHONY: setup-dev
setup-dev: app/vendor tests/test_images/0.png

.PHONY: reload-test-data
reload-test-data:
	docker-compose up -d
	make fix-permission
	-mkdir -p public/uploads/t/e/s/testblog2/file/
	cp -a tests/test_images/1.png public/uploads/t/e/s/testblog2/file/1.png
	cp -a tests/test_images/2.png public/uploads/t/e/s/testblog2/file/2.png
	touch app/temp/installed.lock
	docker-compose run --rm --user www-data php bash -c "tests/cli_load_fixture.php"
	docker-compose run --rm --user www-data php bash -c "tests/cli_update_template.php testblog2"

.PHONY: fix-permission
fix-permission:
	-docker-compose run --rm php bash -c "chown -R www-data:www-data app/temp"
	-docker-compose run --rm php bash -c "chown -R www-data:www-data public/uploads"
