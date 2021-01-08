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

			return preg_replace("/".$EL."/", $DL, htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE));
		}
		else{

			return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE);
		}
	}


	protected function set_value($value)
	{
		if(get_magic_quotes_gpc() === 1){

			return stripslashes(trim($value));
		}
		else{

			return trim($value);
		}
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
		if((substr($name, 0, 1) === "`") && (substr($name, (strlen ($name)-1), 1) === "`")){
			
			$name = str_replace("`", "", $name);
		}
		
		return $name;		
	}

}
