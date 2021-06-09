<?php

define("_EXEC", true);


require __DIR__."/locale.php";
require __DIR__."/config.php";
require __DIR__."/Wr_sql.php";
require __DIR__."/Wr_html.php";
require __DIR__."/Control.php";




if(!isset($_POST["session"]))
{	
	define("_SESSION", uniqid(time()));
	
	require __DIR__."/template.php";
}
else
{
	define("_SESSION", $_POST["session"]);
	
	require __DIR__."/Convert.php";
	require __DIR__."/Manager.php";
	require __DIR__."/View.php";
	require __DIR__."/Query.php";
	require __DIR__."/Controller.php";

	new Controller($SERVER, $LIMIT, $PASS);	
}


