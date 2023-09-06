<?php

defined('_EXEC') or die();

trait Wr_sql
{
	public function connect($SERVER)
	{
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

			if(isset($SERVER["variables"]) && (count($SERVER["variables"]) !== 0)){

				foreach($SERVER["variables"] as $k=>$v){

					if(strtolower($k) === "names"){

						$this->dbc->set_charset($v);
					}
					else{

						if(is_int($v)){

							$this->dbc->query( "SET ".$k." = ".$v.";" );
						}
						else{

							$this->dbc->query( "SET ".$k." = '".$v."';" );
						}
					}
				}
			}

			$this->character_name = $this->dbc->character_set_name();

			$sql_mode = $this->request("SELECT @@session.sql_mode", "", [], __LINE__);
			if($sql_mode[0]){

				$this->sql_mode = $this->fetch_row($sql_mode[1])[0];
			}

			$this->client_info = $this->dbc->client_info;
			$this->server_info = $this->dbc->server_info;
			
			$this->current_user	= $SERVER["user"]."@".$SERVER["host"].":".$SERVER["port"];			
		}
		else{

			$this->connect = true;

			$this->_LOG["MESSAGE"]["connect"] = _MESSAGE_CONNECTION.": ".$SERVER["host"]."@".$SERVER["user"];
		}
	}

	protected function request($sql, $type, $value, $line, $log = true)
	{
		$this->dbc->real_query($sql);
		$result = $this->dbc->store_result();

		if($this->dbc->error){

			$this->_LOG["MESSAGE"][] = htmlentities($this->dbc->error, ENT_SUBSTITUTE);
			
			return [false, $this->dbc->errno];
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


	private function multi_request($script)
	{
		$i = 0;		
		
		if( $this->dbc->multi_query( $script ) )
		{
			do 
			{
				$ST = "";

				$result = $this->dbc->store_result();
	
				if ($result) {

					while($row = $result->fetch_assoc())
					{
						foreach($row as $k=>$v){

							$ST .= htmlentities($k).": ".htmlentities($v, ENT_SUBSTITUTE)."<br>";
						}
						
						$ST .= "<br>";
					}		
				}

				if ($ST !== ""){ 
				
					$this->_LOG["RESULT"][] = $ST;
				}
		
				$i++;
			} 
			while ($this->dbc->next_result());
		}

		if( $this->dbc->errno )
		{
			$this->_LOG["MESSAGE"][] = 
				"Query: [".($i + 1)."]. Errno: [".$this->dbc->errno."]. '".htmlentities($this->dbc->error)."'}";	
		}		
	}

	public function close($result)
	{
		$result->close();
	}

	protected function stat()
	{
		return $this->dbc->stat();
	}

	protected function escape($v)
	{
		return $this->dbc->real_escape_string($v);
	}
}
