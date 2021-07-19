<?php

defined('_EXEC') or die();

trait Wr_sql
{
	public function connect($SERVER)
	{
		$this->_LOG["MESSAGE"]["connect"] = "";

		if(!extension_loaded("mysqli")){

			$this->connect = true;
			$this->_LOG["MESSAGE"]["connect"] = "Module PHP mysqli is not installed";

			return;
		}

		$this->dbc = mysqli_init();

		if((isset($SERVER["ssl-key"]) && ($SERVER["ssl-key"] !== "")) &&
			(isset($SERVER["ssl-cert"]) && ($SERVER["ssl-cert"] !== "")) &&
			(isset($SERVER["ssl-ca"]) && ($SERVER["ssl-ca"] !== ""))){

			$this->dbc->ssl_set( $SERVER["ssl-key"], $SERVER["ssl-cert"], $SERVER["ssl-ca"], NULL, NULL);
		}

		if(!isset($SERVER["port"]) || ($SERVER["port"] === "")){

			$SERVER["port"] = 3306;
		}

		if(!isset($SERVER["socket"]) || ($SERVER["socket"] === "")){

			$SERVER["socket"] = NULL;
		}

		$CLIENT_SSL = NULL;
		if(isset($SERVER["require_secure_transport"]) && $SERVER["require_secure_transport"]){

			$CLIENT_SSL = MYSQLI_CLIENT_SSL;
		}

		$this->dbc->real_connect(
			$SERVER["host"], $SERVER["user"], $SERVER["pass"], "", $SERVER["port"], $SERVER["socket"], $CLIENT_SSL);

		if(!mysqli_connect_errno()){

			if(isset($SERVER["charset"]) && ($SERVER["charset"] !== "")){

				$this->dbc->set_charset($SERVER["charset"]);
			}

			$this->character_name = $this->character_name();

			$sql_mode = $this->request("SELECT @@session.sql_mode", __LINE__);
			if($sql_mode[0]){

				$this->sql_mode = $this->fetch_row($sql_mode[1])[0];
			}

			$this->client_info = $this->client_info();
			$this->server_info = $this->server_info();
		}
		else{

			$this->connect = true;

			$this->_LOG["MESSAGE"]["connect"] = _MESSAGE_CONNECTION.": ".$SERVER["host"]."@".$SERVER["user"];
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
			$ST = "<br><br><b>".preg_replace("/".PHP_EOL."/", "", htmlentities($script))."</b><br>";

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

			$script = $this->html(trim($script), "\n", "<br>");

			$this->_LOG["MESSAGE"][] = "<br><br><b>".htmlentities($this->dbc->error)."</b><br><br>".$script;
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

}
