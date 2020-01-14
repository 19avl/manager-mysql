<?php

define("_EXEC", true);

require __DIR__."/config.php";
require __DIR__."/Control.php";
require __DIR__."/locale.php";


if(!isset($_POST["session"]))
{	
	require __DIR__."/template.php";
}
else
{
	$control = new Control();
	if(!$control->AT($pass)){die();}

	require __DIR__."/sql.php";
	if(!isset($SQL)){$SQL = [];}
	
	require __DIR__."/Manager.php";
	require __DIR__."/View.php";
	require __DIR__."/Query.php";
	require __DIR__."/Controller.php";

	new Controller($SERVER, $SQL, $LIMIT);		
}


