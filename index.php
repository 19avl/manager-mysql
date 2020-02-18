<?php

define("_EXEC", true);

require __DIR__."/config.php";
require __DIR__."/Control.php";
require __DIR__."/Convert.php";
require _LOCALE;

if(!isset($_POST["session"]))
{	
	define("_SESSION", uniqid(time()));
	
	require __DIR__."/template.php";
}
else
{
	define("_SESSION", $_POST["session"]);

	new Control($pass);	

	require __DIR__."/sql.php";
	if(!isset($SQL)){$SQL = [];}

	require __DIR__."/Wr_sql.php";
	require __DIR__."/Manager.php";
	require __DIR__."/Wr_html.php";
	require __DIR__."/View.php";
	require __DIR__."/Query.php";
	require __DIR__."/Controller.php";

	new Controller($SERVER, $SQL, $LIMIT);		
}


