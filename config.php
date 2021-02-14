<?php

/*
Copyright (c) 2018-2021 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/


defined("_EXEC") or die();

set_time_limit(0);
error_reporting (0);


/* - HOST */

define("_URL", "http://".$_SERVER["SERVER_NAME"].parse_url($_SERVER["REQUEST_URI"])["path"]);

$PASS = "";

/* - MYSQL */

$SERVER = ["host"=>"localhost", "port"=>"3306", "user"=>"root", "pass"=>""];


/* - Record on page */

$LIMIT = [
"SCHEMA"=>["25", "50", "100"],
"TABLES"=>["25", "50", "100"],
"RECORDS"=>["10", "25", "50"]
];


/* - LOCALE */

define("_LOCALE", __DIR__."/locale.php");



