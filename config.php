<?php
/*
Copyright (c) 2018-2024 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();

set_time_limit(0);

error_reporting (0);
//error_reporting (E_ALL);


/* HOST */
define("_URL", $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].parse_url($_SERVER["REQUEST_URI"])["path"]);


/* ITEMS ON PAGE */
$LIMIT = ["15", "50", "100", "200"];


$USER = [

	"user"=>[
		"host"=>"localhost", "port"=>3306, "user"=>"root", "pass"=>"root",

		"variables"=>[
			"names" => "utf8mb4",
			"sql_mode" => "TRADITIONAL",			
			//"GLOBAL general_log" => 0,
			//"GLOBAL log_output" => "TABLE",
		],

		"socket"=>"",
		"require_secure_transport"=>false,
		"ssl-key"=>"",
		"ssl-cert"=>"",
		"ssl-ca"=>"",						
	],

	"root@127.0.0.1"=>["host"=>"127.0.0.1", "port"=>3306, "user"=>"root", "pass"=>"root"],

	"root."=>[
		"host"=>".", "user"=>"root", "pass"=>"", 		
		"socket"=>"",	
	],

	"root@localhost:33011"=>[
		"host"=>"localhost", "port"=>33011, "user"=>"root", "pass"=>"",
		"variables"=>[
			"names" => "utf8mb4"
		],		
	],
];


$SQL["userscripts"] = [

	"current_user" => "SELECT CURRENT_USER();",
	"user" => "SELECT * FROM `mysql`.`user`;",
	"show variables" => "show SESSION variables;",
	"show plugins" => "SHOW PLUGINS;",
	"show engines" => "SHOW ENGINES;",
	"show processlist" => "SHOW PROCESSLIST;",
	"show open tables" => "SHOW OPEN TABLES;",
	"show privileges" => "SHOW PRIVILEGES;",
	"show grants" => "SHOW GRANTS;",	
	"show status" => "SHOW STATUS;",	
];


$SQL["functions"] = [
	"AES_ENCRYPT" => ["'str'", "'key'"],
	"AES_DECRYPT" => ["'crypt_str'", "'key'"],
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

