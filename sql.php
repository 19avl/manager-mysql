<?php

defined("_EXEC") or die();


$SQL = [

"plugins" => "SHOW PLUGINS;",

"variables" => "

-- SELECT @@global.sql_mode;
-- SELECT @@session.sql_mode;

-- SELECT @@global.FOREIGN_KEY_CHECKS;
-- SELECT @@session.FOREIGN_KEY_CHECKS;

-- SELECT @@global.event_scheduler;

-- SHOW VARIABLES WHERE Variable_name IN ('log', 'general_log', 'general_log_file', 'log_output');

show GLOBAL variables;
-- show SESSION variables;",

"collation" => "SHOW COLLATION;",

"engines" => "SHOW ENGINES;",

"processlist" => "SHOW PROCESSLIST;",

"privileges" => "SHOW PRIVILEGES;",

"open tables" => "
SHOW OPEN TABLES WHERE In_use>0;
SHOW OPEN TABLES;",

"user" => "
SELECT user, host FROM mysql.user where Grant_priv='Y';
SELECT user, host, Password FROM mysql.user;",


];


