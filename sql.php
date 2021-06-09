<?php

defined("_EXEC") or die();


$SQL = [
"plugins" => "SHOW PLUGINS;",
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


$FUNCTION = [
"PASSWORD" => ["'str'"],
"AES_ENCRYPT" => ["'str'", "'key'"],
"AES_DECRYPT" => ["'crypt_str'", "'key'"],
"DES_ENCRYPT" => ["'str'", "'key'"],
"DES_DECRYPT" => ["'crypt_str'", "'key'"],
"ASCII" => ["'str'"],
"ORD" => ["'str'"],
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


