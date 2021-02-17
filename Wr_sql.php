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

	protected function fetch_assoc($result)
	{
		return $result->fetch_assoc();
	}

	protected function fetch_row($result)
	{
		return $result->fetch_row();
	}


	private function sqls_eval($script)
	{
		$this->dbc->real_query($script);
		$result = $this->dbc->store_result();

		if($result)
		{
			$ST = "<br><b>"._MESSAGE_EXECUTED."</b><br><br>".
				preg_replace("/".PHP_EOL."/", "", htmlentities($script))."<br>";

			while($row = $result->fetch_assoc())
			{
				$ST .= "<br>";
				foreach($row as $k=>$v){

					$ST .= htmlentities($k).": ".htmlentities($v, ENT_SUBSTITUTE)."<br>";
				}
			}

			$this->_LOG["RESULT"][] = $ST;
		}

		if($this->dbc->error){

			$script = $this->html($script, "\n", "<br>");

			$this->_LOG["MESSAGE"][] = htmlentities($this->dbc->error)."<br>".$script;
		}

		while($this->dbc->more_results()){

			$this->dbc->next_result();
			$this->dbc->use_result();
		}
	}


	public function close($result)
	{
		$result->close();
	}

	protected function client_info()
	{
		return $this->dbc->client_info;
	}

	protected function server_info()
	{
		return $this->dbc->server_info;
	}

	protected function character_name()
	{
		return $this->dbc->character_set_name();
	}

	protected function stat()
	{
		return $this->dbc->stat();
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
}
