CREATE TABLE db_migrate_tick
(
    tick BIGINT NOT NULL default 0 PRIMARY KEY
);

INSERT INTO db_migrate_tick (tick)
VALUES (0);