<?php


defined('_EXEC') or die();

trait Convert
{
	protected function s2h($input)
	{
		return unpack('H*', "$input")[1];
	}


	protected function h2s($input)
	{
		return pack('H*', $input);
	}


	protected function html($input, $EL="", $DL="")
	{
		if($EL !==""){

			return preg_replace("/".$EL."/", $DL, htmlspecialchars((string)$input, ENT_QUOTES | ENT_SUBSTITUTE));
		}
		else{

			return htmlspecialchars((string)$input, ENT_QUOTES | ENT_SUBSTITUTE);
		}
	}


	protected function set_value($value)
	{
		return $value;
	}


	protected function set_value_list($list)
	{
		$RT = [];

		foreach($list as $key=>$value){

			$RT[$key] = $this->set_value($value);
		}

		return $RT;
	}

	protected function set_name($name)
	{
		if((substr((string)$name, 0, 1) === "`") && (substr($name, (strlen((string)$name)-1), 1) === "`")){

			$name = str_replace("`", "", (string)$name);
		}

		return $name;
	}

	protected function strTV($str)
	{
		$hex = "";

		$l = strlen((string)$str);

		for ($i=0; $i<$l; $i++){

			if((ord($str[$i]) > 31) && (ord($str[$i]) < 127)){

				$hex = $hex.$str[$i];
			}
			else{

				$hex = $hex.'.';
			}
		}


		return $hex;
	}
}
