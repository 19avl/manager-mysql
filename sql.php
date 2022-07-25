<?php

defined("_EXEC") or die();


$SQL = [
"plugins" => "SHOW PLUGINS;",
"collation" => "SHOW COLLATION;",
"character_set" => "SHOW VARIABLES LIKE 'char%';",
"engines" => "SHOW ENGINES;",
"processlist" => "SHOW PROCESSLIST;",
"privileges" => "SHOW PRIVILEGES;",

"open tables" => "
SHOW OPEN TABLES WHERE In_use>0;
SHOW OPEN TABLES;",

"user" => "
SELECT user, host FROM mysql.user WHERE Grant_priv='Y';
SELECT user, host FROM mysql.user WHERE Host='%';",

];


$FUNCTION = [
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


