<?php

defined("_EXEC") or die();


$SQL_SCRIPTS = [

	"userscripts" => [

"show variables" => "
-- show GLOBAL variables;
-- show SESSION variables;

show variables like 'sql_mode';
SHOW VARIABLES WHERE Variable_name IN ('log', 'general_log', 'general_log_file', 'log_output');",

"show plugins" => "SHOW PLUGINS;",
"show engines" => "SHOW ENGINES;",
"show processlist" => "SHOW PROCESSLIST;",
"show privileges" => "SHOW PRIVILEGES;",
"show open tables" => "SHOW OPEN TABLES;",
"show collation" => "SHOW COLLATION;",

"show charset" => "
SHOW VARIABLES LIKE 'char%';
show variables LIKE '%collation%';
SHOW COLLATION;",

"show grants" => "SHOW GRANTS;",

	],
];

$SQL_FUNCTIONS = [

"AES_ENCRYPT" => ["'str'", "'key'"],
"AES_DECRYPT" => ["'crypt_str'", "'key'"],
"CHAR" => ["int", "..."],
"CONCAT" => ["'str1'", "'str2'", "'...'"],
"REPLACE" => ["'str'", "'from_str'", "'to_str'"],
"LOWER" => ["'str'"],
"UPPER" => ["'str'"],
"HEX" => ["'str'"],
"UNHEX" => ["'str'"],
"MD5" => ["'str'"],
"SHA1" => ["'str'"],
"TO_BASE64" => ["'str'"],
"FROM_BASE64" => ["'str'"],
"LOAD_FILE" => ["'str'"],
];


