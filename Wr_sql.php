<?php

defined('_EXEC') or die();


Class Wr_sql
{
	public function __construct(){}	
	

	public function connectdb($SERVER)
	{
		if(!extension_loaded("mysqli")){

			$this->connect = true;
			$this->_LOG["MESSAGE"][] = "Module PHP mysqli is not installed";

			return;
		}

		$this->dbc = new mysqli($SERVER["host"], $SERVER["user"], $SERVER["pass"], "");

		if(!mysqli_connect_errno()){

			$this->dbc->set_charset("utf8");
			$this->dbc->query( "SET sql_mode = 'STRICT_ALL_TABLES';" );
			$this->server_version = $this->dbc->server_version;
		}
		else{

			$this->connect = true;
			$this->_LOG["MESSAGE"][] = _MESSAGE_CONNECTION_DB_ERROR.": ".$this->dbc->connect_errno;
		}
	}

	protected function use_db($db)
	{
		if($db !== ""){ $this->dbc->real_query("USE `".$db."`;"); }
	}

	protected function request($sql, $line, $error=true)
	{
		$this->_LOG["SQL"][] = $sql;

		$line = "";

		$this->dbc->real_query($sql);
		$result = $this->dbc->store_result();

		if($this->dbc->error){

			if($error){$this->_LOG["MESSAGE"][] = $line.htmlentities($this->dbc->error, ENT_SUBSTITUTE);}

			return [false, false];
		}

		return [true, $result];
	}


	protected function get_sub($_DB, $_DBS, $tb, $target, $create, $searching, $add)
	{
		$RT = [];

		$result = $this->request("SELECT ".$target."_NAME
				FROM information_schema.".$tb." where ".$add." ".$target."_SCHEMA=x'".$_DB."';", __LINE__, false);

		if($result[0])
		{
			while( $row = $result[1]->fetch_assoc() ){

				$trigger = $this->request($create." `$_DBS`.`".$row[$target."_NAME"]."`;", __LINE__);

				while( $row_trigger = $trigger[1]->fetch_assoc() ){

					$RT[$row[$target."_NAME"]] = $row_trigger[$searching];
				}
			}
		}

		return $RT;
	}
	
	
}