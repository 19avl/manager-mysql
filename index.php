<?php

define("_EXEC", true);


require __DIR__."/locale.php";
require __DIR__."/config.php";
require __DIR__."/Wr_sql.php";
require __DIR__."/Wr_html.php";
require __DIR__."/Control.php";



if(!isset($_POST["session"]))
{	
	define("_SESSION", bin2hex(random_bytes(15)));

	require __DIR__."/template.php";
}
else
{
	require __DIR__."/Convert.php";
	require __DIR__."/Manager.php";
	require __DIR__."/View.php";
	require __DIR__."/Query.php";
	require __DIR__."/Controller.php";

	new Controller($SERVER, $LIMIT, $PASS);	
}


