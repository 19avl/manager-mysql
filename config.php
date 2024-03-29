<?php

/*
Copyright (c) 2018-2024 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();

set_time_limit(0);
error_reporting (0);
//error_reporting (E_ALL);


define("_URL", $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].parse_url($_SERVER["REQUEST_URI"])["path"]);

$USER = [

	"pass"=>"",
	
	"server" => [
		"host"=>"localhost", 
		"port"=>3311, 
		"user"=>"root", 
		"pass"=>"root",


		"variables"=>[
			"names" => "utf8mb4",
		//	"sql_mode" => "TRADITIONAL",
			"sql_mode" => "STRICT_ALL_TABLES", 
		//	"GLOBAL general_log" => 0,
		//	"GLOBAL log_output" => "TABLE",
		
			//"sql_mode" => "NO_TABLE_OPTIONS",
			//"sql_mode" => "ORACLE",
		],
	
		"socket"=>"",
	
		"require_secure_transport"=>false,
		"ssl-key"=>"",
		"ssl-cert"=>"",
		"ssl-ca"=>""
	]
];


/* ITEMS ON PAGE */

$LIMIT = [
"SCHEMA"=>["10", "25", "50", "100"],
"TABLES"=>["10", "25", "50", "100"],
"RECORDS"=>["10", "1", "25", "100"]
];

