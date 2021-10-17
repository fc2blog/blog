drop procedure if exists dropalltables;
delimiter //
create procedure dropalltables()
begin
    declare tablename varchar(255);
    declare isdone int default 0;
    declare tablenamecursor cursor for
        select TABLE_NAME
        from `information_schema`.tables
        where table_schema = database() and table_type = 'BASE TABLE';
    declare continue handler for sqlstate '02000' set isdone = 1;
    open tablenamecursor;
    repeat
        fetch tablenamecursor into tablename;
        if not isdone then
            set @sql = CONCAT('DROP TABLE ', tablename);
            prepare stmt from @sql;
            execute stmt;
        end if;
    until isdone end repeat;
    close tablenamecursor;
end
//
delimiter ;

call dropalltables;

drop procedure if exists dropalltables;
