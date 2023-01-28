<?php

/*
Copyright (c) 2018-2023 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/


defined("_EXEC") or die();


Class Control
{
	private $session_key;

	public static $CHECK ="action,jump_sv,sv,db,tb,key,field,
		blob_ch,display,list_db,list_tb,
		page_db,from_db,order_db,field_db,
		page_rc,from_rc,order_rc,field_rc,
		page_tb,from_tb,order_tb,field_tb,
		fl_field_db,fl_value_db,fl_operator_db,fl_and_db,
		fl_field_tb,fl_value_tb,fl_operator_tb,fl_and_tb,
		fl_field_rc,fl_value_rc,fl_operator_rc,fl_and_rc,
		view_rc,function,file,cl_sl,cl_dl,cl_df,cl_in,cl_tr,script";

	private $exceptions = [
		["action","_EXPORT_SQL_DB"],
		["action","_EXPORT_SQL_TB"],
	];

	public function __construct(){}

	public function main($PASS)
	{
		if(!$this->AT($PASS)){ die(); }
	}

	public function AT($PASS)
	{
		if($PASS === ""){return true;}
		else{$PASS = $this->hashE($PASS);}

		ini_set('session.use_cookies', 0);
		session_id($_POST["session"]);
		session_start();

		if(isset($_SESSION["request"])){

			$this->session_key = $_SESSION["request"];
		}
		else{ $this->session_key = ""; }

		$update = false;

		foreach($this->exceptions as $k=>$v)
		{
			if(isset($_POST[$this->exceptions[$k][0]]) && 
				($_POST[$this->exceptions[$k][0]] == $this->exceptions[$k][1])){

				$update = true;
			}
		}

		if(!$update)
		{
			$_SESSION["request"] = $this->rs(strlen($PASS));
			print "<input type='hidden' id='request' class='' value='".$_SESSION["request"]."'/>";
		}

		if(!isset($_POST['request']) || ($_POST['request'] === ''))
		{
			$this->authorize_form("&nbsp;");
			return false;
		}
		else
		{
			if($_POST['request'] !== (string)$this->hashE($this->enc($this->session_key, $PASS).$this->str_request()))
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


	private function rs($len)
	{
		mt_srand(time()+(double)microtime()*1000000);

		$str = "";

		for($i=0;$i<$len;$i++){

			$str .= mt_rand(1,9);
		}

		return $str;
	}


	private function enc($key, $str)
	{
		$hash = "";
		$m = 251;

		if($key === ""){return "";}

		for ($i=0; $i<strlen($str); $i++){

			$hash .= base_convert((pow((int)($key[$i]), (int)($str[$i])) % $m), 10, 16);
		}

		return $hash;
	}

	private function hashE($str)
	{
		$R = "";
		$H = 1;
		$L = strlen($str);
		
		for ($s = 0; $s < 7; $s++) {
		
			for ($i = 1; $i < $L; $i++) {

				$H = (( $H % ord($str[$i]) ) << 5) + (( ord($str[$i]) % ord($str[$i-1]) ) << $s);
			}

			$R .= $H;
		}
		
		return $R;
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