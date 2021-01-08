<?php

defined('_EXEC') or die();


trait Wr_sql
{
	public function connectdb($SERVER)
	{
		if(!extension_loaded("mysqli")){

			$this->connect = true;
			$this->_LOG["MESSAGE"][] = "Module PHP mysqli is not installed";

			return;
		}

		$this->dbc = new mysqli($SERVER["host"], $SERVER["user"], $SERVER["pass"], "", $SERVER["port"]);

		if(!mysqli_connect_errno()){

			$this->dbc->set_charset("utf8");
			$this->dbc->query( "SET sql_mode = 'STRICT_ALL_TABLES';" );
			$this->server_version = $this->dbc->server_version;
		}
		else{

			$this->connect = true;

			$this->_LOG["MESSAGE"][] = _MESSAGE_CONNECTION.": ".$SERVER["host"]."@".$SERVER["user"];
		}
	}

	protected function use_db($db)
	{
		if($db !== ""){ $this->dbc->real_query("USE `".$db."`;"); }
	}

	protected function request($sql, $line, $log = true)
	{
		$line = "";

		if($log){$this->_LOG["SQL"][] = $sql;}

		$this->dbc->real_query($sql);
		$result = $this->dbc->store_result();

		if($this->dbc->error){

			if($log){$this->_LOG["MESSAGE"][] = $line.htmlentities($this->dbc->error, ENT_SUBSTITUTE);}

			return [false, false];
		}

		return [true, $result];
	}


	protected function get_sub($_DB, $_DBS, $tb, $target, $create, $searching, $add)
	{
		$RT = [];

		$result = $this->request("SELECT ".$target."_NAME
				FROM information_schema.".$tb." WHERE ".$add." ".$target."_SCHEMA=x'".$_DB."';", __LINE__, false);

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


	public function priv($SERVER)
	{
		$PRIVILEGES = [];

		$result = $this->request("SELECT ".
			"Select_priv, Insert_priv, Update_priv, Delete_priv, Create_priv, Drop_priv, Grant_priv ".
			"FROM `mysql`.`user` ".
			"WHERE (`Host`='".$SERVER["host"]."' OR `Host`='%') ".
			"AND `User`='".$SERVER["user"]."';", __LINE__, false);

		if($result[0]){ $PRIVILEGES = $result[1]->fetch_assoc(); }

		return $PRIVILEGES;
	}

}