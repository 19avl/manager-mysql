<?php
/*
Copyright (c) 2018-2025 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();

set_time_limit(0);

error_reporting (0);
//error_reporting (E_ALL);


/* HOST */
define("_URL", $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].parse_url($_SERVER["REQUEST_URI"])["path"]);


$USER = [

	"user"=>[ // Alias

		"key"=>"key",	

		"server"=>[
			"host"=>"localhost", "port"=>3306, "user"=>"root", "pass"=>"root",
			"flags"=>0,

			"variables"=>[
				"names" => "utf8mb4",
				//"sql_mode" => "TRADITIONAL",	
				//"sql_mode" => "STRICT_ALL_TABLES", 
				//"GLOBAL general_log" => 0,
				//"GLOBAL log_output" => "TABLE",
			],

			"socket"=>"",

			"ssl-key"=>"",
			"ssl-cert"=>"",
			"ssl-ca"=>"",	
		]			
	],

	"root@127.0.0.1"=>[
	
		"server"=>["host"=>"127.0.0.1", "port"=>3306, "user"=>"root", "pass"=>"root"]],

	"root."=>[
	
		"server"=>[
			"host"=>".", "user"=>"root", "pass"=>"", 		
			"socket"=>"",
		]			
	],

	"root@localhost:3307"=>[
	
		"server"=>[
			"host"=>"localhost", "port"=>3307, "user"=>"root", "pass"=>"root",
			"variables"=>[
				"names" => "utf8mb4",			
			],	
		]			
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

