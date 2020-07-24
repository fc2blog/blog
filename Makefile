
.PHONY: db-dump-schema
db-dump-schema:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --no-data --column-statistics=0 "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_schema.sql

.PHONY: db-dump-all
db-dump-all:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_all.sql

.PHONY: db-dump-data-only
db-dump-data-only:
	mysqldump -u docker -pdocker -h 127.0.0.1 -P 3306 --complete-insert --skip-extended-insert --column-statistics=0 --skip-triggers --no-create-db --no-create-info "dev_fc2blog" | sed "s/ AUTO_INCREMENT=[0-9]*//" > dump_data.sql
