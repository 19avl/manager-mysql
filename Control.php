<?php

/*
Copyright (c) 2018-2020 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/


defined("_EXEC") or die();


Class Control
{
	private $pass;
	private $session_key;

	public static $CHECK =
		"action,
		bd,
		tb,
		key,
		field,
		name_new,
		cl_del,
		cl_def,
		cl_in,
		cl_change,
		tb_def,
		script";

	private $exceptions = [
		["action","_EXPORT_DB"],
		["action","_EXPORT_TB"],
	];


	public function __construct($pass)
	{
		if(!$this->AT($pass)){ die(); }
	}


	public function AT($pass)
	{
		if($pass === ""){return true;}
		else{$pass = $this->hashE($pass);}

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
			if(isset($_POST[$this->exceptions[$k][0]]) && ($_POST[$this->exceptions[$k][0]] == $this->exceptions[$k][1])){

				$update = true;
			}
		}

		if(!$update)
		{
			$_SESSION["request"] = $this->rs(strlen($pass));
			print "<input type='hidden' id='request' class='' value='".$_SESSION["request"]."'/>";
		}

		if(!isset($_POST['request']) || ($_POST['request'] === ''))
		{
			$this->authorize_form("&nbsp;");
			return false;
		}
		else
		{
			if($_POST['request'] !== (string)$this->hashE($this->enc($this->session_key, $pass).$this->check_request()))
			{
				$this->authorize_form(_AT_ERROR);
				return false;
			}

			return true;
		}
	}


	static public function storage()
	{
		print "<div id='pass' class='' style='display: none;'>...</div>";
	}

	static public function authorize_form($ms)
	{
		print "<div class='separator11'></div>";
		print "<div class='app'>"._APP."</div>";
		print "<div class='separator11'></div>";
		print "<div id='ms_in' class='message' >".$ms."</div>";
		print "<form method='post'>";
		print "<input type='password' id='en_pass' name='' class='int_pass' value='' autocomplete='off' placeholder='"._AT_PASSWORD."'/>";
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
		$M = 25717;
		$L = strlen($str);

		return $this->hc(0, $L, 2, $M, $str)."".$this->hc(1, $L, 2, $M, $str);
	}


	private function hc($N, $L, $S, $M, $str)
	{
		$H = 0;
		for ($i = $N; $i < $L; $i=$i+$S) {

			$H = (( $H % $M ) * 10000) + ( ord($str[$i]) % $M );
		}
		return $H;
	}

	private function check_request()
	{
		$A = explode(",", preg_replace("/\s{1,}/","",Control::$CHECK));

		$R = [];

		foreach($A as $value){

			if(isset($_POST[$value])){

				if(!is_array($_POST[$value])){

					$R[$value] = $_POST[$value]."&";
				}
				else{

					$str = "";
					foreach($_POST[$value] as $v){ $str .=  $v."&";	}
					$R[$value] = $str ;
				}
			}
		}

		$str = "";

		foreach($A as $value){

			if(isset($R[$value])){$str .= $R[$value];}
		}

		return $str;
	}



}