<?php

/*
Copyright (c) 2018-2023 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/


defined("_EXEC") or die();


Class Control
{
	private $session_key;

	public static $CHECK ="action,jump_sv,sv,sh,tb,key,field,
		blob_ch,display,list_sh,list_tb,
		page_sh,from_sh,order_sh,field_sh,
		page_rc,from_rc,order_rc,field_rc,
		page_tb,from_tb,order_tb,field_tb,
		fl_field_sh,fl_value_sh,fl_operator_sh,fl_and_sh,
		fl_field_tb,fl_value_tb,fl_operator_tb,fl_and_tb,
		fl_field_rc,fl_value_rc,fl_operator_rc,fl_and_rc,
		view_rc,function,file,cl_sl,cl_dl,cl_df,cl_in,cl_tr,script";

	private $exceptions = ["_EXPORT_SQL_SH","_EXPORT_SQL_TB",];

	public function __construct(){}

	public function main($PASS)
	{
		if(!$this->AT($PASS)){ die(); }
	}

	public function AT($PASS)
	{
		if($PASS === ""){return true;}
		else{

			$PASS = $this->hashE($PASS);
		}

		ini_set('session.use_cookies', 0);
		session_id($_POST["session"]);
		session_start();

		if(isset($_SESSION["request"])){

			$this->session_key = $_SESSION["request"];
		}
		else{ $this->session_key = ""; }

		$update = false;

		if(isset($_POST["action"]) && in_array($_POST["action"], $this->exceptions, true)){

			$update = true;
		}

		if(!$update)
		{
			$_SESSION["request"] = bin2hex(random_bytes(15));

			print "<input type='hidden' id='request' class='' value='".$_SESSION["request"]."'/>";
		}

		if(!isset($_POST['request']) || ($_POST['request'] === ''))
		{
			$this->authorize_form("&nbsp;");
			return false;
		}
		else
		{
			if($_POST['request'] !== (string)$this->hashE($this->session_key.$PASS.$this->str_request()))
			{
				$this->authorize_form(_MESSAGE_CONNECTION);
				return false;
			}

			return true;
		}

	}

	static public function storage()
	{
		print "<div id='pass' class='' style='display: none;'>...</div>";
	}

	static public function ms($ms)
	{
		print "<div class='separator11'></div>";
		print "<div class='app'>"._APP."</div>";
		print "<div class='separator11'></div>";
		print "<div id='ms_in' class='message_at' >".$ms."</div>";
	}

	static public function authorize_form($ms)
	{
		print "<div class='separator11'></div>";
		print "<div class='app'>"._APP."</div>";
		print "<div class='separator11'></div>";
		print "<div id='ms_in' class='message_at' >".$ms."</div>";
		print "<form method='post'>";
		
		print "<input type='password' id='en_pass' name='' class='int_pass' value='' 
			onkeydown='ct.in_stp();' autocomplete='off' placeholder='"._AT_PASSWORD."'/>";

		print "<br><input type='button' name='' class='btn' value='OK' onclick='ct.get_ps(); '/><br/>";
		print "</form>";
	}

	function hashE($str)
	{
		$H1 = 1;
		$H2 = 1;
		$L = strlen($str);
		
		for ($i = 1; $i < $L; $i++) 	
		{	
			$H1 = ($H1 % ord($str[$i-1]) << 19) + ( ord($str[$i-1]) );	
			$H2 = ($H2 % ord($str[$i]) << 19) + ( ord($str[$i]) );
		}

		return $H1.$H2;			
	}

	private function str_request()
	{
		$A = explode(",", preg_replace("/\s{1,}/","",Control::$CHECK));

		$str = "";

		foreach($A as $value)
		{
			if(isset($_POST[$value]))
			{
				if(!is_array($_POST[$value])){
					
					$str .= $_POST[$value];
				}
				else
				{
					foreach($_POST[$value] as $v){

						$str .= $v;
					}
				}
			}
		}

		return preg_replace("/&/", "", $str);
	}	



}