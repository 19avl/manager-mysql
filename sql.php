<?php

defined("_EXEC") or die();

$SQL = [

"sql_mode" => "SELECT @@sql_mode;",

"character_set" => "show variables like 'char%';",

"processlist" => "SHOW PROCESSLIST;",

"open tables" => "
SHOW OPEN TABLES;
SHOW OPEN TABLES WHERE In_use>0;
",

"foreign_key_checks" => "SELECT @@GLOBAL.foreign_key_checks, @@SESSION.foreign_key_checks;",

"foreign key" => "
SELECT * FROM information_schema.KEY_COLUMN_USAGE 
WHERE CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME is not null;
",

];
