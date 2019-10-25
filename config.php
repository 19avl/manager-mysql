<?php

/*
Copyright (c) 2018-2019 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();
set_time_limit(0);

//error_reporting (0);
error_reporting (E_ALL);


/* - HOST */

define("_URL", "http://".$_SERVER["SERVER_NAME"].parse_url($_SERVER["REQUEST_URI"])["path"]);

$pass = "";


/* - MYSQL */

$SERVER = ["host"=>"localhost", "user"=>"root", "pass"=>""];


/* - Record on page */

$LIMIT = [
"SCHEMA"=>["25", "50", "100"],
"TABLES"=>["25", "50", "100"],
"RECORDS"=>["5", "10", "20", "100"]
];
	
	