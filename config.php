<?php

/*
Copyright (c) 2018-2021 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();

set_time_limit(0);
error_reporting (0);


/* HOST */

define("_URL", $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].parse_url($_SERVER["REQUEST_URI"])["path"]);

$PASS = "";


/* MYSQL */

$SERVER = ["host"=>"localhost", 
//	"port"=>3311, 
	"user"=>"root", 
	"pass"=>"",
	"charset"=>"utf8mb4",
	"socket"=>"",
	"require_secure_transport"=>false,
	"ssl-key"=>"",
	"ssl-cert"=>"",
	"ssl-ca"=>""];


/* ITEMS ON PAGE */

$LIMIT = [
"SCHEMA"=>["10", "25", "50", "100"],
"TABLES"=>["10", "25", "50", "100"],
"RECORDS"=>["10", "25", "50", "100"]
];

