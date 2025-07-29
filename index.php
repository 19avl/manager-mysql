<?php

define("_EXEC", true);

require __DIR__."/config.php";
require __DIR__."/locale.php";

$CHECK ="action,jsv,sv,sh,tb,
		list_rw,list_rc,page_rc,from_rc,order_rc,field_rc,		
		fl_field_rc,fl_value_rc,fl_operator_rc,fl_and_rc,
		key,field,function,file,
		cl_in,cl_df,			
		script";

if(!isset($_POST["session"]))
{	
	define("_SESSION", bin2hex(random_bytes(15)));

	require __DIR__."/template.php";
}
else
{	
	require __DIR__."/Auth.php";	
	require __DIR__."/Request.php";
	require __DIR__."/Controller.php";

	new Controller($USER, $CHECK, $SQL);	
}


