drun=docker-compose exec -T --user www-data php
dexec=docker-compose exec -T --user www-data php
dexec_tty=docker-compose exec --user www-data php
dexec_root=docker-compose exec -T --user root php
dexec_root_tty=docker-compose exec --user root php

.PHONY: bash
bash:
	$(dexec_tty) bash

.PHONY: root-bash
root-bash:
	$(dexec_root_tty) bash

.PHONY: test
test: app/vendor e2e_test/node_modules tests/test_images/0.png
	make reload-test-data
	$(dexec) php composer.phar run test
	$(dexec) bash -c "cd e2e_test && BASE_URL=http://localhost npm run test"

.PHONY: e2etest
e2etest: app/vendor e2e_test/node_modules tests/test_images/0.png
	make reload-test-data
	$(dexec) bash -c "cd e2e_test && BASE_URL=http://localhost npm run test"

.PHONY: clean
clean:
	cd dist_zip && make clean
	docker-compose up -d
	-make fix-permission
	-$(dexec) tests/cli_drop_all_table.php
	docker-compose stop
	-rm -r app/temp/installed.lock
	-rm -r app/temp/blog_template/*
	-rm -r public/uploads/*
	-rm -r tests/test_images/*.png
	-rm -r e2e_test/node_modules/
	-rm -r e2e_test/ss/*
	-rm -rf app/vendor/
	-rm -rf node_modules/
	-rm composer.phar
	-rm tests/App/Lib/CaptchaImage/test_output.gif

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

.PHONY: mysql-shell
mysql-shell:
	$(dexec_tty) make d-mysql-shell

.PHONY: d-mysql-shell
d-mysql-shell:
	mysql -u $(FC2_DB_USER) -p$(FC2_DB_PASSWORD) -h $(FC2_DB_HOST) -P $(FC2_DB_PORT) "$(FC2_DB_DATABASE)"

.PHONY: db-dump-schema
db-dump-schema:
	$(dexec) make d-db-dump-schema

.PHONY: d-db-dump-schema
d-db-dump-schema:
	mysqldump -u $(FC2_DB_USER) -p$(FC2_DB_PASSWORD) -h $(FC2_DB_HOST) -P $(FC2_DB_PORT) --no-data --no-tablespaces "$(FC2_DB_DATABASE)" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_schema.sql

.PHONY: db-dump-all
db-dump-all:
	$(dexec) make d-db-dump-all

.PHONY: d-db-dump-all
d-db-dump-all:
	mysqldump -u $(FC2_DB_USER) -p$(FC2_DB_PASSWORD) -h $(FC2_DB_HOST) -P $(FC2_DB_PORT) --complete-insert --skip-extended-insert --no-tablespaces "$(FC2_DB_DATABASE)" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_all.sql

.PHONY: db-dump-data-only
db-dump-data-only:
	$(dexec) make d-db-dump-data-only
.PHONY: d-db-dump-data-only
d-db-dump-data-only:
	mysqldump -u $(FC2_DB_USER) -p$(FC2_DB_PASSWORD) -h $(FC2_DB_HOST) -P $(FC2_DB_PORT) --complete-insert --skip-extended-insert --skip-triggers --no-create-db --no-create-info --no-tablespaces "$(FC2_DB_DATABASE)" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_data.sql

composer.phar:
	-$(dexec_root) mkdir -p /var/www/.composer
	-$(dexec_root) chown -R www-data:www-data /var/www/.composer
	$(dexec) curl -sSfL -o composer-setup.php https://getcomposer.org/installer
	$(dexec) php composer-setup.php --filename=composer.phar
	$(dexec) rm composer-setup.php

app/vendor: composer.phar
	$(dexec) php composer.phar install

e2e_test/node_modules:
	-$(dexec_root) mkdir -p /var/www/.npm
	-$(dexec_root) chown -R www-data:www-data /var/www/.npm
	$(dexec) bash -c "cd e2e_test && npm ci"

tests/test_images/0.png:
	$(dexec) bash -c "cd tests/test_images && ./download_samples.sh"

.PHONY: reload-test-data
reload-test-data:
	-$(dexec_root) chown -R www-data:www-data app/temp
	-$(dexec_root) chown -R www-data:www-data public/uploads
	-$(dexec) mkdir -p public/uploads/t/e/s/testblog2/file/
	$(dexec) cp -a tests/test_images/1.png public/uploads/t/e/s/testblog2/file/1.png
	$(dexec) cp -a tests/test_images/2.png public/uploads/t/e/s/testblog2/file/2.png
	$(dexec) touch app/temp/installed.lock
	$(dexec) tests/cli_load_fixture.php
	$(dexec) tests/cli_update_template.php testblog1
	$(dexec) tests/cli_update_template.php testblog2
	$(dexec) tests/cli_update_template.php testblog3

.PHONY: fix-permission
fix-permission:
	-$(dexec_root) chown -R www-data:www-data app/temp
	-$(dexec_root) chown -R www-data:www-data public/uploads
