drop procedure if exists dropAllTables;

delimiter //
create procedure dropAllTables()
begin
    declare tableName varchar(255);
    declare isDone int default 0;
    declare tableNameCursor cursor for
        select TABLE_NAME
        from `information_schema`.tables
        where table_schema = database()
          and table_type = 'BASE TABLE';
    declare continue handler for sqlstate '02000' set isDone = 1;
    open tableNameCursor;
    repeat
        fetch tableNameCursor into tableName;
        if not isDone then
            set @sql = CONCAT('DROP TABLE ', tableName);
            prepare stmt from @sql;
            execute stmt;
        end if;
    until isDone end repeat;
    close tableNameCursor;
end
//
delimiter ;

call dropAllTables;

drop procedure if exists dropAllTables;
