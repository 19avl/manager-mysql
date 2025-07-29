<?php

/*
Copyright (c) 2018-2025 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/


defined("_EXEC") or die();

Class Auth
{
	private $session_key;

	public $CHECK;

	private $exceptions = [
		"_SAVE_SQL_SH","_SAVE_SQL_SH_FILTER",
		"_SAVE_SQL_TB","_SAVE_SQL_TB_FILTER",
		"_SAVE_SQL_RC_FILTER", "VIEW_DATA"];

	public function __construct($USER, $CHECK)
	{
		$this->CHECK = $CHECK;

		$this->main($USER);
	}


	public function main($USER)
	{
		if(!isset($_POST["usr"]))
		{
			$this->sess();

			session_destroy();
			unset($_POST["session"]);

			$this->user_form("&nbsp;", "", "");

			die();
		}
		else
		{
			if(isset($USER[$_POST["usr"]]))
			{
				if(!$this->AT($USER)){

					die();
				}
			}
			else
			{
				$this->user_form(_AT_MS_US, "", "");

				die();
			}
		}
	}


	public function AT($USER)
	{
		if(!isset($USER[$_POST["usr"]]["key"]) || ($USER[$_POST["usr"]]["key"] === "")){

			return true;
		}

		$this->sess();

		if(isset($_SESSION["request"])){

			$this->session_key = $_SESSION["request"];
		}
		else{

			$this->session_key = "";
		}

		if(!isset($_POST["action"]) || !in_array($_POST["action"], $this->exceptions, true))
		{
			$_SESSION["request"] = bin2hex(random_bytes(15));

			print "<input type='hidden' id='request' class='' value='".$_SESSION["request"]."'/>";
		}

		if(!isset($_POST['request']) || ($_POST['request'] === '') ||
			!isset($_SESSION["alias"]) || ($_SESSION["alias"] !== md5($_POST["usr"])))
		{
			$_SESSION["alias"] = md5($_POST["usr"]);

			$this->authorize_form("&nbsp;", $_POST["usr"]);

			return false;
		}
		else
		{
			if($_POST['request'] === sha1($this->session_key.sha1($_POST["session"].$USER[$_POST["usr"]]["key"]).$this->str_request()))
			{
				return true;
			}
			else
			{
				$this->authorize_form(_AT_MS_PS, $_POST["usr"]);

				return false;
			}
		}
	}

	private function sess()
	{
		session_name(md5(_URL));

		$url = parse_url(_URL);

		session_set_cookie_params(0, $url["path"], $url["host"], false, true);
		register_shutdown_function('session_write_close');

		session_start();
		session_regenerate_id(true);
	}

	public function user_form($ms)
	{
		print "<div class='at_app'>"._AT_APP.": "._AT_ALIAS."</div>";
		print "<div class='separator11'></div>";
		print "<div id='ms_in' class='at_message' >".$ms."</div>";
		print "<form method='post'>";

		print "<input type='input' id='en_user' name='at_user' class='at_key' value=''
			onkeydown='as.in_stu();' autocomplete='off' autofocus/>";

		print "<br><input type='button' name='' class='at_btn' value='OK' onclick='as.set_usr();'/><br/>";
		print "</form>";
		print "<div class='separator11'></div>";
	}

	public function authorize_form($ms, $_US)
	{
		print "<div class='at_app'>"._AT_APP.": "._AT_KEY."</div>";
		print "<div class='separator11'></div>";
		print "<div id='ms_in' class='at_message' >".$ms."</div>";
		print "<form method='post'>";

		print "<input type='password' id='en_key' name='' class='at_key' value=''
			onkeydown='as.in_stp(\"".$_US."\");' autocomplete='off' autofocus/>";

		print "<br><input type='button' name='' class='at_btn' value='OK' onclick='as.set_ps(\"".$_US."\");'/><br/>";
		print "</form>";
		print "<div class='separator11'></div>";
	}


	private function str_request()
	{
		$A = explode(",", preg_replace("/\s{1,}/","",$this->CHECK));

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