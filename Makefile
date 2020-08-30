
.PHONY: db-dump-schema
db-dump-schema:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --no-data --column-statistics=0 "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_schema.sql

.PHONY: db-dump-all
db-dump-all:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_all.sql

.PHONY: db-dump-data-only
db-dump-data-only:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 --skip-triggers --no-create-db --no-create-info "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_data.sql

.PHONY: build-dist-zip
build-dist-zip:
	find app public |grep .DS_Store |xargs rm
	$(eval date := $(shell date "+%Y%m%d%H%M%S")-$(shell git rev-parse --short HEAD))
	$(eval tmpdir := fc2blog-dist-$(date))
	mkdir $(tmpdir)
	composer install --no-dev --optimize-autoloader
	cp -a app $(tmpdir)
	rm -r $(tmpdir)/app/temp/*
	chmod 777 $(tmpdir)/app/temp/
	cp -a public $(tmpdir)
	rm -r $(tmpdir)/public/uploads/*
	chmod 777 $(tmpdir)/public/uploads/
	rm -r $(tmpdir)/public/_for_unit_test_
	rm -r $(tmpdir)/public/.htaccess
	cp -r app/resource/apache/.htaccess.production $(tmpdir)/public/.htaccess
	cp LICENSE.txt README.md $(tmpdir)/app
	zip -r $(tmpdir).zip $(tmpdir)
	rm -r $(tmpdir)
	composer install
	echo "build $(tmpdir).zip successfully"
