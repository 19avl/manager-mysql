<?php

/*
Copyright (c) 2018-2021 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();


Class Manager
{
	use Convert;
	use Wr_sql;

	public function __construct()
	{
		$this->_LOG = [];
		$this->connect = false;
	}


	public function mk($script_id, $_sql)
	{
		$RT = [];
		$RT["LIST"] =  [];
		$RT["SCRIPT"] = "";

		$RT["LIST"] = array_keys($_sql);

		if($script_id !== ""){

			$RT["SCRIPT"] = $_sql[$script_id];
		}

		return $RT;
	}


	public function db($nv, $LIMIT)
	{
		$RT = [];
		$RT["DB"] = [];
		$RT["COUNT"] = "";
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["SCHEMA"];
		$RT["FIELD_ST"] = ["SCHEMA_NAME", "DEFAULT_CHARACTER_SET_NAME", "DEFAULT_COLLATION_NAME"];
		$RT["FIELD_ST_NAV"] = ["SCHEMA_NAME", "DEFAULT_CHARACTER_SET_NAME", "DEFAULT_COLLATION_NAME"];
		$RT["FIELD_SE"] = ["DEFAULT_CHARACTER_SET_NAME", "DEFAULT_COLLATION_NAME"];
		$RT["FILTER_EX"] = ["...","=","<>","LIKE"];

		if($nv["page_db"] === "0"){$nv["page_db"] = $RT["ON_PAGE"][0];}

		if(in_array($nv["fl_operator_db"], $RT["FILTER_EX"])){

			$WHERE = ($nv["fl_operator_db"] === "LIKE") ?
				"WHERE `".$this->h2s($nv["fl_field_db"])."` LIKE '%".addslashes($nv["fl_value_db"])."%'" :
				"WHERE `".$this->h2s($nv["fl_field_db"])."`".$nv["fl_operator_db"]."'".
				addslashes($nv["fl_value_db"])."'";
		}
		else{ $WHERE = ""; }


		$result = $this->request("SELECT COUNT(*) FROM information_schema.SCHEMATA ".$WHERE.";", __LINE__);

		if($result[0]){

			$RT["COUNT"] = $this->fetch_row($result[1])[0];

			if($RT["COUNT"] <= $nv["from_db"]){$nv["from_db"] = "0";}
		}

		$result = $this->request("SELECT
			SCHEMA_NAME, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
			FROM information_schema.SCHEMATA ".$WHERE." ORDER BY ".
			($nv["order_db"]+1)." LIMIT ".$nv["from_db"].", ".$nv["page_db"].";", __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) )
			{
				$RT["DB"][$row["SCHEMA_NAME"]] = $row;
				$RT["DB"][$row["SCHEMA_NAME"]]["COUNT"] = "";

				$lines = $this->request("SELECT COUNT(*)
					FROM information_schema.TABLES WHERE `TABLE_SCHEMA`=x'".
					$this->s2h($row["SCHEMA_NAME"])."';", __LINE__);

				if($lines[0]){

					$RT["DB"][$row["SCHEMA_NAME"]]["COUNT"] = $this->fetch_row($lines[1])[0];
				}
			}
		}

		$count_page = 0;
		do{

			$RT["FROM"][] = $count_page;
			$count_page = $count_page + $nv["page_db"];
		}
		while($count_page < $RT["COUNT"]);

		return $RT;
	}


	public function status()
	{
		return $this->stat();
	}


	public function info()
	{
		$RT = [];

		if(isset($GLOBALS["_SERVER"]["SERVER_SOFTWARE"])){

			$RT[] = $GLOBALS["_SERVER"]["SERVER_SOFTWARE"];
		}

		$RT[] = "&nbsp;";

		$RT[] = "CLIENT_INFO: ".$this->client_info;
		$RT[] = "SERVER_INFO: ".$this->server_info;
		$RT[] = "CHARACTER_NAME: ".$this->character_name;

		$sql_mode = $this->request("SELECT @@session.sql_mode", __LINE__);
		if($sql_mode[0]){

			$RT[] = "SQL_MODE: ".str_replace(",", ", ", $this->fetch_row($sql_mode[1])[0]);
		}

		$RT[] = "&nbsp;";

		$result = $this->request("SHOW STATUS like 'Ssl_cipher';", __LINE__);
		if($result[0]){

			$ssl_cipher = $this->fetch_row($result[1])[1];

			if($ssl_cipher !== ""){

				$RT[] = "SSL_CIPHER: ".$ssl_cipher;
			}
			else{

				$RT[] = "SSL_CIPHER: "._NOTE_IN_USE;
			}
		}

		$result = $this->request("SELECT CURRENT_USER();", __LINE__);
		if($result[0]){

			$user = $this->fetch_row($result[1])[0];

			$_user = explode("@", $user);

			$result = $this->request("SELECT ssl_type FROM mysql.user
				WHERE Host='".$_user[1]."' AND User='".$_user[0]."';", __LINE__, false);

			if($result[0]){

				$ssl_type = $this->fetch_row($result[1])[0];

				if($ssl_type !== "")
				{
					$RT[] = "SSL_TYPE: ".$ssl_type;
				}
				else{

					$RT[] = "SSL_TYPE: NULL";
				}
			}

			$RT[] = "&nbsp;";

			$result = $this->request("SHOW GRANTS FOR `".$_user[0]."`@`".$_user[1]."`;", __LINE__);

			if($result[0]){

				$RT[] = "".$this->fetch_row($result[1])[0];
			}
		}

		$result = $this->request("SELECT PRIVILEGE_TYPE, TABLE_SCHEMA
			FROM `information_schema`.`SCHEMA_PRIVILEGES`
			WHERE `GRANTEE` = '\'".$_user[0]."\'@\'".$_user[1]."\'';", __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT[] = $this->html($row["TABLE_SCHEMA"]).": ".$row["PRIVILEGE_TYPE"];
			}
		}

		$result = $this->request("SELECT PRIVILEGE_TYPE, TABLE_SCHEMA, TABLE_NAME
			FROM `information_schema`.`TABLE_PRIVILEGES`
			WHERE `GRANTEE` = '\'".$_user[0]."\'@\'".$_user[1]."\'';", __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT[] = $this->html($row["TABLE_SCHEMA"])." . ".
					$this->html($row["TABLE_NAME"]).": ".
					$row["PRIVILEGE_TYPE"];
			}
		}

		$result = $this->request("SELECT PRIVILEGE_TYPE, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME
			FROM `information_schema`.`COLUMN_PRIVILEGES`
			WHERE `GRANTEE` = '\'".$_user[0]."\'@\'".$_user[1]."\'';", __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT[] = $this->html($row["TABLE_SCHEMA"])." . ".
					$this->html($row["TABLE_NAME"])." . ".
					$this->html($row["COLUMN_NAME"]).": ".
					$row["PRIVILEGE_TYPE"];
			}
		}

		return $RT;
	}


	public function tb($_DB, $nv, $cl_sl, $LIMIT)
	{
		$_DBS = $this->h2s($_DB);

		$RT = [];

		$RT["DB"] = $_DBS;
		$RT["CREATE"] = [];
		$RT["EVENTS"] = [];
		$RT["TRIGGERS"] = [];
		$RT["PROCEDURE"] = [];
		$RT["FUNCTION"] = [];
		$RT["VIEWS"] = [];
		$RT["SUB"] = [];
		$RT["SUB"]["ID"] = "";
		$RT["SUB"]["NM"] = "";
		$RT["SUB"]["SL"] = "";
		$RT["SUB"]["PR"] = "";
		$RT["TABLES"] = [];
		$RT["COUNT"] = "";
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["TABLES"];
		$RT["FIELD_ST"] = ["table_name", "create_time", "update_time", "engine", "table_collation"];
		$RT["FIELD_ST_NAV"] = ["table_name", "create_time", "update_time", "engine", "table_collation"];
		$RT["FIELD_SE"] = ["CREATE_TIME", "UPDATE_TIME", "ENGINE", "TABLE_COLLATION", "AUTO_INCREMENT"];
		$RT["FILTER_EX"] = ["...","=","<>","LIKE"];
		$RT["PRIVILEGES"] = [];

		$VIEW = [];
		$OPEN_TABLES = [];

		$SUB_PR = [];
		$SUB_PR_SET = [];
		$SUB_PR_OUT = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", __LINE__);

		if($CREATE[0])
		{
			$RT["CREATE"]["DB"] = $this->fetch_row($CREATE[1])[1];

			$this->use_db($_DBS);

			$result = $this->request("SELECT TABLE_NAME
				FROM information_schema.VIEWS WHERE TABLE_SCHEMA=x'".$_DB."';", __LINE__, false);

			if($result[0])
			{
				while( $row = $result[1]->fetch_assoc() ){

					$CREATE = $this->request("SHOW CREATE TABLE `$_DBS`.`".$row["TABLE_NAME"]."`;", __LINE__);

					if($CREATE[0]){

						$RT["VIEWS"][$row["TABLE_NAME"]] = $this->fetch_row($CREATE[1])[1];
					}

					$VIEW[] = $row["TABLE_NAME"];
				}
			}

			$RT["EVENTS"] = $this->get_sub($_DB, $_DBS,
				"EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", "");

			$RT["TRIGGERS"] = $this->get_sub($_DB, $_DBS,
				"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER", "SQL Original Statement", "");

			$RT["PROCEDURE"] = $this->get_sub($_DB, $_DBS,
				"ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE", "Create Procedure", "ROUTINE_TYPE='PROCEDURE' AND");

			$RT["FUNCTION"] = $this->get_sub($_DB, $_DBS,
				"ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION", "Create Function", "ROUTINE_TYPE='FUNCTION' AND");

			if(isset($cl_sl["views"]) && ($cl_sl["views"] !== "")){

				$RT["SUB"]["NM"] = $cl_sl["views"];
				if(isset($RT["VIEWS"][$this->h2s($cl_sl["views"])])){

					$RT["SUB"]["SL"] = $RT["VIEWS"][$this->h2s($cl_sl["views"])];
				}

				$RT["SUB"]["ID"] = "views";
			}
			elseif(isset($cl_sl["events"]) && ($cl_sl["events"] !== "")){

				$RT["SUB"]["NM"] = $cl_sl["events"];
				if(isset($RT["EVENTS"][$this->h2s($cl_sl["events"])])){

					$RT["SUB"]["SL"] = $RT["EVENTS"][$this->h2s($cl_sl["events"])];
				}

				$RT["SUB"]["ID"] = "events";
			}
			elseif(isset($cl_sl["triggers"]) && ($cl_sl["triggers"] !== "")){

				$RT["SUB"]["NM"] = $cl_sl["triggers"];
				if(isset($RT["TRIGGERS"][$this->h2s($cl_sl["triggers"])])){

					$RT["SUB"]["SL"] = $RT["TRIGGERS"][$this->h2s($cl_sl["triggers"])];
				}
				$RT["SUB"]["ID"] = "triggers";
			}
			elseif(isset($cl_sl["procedure"]) && ($cl_sl["procedure"] !== "")){

				$RT["SUB"]["NM"] = $cl_sl["procedure"];
				if(isset($RT["PROCEDURE"][$this->h2s($cl_sl["procedure"])])){

					$RT["SUB"]["SL"] = $RT["PROCEDURE"][$this->h2s($cl_sl["procedure"])];

					$result = $this->request("SELECT PARAMETER_MODE, PARAMETER_NAME
						FROM information_schema.PARAMETERS where SPECIFIC_SCHEMA=x'".$_DB."'
						AND SPECIFIC_NAME=x'".$cl_sl["procedure"]."'
						AND PARAMETER_MODE<>'';", __LINE__);

					if($result[0]){

						while( $row = $this->fetch_assoc($result[1]) ){

							if(($row["PARAMETER_MODE"] === "OUT") || ($row["PARAMETER_MODE"] === "INOUT")){

								$SUB_PR[] = "@".$row["PARAMETER_NAME"];
								$SUB_PR_OUT[] = "@".$row["PARAMETER_NAME"]." AS `".$row["PARAMETER_NAME"]."`";

								if($row["PARAMETER_MODE"] === "INOUT"){

									$SUB_PR_SET[] = "SET @".$row["PARAMETER_NAME"]." = ".$row["PARAMETER_NAME"].";\n";
								}
							}
							else{

								$SUB_PR[] = $row["PARAMETER_NAME"];
							}
						}
					}

					$RT["SUB"]["PR"] =
						implode(" ",$SUB_PR_SET)."CALL `".$this->h2s($cl_sl["procedure"])."`(".implode(", ",$SUB_PR).");";

					if(count($SUB_PR_OUT) !== 0){

						$RT["SUB"]["PR"] .= " \nSELECT ".implode(", ",$SUB_PR_OUT).";";
					}
				}
				$RT["SUB"]["ID"] = "procedure";
			}
			elseif(isset($cl_sl["function"]) && ($cl_sl["function"] !== "")){

				$RT["SUB"]["NM"] = $cl_sl["function"];
				if(isset($RT["FUNCTION"][$this->h2s($cl_sl["function"])])){

					$RT["SUB"]["SL"] = $RT["FUNCTION"][$this->h2s($cl_sl["function"])];

					$result = $this->request("SELECT PARAMETER_MODE, PARAMETER_NAME
						FROM information_schema.PARAMETERS where SPECIFIC_SCHEMA=x'".$_DB."'
						AND SPECIFIC_NAME=x'".$cl_sl["function"]."'
						AND PARAMETER_MODE<>'';", __LINE__);

					if($result[0]){

						while( $row = $this->fetch_assoc($result[1]) ){

							$SUB_PR[] = $row["PARAMETER_NAME"];
						}
					}

					$RT["SUB"]["PR"] = "SELECT ".$this->h2s($cl_sl["function"])."(".implode(", ",$SUB_PR).");";
				}
				$RT["SUB"]["ID"] = "function";
			}

			$result = $this->request("SHOW OPEN TABLES FROM `$_DBS` WHERE In_use>0;", __LINE__);

			while( $row = $this->fetch_assoc($result[1]) ){ $OPEN_TABLES[] = $row["Table"];}

			if($nv["page_tb"] === "0"){$nv["page_tb"] = $RT["ON_PAGE"][0];}

			if(in_array($nv["fl_operator_tb"], $RT["FILTER_EX"])){

				$WHERE = ($nv["fl_operator_tb"] === "LIKE") ?
					"AND `".$this->h2s($nv["fl_field_tb"])."` LIKE '%".addslashes($nv["fl_value_tb"])."%'" :
					"AND `".$this->h2s($nv["fl_field_tb"])."`".$nv["fl_operator_tb"]."'".
					addslashes($nv["fl_value_tb"])."'";
			}
			else{ $WHERE = ""; }

			$result = $this->request("SELECT COUNT(*)
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_DB."' ".$WHERE." ;", __LINE__);

			if($result[0]){

				$RT["COUNT"] = $this->fetch_row($result[1])[0];

				if($RT["COUNT"] <= $nv["from_tb"]){$nv["from_tb"] = "0";}
			}

			$result = $this->request("SELECT
				TABLE_NAME, CREATE_TIME, UPDATE_TIME, ENGINE, TABLE_COLLATION, AUTO_INCREMENT
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_DB."' ".$WHERE." ORDER BY ".
				($nv["order_tb"]+1)." LIMIT ".$nv["from_tb"].", ".$nv["page_tb"].";", __LINE__);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) )
				{
					$RT["TABLES"][$row["TABLE_NAME"]] = $row;
					$RT["TABLES"][$row["TABLE_NAME"]]["COUNT"] = "";

					if(in_array($row["TABLE_NAME"], $VIEW)){

						$RT["TABLES"][$row["TABLE_NAME"]]["COUNT"] = "VIEW";
					}
					elseif(in_array($row["TABLE_NAME"], $OPEN_TABLES)){

						$RT["TABLES"][$row["TABLE_NAME"]]["COUNT"] = "IN USE";
					}
					else{

						$lines = $this->request("SELECT COUNT(*) FROM `$_DBS`.`".$row["TABLE_NAME"]."`;", __LINE__);

						if($lines[0]){

							$RT["TABLES"][$row["TABLE_NAME"]]["COUNT"] = $this->fetch_row($lines[1])[0];
						}
					}
				}
			}

			$count_page = 0;
			do{

				$RT["FROM"][] = $count_page;
				$count_page = $count_page + $nv["page_tb"];
			}
			while($count_page < $RT["COUNT"]);
		}

		return $RT;
	}


	public function rc($_DB, $_TB, $nv, $exceptions, $LIMIT, $mode)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->set_name($this->h2s($_TB));

		$RT = [];
		$RT["DB"] = $_DBS;
		$RT["TB"] = $_TBS;
		$RT["CREATE"] = [];
		$RT["PRI"] = false;
		$RT["EXCEPT_GEO"] = false;
		$RT["EXCEPT_BIN"] = false;
		$RT["TABLE_TYPE"] = "";
		$RT["ENGINE"] = "";
		$RT["FIELDS"] = [];
		$RT["RECORDS"] = [];
		$RT["RECORD_NEW"] = [];
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["RECORDS"];
		$RT["COUNT"] = 0;
		$RT["FIELD_ST"] = [];
		$RT["FIELD_ST_NAV"] = [];
		$RT["FILTER_EX"] = ["...","=","<>",">","<","LIKE"];
		$RT["PRIVILEGES"] = [];
		$RT["DB_LIST"] = [];

		if($mode === "")
		{
			$RT["ALTER_TABLE"] = [
			"ADD"=>[
				"ADD PRIMARY KEY (`...`);",
				"ADD CONSTRAINT `...` \nFOREIGN KEY (`...`) \nREFERENCES `...` (`...`);",
				"ADD UNIQUE (`...`);",
				"ADD\n... \nFIRST;",
			],
			"CHANGE"=>[],
			"DROP"=>[]
			];
		}

		$CONSTRAINT = [];
		$LIST = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", __LINE__);

		$RT["CREATE"]["DB"] = "";
		if($CREATE[0]){

			$RT["CREATE"]["DB"] = $this->fetch_row($CREATE[1])[1];
		}

		$CREATE = $this->request("SHOW CREATE TABLE `$_DBS`.`$_TBS`;", __LINE__);

		$RT["CREATE"]["TB"] = "";
		if($CREATE[0])
		{
			$RT["CREATE"]["TB"] = $this->fetch_row($CREATE[1])[1];

			$create_tb = explode("\n", $RT["CREATE"]["TB"]);

			$prc = 0;
			$fpr = 0;
			$str = "";

			foreach($create_tb as $v)
			{
				if(preg_match("/^\) ENGINE=/", $v) || preg_match("/^\) \/\*\!/", $v) || ($fpr === 1)){

					$fpr = 1;
					$str .= $v."\n";

					if(preg_match("/^\/\*\![0-9]{5} PARTITION /",$v)){$prc = 1;}
				}
			}

			$result = $this->request("SELECT `TABLE_TYPE`, `ENGINE` FROM information_schema.TABLES where
				TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$_TB."';", __LINE__);

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT["TABLE_TYPE"] = $row["TABLE_TYPE"];
				$RT["ENGINE"] = $row["ENGINE"];
			}

			if($mode === "")
			{
				if($str !== ""){

					array_unshift($RT["ALTER_TABLE"]["CHANGE"], substr($str, 2));
				}

				if($prc === 1){

					array_push($RT["ALTER_TABLE"]["DROP"], "REMOVE PARTITIONING;");
				}

				$result = $this->request("SELECT kc.COLUMN_NAME, tc.CONSTRAINT_NAME, tc.CONSTRAINT_TYPE
					FROM information_schema.TABLE_CONSTRAINTS tc JOIN information_schema.KEY_COLUMN_USAGE kc
					ON tc.TABLE_SCHEMA = kc.TABLE_SCHEMA AND tc.TABLE_NAME = kc.TABLE_NAME
					AND tc.CONSTRAINT_NAME = kc.CONSTRAINT_NAME
					WHERE tc.TABLE_SCHEMA = x'".$_DB."' AND tc.TABLE_NAME = x'".$_TB."';", __LINE__);

				while( $row = $this->fetch_row($result[1]) ){

					$CONSTRAINT[$row[0]][] = $row[2];

					if(trim($row[2]) === "FOREIGN KEY"){

						array_push($RT["ALTER_TABLE"]["DROP"], "DROP FOREIGN KEY `".$row[1]."`;");
					}
					elseif(trim($row[2]) === "UNIQUE"){

						array_push($RT["ALTER_TABLE"]["DROP"], "DROP INDEX `".$row[1]."`;");
					}
					elseif((trim($row[2]) === "PRIMARY KEY") && (!in_array("DROP PRIMARY KEY;", $RT["ALTER_TABLE"]["DROP"]))){

						array_push($RT["ALTER_TABLE"]["DROP"], "DROP PRIMARY KEY;");
					}
				}
			}

			$result = $this->request("select
				COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_KEY, COLUMN_DEFAULT, IS_NULLABLE, EXTRA, NUMERIC_PRECISION
				from information_schema.columns where TABLE_SCHEMA=x'".$_DB."'
				AND table_name = x'".$_TB."' ORDER BY ORDINAL_POSITION;", __LINE__);

			if($result[0])
			{
				while($row = $this->fetch_assoc($result[1]))
				{
					$RT["FIELDS"][$row["COLUMN_NAME"]] = $row;

					if($mode === "")
					{
						array_push($RT["ALTER_TABLE"]["ADD"], "ADD\n... \nAFTER `".$row["COLUMN_NAME"]."`;");
						array_push($RT["ALTER_TABLE"]["CHANGE"], "CHANGE COLUMN `".$row["COLUMN_NAME"]."` \n...\n;");
						array_push($RT["ALTER_TABLE"]["DROP"], "DROP COLUMN `".$row["COLUMN_NAME"]."`;");

						if(!in_array( $row["DATA_TYPE"], $exceptions["bin"])){

							$RT["FIELD_ST_NAV"][] = $row["COLUMN_NAME"];
						}

						$RT["FIELD_ST"][] = $row["COLUMN_NAME"];

						if((($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "varbinary") ||
							($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "binary")) &&
							($RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] != ""))
						{
							if($this->server_info[0] > 5){

								$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] =
									$this->h2s(preg_replace("/^0x/", "", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"]));
							}
						}

						if(($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "bit") &&
							($RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] != ""))
						{
							$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] =
								preg_replace("/^b'/", "", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"]);

							$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] =
								preg_replace("/'$/", "", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"]);
						}

						$RT["RECORD_NEW"][0][$row["COLUMN_NAME"]] = $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"];
					}

					if(isset($CONSTRAINT[$row["COLUMN_NAME"]])){

						$RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"] = $CONSTRAINT[$row["COLUMN_NAME"]];
					}
					else{

						$RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"] = [];
					}

					$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_TYPE"] =
						$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_TYPE"];

					$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = [];

					if(($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "enum") ||
						($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "set")){

						$temp = preg_replace("/^(enum|set)\('/", "", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_TYPE"]);
						$temp = preg_replace("/'\)$/", "", $temp);
						$temp = str_replace("\\\\", "\\", $temp);

						$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = explode("','", $temp);

						for($i=0;$i<count($RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"]);$i++){

							$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"][$i] =
								preg_replace("/\'\'/", "'", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"][$i]);
						}
					}

					$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = false;

					if(in_array("FOREIGN KEY", $RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"]))
					{
						$cnt = $this->request("SELECT
							REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
							FROM information_schema.KEY_COLUMN_USAGE
							WHERE TABLE_SCHEMA = x'".$_DB."' AND TABLE_NAME = x'".$_TB."' AND
							COLUMN_NAME = '".$row['COLUMN_NAME']."' AND
							CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME is not null;", __LINE__);

						$row_constraint = $this->fetch_assoc($cnt[1]);

						$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = [];

						if($row_constraint["REFERENCED_COLUMN_NAME"] && ($row_constraint["REFERENCED_COLUMN_NAME"] !== ""))
						{
							$constraint_value = $this->request("SELECT ".
								$row_constraint["REFERENCED_COLUMN_NAME"]." FROM `".
								$row_constraint["REFERENCED_TABLE_SCHEMA"]."`.`".
								$row_constraint["REFERENCED_TABLE_NAME"]."`;", __LINE__);

							if($constraint_value[0]){

								while($row_constraint_value = $this->fetch_row($constraint_value[1])){

									$RT["FIELDS"][$row['COLUMN_NAME']]["COLUMN_VALUE"][] = $row_constraint_value[0];
								}
							}

							$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = true;
						}
					}

					if($row["COLUMN_KEY"] == "PRI"){$RT["PRI"] = true;}

					if(in_array( $row["DATA_TYPE"], $exceptions["geo"])){$RT["EXCEPT_GEO"] = true;}
					if(in_array( $row["DATA_TYPE"], $exceptions["bin"])){$RT["EXCEPT_BIN"] = true;}

					if($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "bit"){

						$LIST[] = "LPAD(BIN(`".$row["COLUMN_NAME"]."`), ".
							$RT["FIELDS"][$row["COLUMN_NAME"]]["NUMERIC_PRECISION"].", '0') AS `".
							$row["COLUMN_NAME"]."`";
					}
					elseif($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "float"){

						$LIST[] = "abs(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
					}
					elseif(in_array( $row["COLUMN_TYPE"], $exceptions["geo"])){

						$LIST[] = "ST_AsText(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
					}
					else{

						$LIST[] = "`".$row["COLUMN_NAME"]."`";
					}
				}

				if(($RT["TABLE_TYPE"] !== "VIEW") && ($mode === "")){

					if(!$RT["PRI"]){ $this->_LOG["MESSAGE"][] = _MESSAGE_MISSING_PRI; }
				}
			}

			if(in_array($nv["fl_operator_rc"], $RT["FILTER_EX"])){

				$WHERE = $this->get_wr($nv, $RT["FIELDS"], $exceptions);
			}
			else{ $WHERE = ""; }

			if($nv["page_rc"] === "0"){$nv["page_rc"] = $RT["ON_PAGE"][0];}
			elseif($nv["page_rc"] === "1"){array_push($RT["ON_PAGE"], "1");}

			$result = $this->request("SELECT COUNT(*)
				FROM `$_DBS`.`$_TBS` ".$WHERE." ;", __LINE__);

			if($result[0]){

				$RT["COUNT"] = $this->fetch_row($result[1])[0];

				if($RT["COUNT"] <= $nv["from_rc"]){$nv["from_rc"] = "0";}
			}

			$LIMIT = " LIMIT ".$nv["from_rc"].", ".$nv["page_rc"];

			if($mode !== ""){$LIMIT = "";}

			$this->use_db($_DBS);

			$ORDER_LIST	= array_keys($LIST);
			unset($ORDER_LIST[0]);

			if(count($ORDER_LIST) === 0){$order_list_st = "1";}
			else{$order_list_st = ($nv["order_rc"]+1).",".implode(", ", $ORDER_LIST);}

			$result = $this->request("SELECT ".implode(", ",  $LIST).
				" FROM `".$_DBS."`.`".$_TBS."` ".$WHERE." ORDER BY ".$order_list_st.$LIMIT.";", __LINE__);

			if($result[0]){

				while($res = $this->fetch_assoc($result[1])){

					$RT["RECORDS"][] = $res;
				}
			}

			if($mode === "")
			{
				$count_page = 0;

				do{

					$RT["FROM"][] = $count_page;
					$count_page = $count_page + $nv["page_rc"];
				}
				while($count_page < $RT["COUNT"]);

				if(count($RT["RECORDS"]) === 0){

					foreach($RT["FIELDS"] as $k=>$v){

						$RT["RECORDS"][0][$k] = "";
					}
					$RT["COUNT"] = 0;
				}
			}
		}

		$result = $this->request("SELECT CURRENT_USER();", __LINE__);
		if($result[0]){

			$user = $this->fetch_row($result[1])[0];

			$_user = explode("@", $user);
		}

		$result = $this->request("SELECT PRIVILEGE_TYPE, COLUMN_NAME
			FROM `information_schema`.`COLUMN_PRIVILEGES`
			WHERE `GRANTEE` = '\'".$_user[0]."\'@\'".$_user[1]."\''
			AND TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$_TB."';", __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT["PRIVILEGES"]["COLUMN_PRIVILEGES"][] = $row["PRIVILEGE_TYPE"]."-".$row["COLUMN_NAME"];
			}
		}

		$db_list = $this->request("SHOW DATABASES;", __LINE__);
		if($db_list[0]){

			while( $row = $this->fetch_assoc($db_list[1]) ){

				$RT["DB_LIST"][] = $row["Database"];
			}
		}

		if($mode !== ""){ $this->_LOG["MESSAGE"] = []; }

		return $RT;
	}


	public function get_list_tb($_DB)
	{
		$RT = [];

		$_DBS = $this->h2s($_DB);

		$result = $this->request("SHOW TABLES FROM `$_DBS`;", __LINE__);

		if($result[0]){

			while($row = $this->fetch_row($result[1])){

				$RT[] = $this->s2h($row[0]);
			}
		}
		return $RT;
	}


	public function searching($_DB, $list_tb, $find, $mode)
	{
		$_DBS = $this->h2s($_DB);

		$count = 0;

		$find = trim($find);

		$this->_LOG["RESULT"][] = "<br>"._MESSAGE_SEARCHING.": ".$this->html($find)."<br>";

		if(isset($list_tb))
		{
			foreach($list_tb as $val)
			{
				$valS = $this->h2s($val);

				$result = $this->request("SELECT * FROM `$_DBS`.`$valS`;", __LINE__);

				if($result[0])
				{
					while($row = $this->fetch_assoc($result[1]))
					{
						$str = "";

						foreach($row as $k=>$v)
						{
							$str = $v;

							if($mode === "0")
							{
								if(stristr($str, $find)){

									$this->_LOG["RESULT"][] = $this->html($_DBS).".".$this->html($valS).
										":<br>[ ".$this->html($k)." ] - ".$this->html($v);

									$count += 1;
								}
							}
							elseif($mode === "1")
							{
								if($str === trim($find)){

									$this->_LOG["RESULT"][] = $this->html($_DBS).".".$this->html($valS).
										":<br>[ ".$this->html($k)." ] - ".$this->html($v);

									$count += 1;
								}
							}
						}
					}
				}
			}
		}

		if($count == 0){ $this->_LOG["RESULT"][] = _MESSAGE_FIND_NOT_FOUND." ".$this->html($find); }
	}


	public function copy_tb($_DB, $list_tb, $copy_2bd, $name_new, $pre=false)
	{
		$_DBS = $this->h2s($_DB);
		$copy_2bdS = $this->h2s($copy_2bd);

		$name_new = $this->set_name($name_new);

		if( isset($list_tb) )
		{
			foreach($list_tb as $val)
			{
				$result = $this->request("SELECT TABLE_TYPE, ENGINE
					FROM information_schema.TABLES where
					TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$val."';", __LINE__);

				$TABLE_TYPE = "";
				$ENGINE = "";

				if($result[0]){

					while($row = $this->fetch_assoc($result[1])){

						$TABLE_TYPE = $row["TABLE_TYPE"];
						$ENGINE = $row["ENGINE"];
					}
				}

				if(($TABLE_TYPE !== "VIEW") && ($ENGINE !== "MRG_MyISAM") && ($ENGINE !== "MRG_MYISAM"))
				{
					$valS = $this->h2s($val);

					if($val !== "")
					{
						if($pre === false){

							$tbs_new = $valS;

						}
						elseif($pre === true){

							if(($name_new === $valS)){ $tbs_new = $valS."_copy"; }
							else{ $tbs_new = $name_new; }
						}

						$result = $this->request(
							"CREATE TABLE `".$copy_2bdS."`.`".$tbs_new."` LIKE `".$_DBS."`.`".$valS."`;", __LINE__);

						if($result[0])
						{
							$result = $this->request("SET FOREIGN_KEY_CHECKS=0;", __LINE__);

							$ex = $this->request("select COLUMN_NAME FROM information_schema.COLUMNS where
								TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$val."'
								AND EXTRA<>'STORED GENERATED' AND EXTRA<>'VIRTUAL GENERATED'
								ORDER BY ORDINAL_POSITION;", __LINE__);

							$_EX = [];

							if($ex[0]){

								while($row_ex = $this->fetch_row($ex[1])){

									$_EX[] = $row_ex[0];
								}
							}

							$this->request(
								"INSERT INTO `".$copy_2bdS."`.`".$tbs_new."` (`".implode("`,`", $_EX)."`)
								SELECT `".implode("`,`", $_EX)."` FROM `".$_DBS."`.`".$valS."`;", __LINE__);

							$constraint = $this->request("SELECT
								COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME,
								REFERENCED_COLUMN_NAME, CONSTRAINT_NAME
								FROM information_schema.KEY_COLUMN_USAGE
								WHERE TABLE_SCHEMA = x'".$_DB."' AND TABLE_NAME = x'".$val."' AND
								CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME is not null;", __LINE__);

							while($row_constraint = $this->fetch_assoc($constraint[1]))
							{
								$referent = $this->request("SELECT UPDATE_RULE, DELETE_RULE
									FROM information_schema.REFERENTIAL_CONSTRAINTS
									WHERE CONSTRAINT_SCHEMA = x'".$_DB."'
									AND TABLE_NAME = x'".$val."'
									AND CONSTRAINT_NAME='".$row_constraint["CONSTRAINT_NAME"]."';", __LINE__);

								$row_referent = $this->fetch_assoc($referent[1]);

								$action = "ON UPDATE ".$row_referent["UPDATE_RULE"]." ON DELETE ".$row_referent["DELETE_RULE"];

								if(($_DBS !== $copy_2bdS) && ($row_constraint["REFERENCED_TABLE_SCHEMA"] === $_DBS)){

									$row_constraint["REFERENCED_TABLE_SCHEMA"] = $copy_2bdS;
								}

								$this->request("ALTER TABLE `".$copy_2bdS."`.`".$tbs_new."` ADD CONSTRAINT ".
								$row_constraint["CONSTRAINT_NAME"]."0 FOREIGN KEY (`".
								$row_constraint["COLUMN_NAME"]."`) REFERENCES `".
								$row_constraint["REFERENCED_TABLE_SCHEMA"]."`.`".
								$row_constraint["REFERENCED_TABLE_NAME"]."` (`".
								$row_constraint["REFERENCED_COLUMN_NAME"]."`) ".$action.";", __LINE__);
							}

							$result = $this->request("SET FOREIGN_KEY_CHECKS=1;", __LINE__);
						}
					}
				}
			}
		}
	}


	public function copy_db($_db, $name_new)
	{
		$valueS = $this->h2s($_db);

		$name_new = $this->set_name($name_new);

		if($name_new === $valueS){

			$dbs_new = $valueS."_copy";
		}
		else{$dbs_new = $name_new;}

		$db_new = $this->s2h($dbs_new);

		$result = $this->request("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
			FROM information_schema.SCHEMATA where SCHEMA_NAME=x'".$_db."';", __LINE__);

		if($result[0])
		{
			$schema = $this->fetch_row($result[1]);

			$result = $this->request(
				"CREATE DATABASE `".$dbs_new."` CHARACTER SET ".$schema[0]." COLLATE ".$schema[1].";", __LINE__);

			if($result[0])
			{
				$this->use_db($dbs_new);

				$this->copy_tb($_db, $this->get_list_tb($_db), $db_new, "", false);

				$triggers = $this->get_sub($_db, $valueS,
					"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER", "SQL Original Statement", "");

				foreach($triggers as $vt){$result = $this->request($vt, __LINE__);}

				$procedure = $this->get_sub($_db, $valueS,
					"ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE", "Create Procedure",
					"ROUTINE_TYPE='PROCEDURE' AND");

				foreach($procedure as $vt){$result = $this->request($vt, __LINE__);}

				$function = $this->get_sub($_db, $valueS,
					"ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION", "Create Function",
					"ROUTINE_TYPE='FUNCTION' AND");

				foreach($function as $vt){$result = $this->request($vt, __LINE__);}


				$events = $this->get_sub($_db, $valueS,
					"EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", "");

				foreach($events as $vt){$result = $this->request($vt, __LINE__);}
			}
		}
	}


	private function export_get($filename, $string)
	{
		header("Content-Type: text/html");

		header("Content-Disposition: attachment; filename=".$filename);
		header("Content-Transfer-Encoding: binary");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Length: ".strlen($string));
		print $string;

		die();
	}


	public function export($_DB, $list_tb, $nv, $exceptions, $mode, $type)
	{
		$RT = [];

		$LIMIT = ["RECORDS"=>["1"]];

		foreach($list_tb as $val)
		{
			$RT[] = $this->rc( $_DB, $val, $nv, $exceptions, $LIMIT, $mode );
		}

		return $RT;
	}


	private function default_value($column_default, $value)
	{
		if(($column_default === NULL) && ($value === NULL)){

			return 'NULL';
		}
		elseif($column_default === "0"){

			return 0;
		}
		else{

			return "''";
		}
	}


	public function export_sql($list_db, $list_tb, $nv, $exceptions, $mode)
	{
		$filename = date("d-m-Y").".sql";

		$string = PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';";

		$string .= PHP_EOL."SET FOREIGN_KEY_CHECKS=0;".PHP_EOL;

		foreach($list_db as $value)
		{
			$_DBS = $this->h2s($value);

			if($value !== $this->s2h("information_schema"))
			{
				if($mode === "DB"){

					$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", __LINE__);

					if($CREATE[0]){

						$string .= PHP_EOL.$this->fetch_row($CREATE[1])[1].";";

						$string .= PHP_EOL."USE `$_DBS`;";
					}

					$RT = $this->export($value, $this->get_list_tb($value), $nv, $exceptions, $mode, "sql");
				}
				else{

					$RT = $this->export($value, $list_tb, $nv, $exceptions, $mode, "sql");
				}

				foreach($RT as $k=>$v)
				{
					$string .= PHP_EOL.PHP_EOL.$v["CREATE"]["TB"].";".PHP_EOL;
					$row = [];

					if(($v["TABLE_TYPE"] !== "VIEW") && ($v["ENGINE"] !== "MRG_MyISAM") && ($v["ENGINE"] !== "MRG_MYISAM"))
					{
						foreach($v["RECORDS"] as $kr=>$vr)
						{
							$vrex = [];

							foreach($vr as $kf=>$vf)
							{
								if(($v["FIELDS"][$kf]["EXTRA"] === "VIRTUAL GENERATED") ||
									($v["FIELDS"][$kf]["EXTRA"] === "STORED GENERATED")){

								}
								elseif(in_array($v["FIELDS"][$kf]["COLUMN_TYPE"], $exceptions["geo"])){

									$geo_function = "ST_GeomFromText";

									if(($vf === "") || ($vf === "0") || ($vf == NULL)){

										$vrex[$kf] = $this->default_value($v["FIELDS"][$kf]["COLUMN_DEFAULT"], $vf);
									}
									else{

										$vrex[$kf] = $geo_function."('".$vf."')";
									}
								}
								elseif(($v["FIELDS"][$kf]["DATA_TYPE"] === "varbinary") ||
									preg_match("/blob$/", $v["FIELDS"][$kf]["DATA_TYPE"])){

									if(($vf === "") || ($vf === "0") || ($vf == NULL)){

										$vrex[$kf] = $this->default_value($v["FIELDS"][$kf]["COLUMN_DEFAULT"], $vf);
									}
									else{

										$vrex[$kf] = "x'".$this->s2h($vf)."'";
									}
								}
								elseif($v["FIELDS"][$kf]["DATA_TYPE"] === "bit"){

									if(($vf === "") || ($vf === "0") || ($vf == NULL)){

										$vrex[$kf] = $this->default_value($v["FIELDS"][$kf]["COLUMN_DEFAULT"], $vf);
									}
									else{

										$vrex[$kf] = "b'".$vf."'";
									}
								}
								else{

									if(($vf === "") || ($vf === "0") || ($vf == NULL)){

										$vrex[$kf] = $this->default_value($v["FIELDS"][$kf]["COLUMN_DEFAULT"], $vf);
									}
									else{

										$vrex[$kf] = "'".addslashes($vf)."'";
									}
								}
							}

							$row[] = "(".implode(",", $vrex).")";
						}

						if(count($row) !== 0){

							$string .= PHP_EOL."insert into `".
								$v["TB"]."` (`".implode("`,`", array_keys($vrex))."`) values".
								PHP_EOL.implode(",".PHP_EOL, $row).";".PHP_EOL;
						}
					}
				}

				if($mode === "DB")
				{
					$this->use_db($_DBS);

					$trigger = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
						"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER",
						"SQL Original Statement", "")).";";

					if($trigger !== ";"){

						$string .= PHP_EOL."/* TRIGGER */".PHP_EOL;
						$string .= PHP_EOL.$trigger.PHP_EOL;
					}

					$procedure = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
						"ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE",
						"Create Procedure", "ROUTINE_TYPE='PROCEDURE' AND")).";";

					if($procedure !== ";"){

						$string .= PHP_EOL."/* PROCEDURES */".PHP_EOL;
						$string .= PHP_EOL.$procedure.PHP_EOL;
					}

					$function = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
						"ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION",
						"Create Function", "ROUTINE_TYPE='FUNCTION' AND")).";";

					if($function !== ";"){

						$string .= PHP_EOL."/* FUNCTIONS */".PHP_EOL;
						$string .= PHP_EOL.$function.PHP_EOL;
					}

					$events = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
						"EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", "")).";";

					if($events !== ";"){

						$string .= PHP_EOL."/* EVENTS */".PHP_EOL;
						$string .= PHP_EOL.$events.PHP_EOL;
					}
				}
			}
		}

		$string .= PHP_EOL."SET FOREIGN_KEY_CHECKS=1;".PHP_EOL;

		$this->export_get($filename, $string);
	}


	public function clear_db($list_db, $DS)
	{
		foreach($list_db as $val){

			$valS = $this->h2s($val);

			$result = $this->request("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
				FROM information_schema.SCHEMATA where SCHEMA_NAME=x'".$val."';", __LINE__);

			if($result[0])
			{
				$schema = $this->fetch_row($result[1]);

				$CREATE = "CREATE DATABASE `".$valS."` CHARACTER SET ".$schema[0]." COLLATE ".$schema[1].";";

				if(($val !== "") && (!in_array($valS, $DS))){

					$this->request("DROP DATABASE `$valS`;", __LINE__);

					$this->request($CREATE, __LINE__);
				}
			}
		}
	}


	public function delete_db($list_db, $DS)
	{
		foreach($list_db as $val)
		{
			$valS = $this->h2s($val);

			if(($val !== "") && (!in_array($valS, $DS))){

				$this->request("DROP DATABASE `$valS`;", __LINE__);
			}
		}
	}


	public function create_sub($_DB, $cl_df)
	{
		$_DBS = $this->h2s($_DB);
		$cl_df = "CREATE ".$cl_df;

		$this->use_db($_DBS);

		$this->request($cl_df, __LINE__);
	}

	public function update_sub($_DB, $cl_tr, $cl_in, $cl_df, $cl_dl)
	{
		$_DBS = $this->h2s($_DB);

		$cl_df = "CREATE ".$cl_df;
		$cl_dl = "CREATE ".$this->h2s($cl_dl);

		$this->use_db($_DBS);

		$this->delete_sub($_DB, $cl_tr, $cl_in);

		$result = $this->request($cl_df, __LINE__);

		if(!$result[0]){

			$this->request($cl_dl, __LINE__);
		}
	}

	public function delete_sub($_DB, $cl_tr, $cl_in)
	{
		$_DBS = $this->h2s($_DB);
		$cl_inS = $this->h2s($cl_in);

		$this->use_db($_DBS);

		if($cl_tr === "views"){

			$this->request("DROP VIEW `".$cl_inS."`;", __LINE__);
		}
		elseif($cl_tr === "events"){

			$this->request("DROP EVENT `".$cl_inS."`;", __LINE__);
		}
		elseif($cl_tr === "triggers"){

			$this->request("DROP TRIGGER `".$cl_inS."`;", __LINE__);
		}
		elseif($cl_tr === "procedure"){

			$this->request("DROP PROCEDURE `".$cl_inS."`;", __LINE__);
		}
		elseif($cl_tr === "function"){

			$this->request("DROP FUNCTION `".$cl_inS."`;", __LINE__);
		}
	}


	public function rename_tb($_DB, $tb_name, $tb_name_new, $DS)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $DS))
		{
			$tb_name = $this->h2s($tb_name);

			$tb_name_new = $this->set_name($tb_name_new);

			$this->use_db($_DBS);

			$result = $this->request("RENAME TABLE `".$tb_name."` TO `".$tb_name_new."`;", __LINE__);

			return $result[0];
		}
	}


	public function clear_tb($_DB, $list_tb, $DS)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $DS))
		{
			if( isset($list_tb) )
			{
				foreach($list_tb as $val)
				{
					$valS = $this->h2s($val);

					if($val !== ""){

						$result = $this->request("DELETE FROM `$_DBS`.`$valS`;", __LINE__, false);

						if(!$result[0]){

							$this->request("TRUNCATE `$_DBS`.`$valS`;", __LINE__);
						}
					}
				}
			}
		}
	}


	public function delete_tb($_DB, $list_tb, $DS)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $DS))
		{
			$VIEW = [];

			$result = $this->request("SELECT TABLE_NAME
				FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_DB."';", __LINE__);

			while( $row = $this->fetch_assoc($result[1]) ){ $VIEW[] = $row["TABLE_NAME"];}

			$A = [];
			$B = [];

			foreach($list_tb as $val){

				$valS = $this->h2s($val);

				if(in_array($valS, $VIEW)){	$A[] = "`".$valS."`"; }
				else{ $B[] = "`".$valS."`"; }
			}

			$this->use_db($_DBS);

			if(count($A) > 0){ $this->request("DROP VIEW ".implode(", ", $A).";", __LINE__); }

			if(count($B) > 0){ $this->request("DROP TABLE ".implode(", ", $B).";", __LINE__); }
		}
	}


	public function update_tb($_DB, $_TB, $cl_df)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$this->use_db($_DBS);

		$this->request("ALTER TABLE `".$_TBS."` ".$cl_df.";", __LINE__);
	}


	public function insert_rc($_DB, $_TB, $field, $file, $function, $exceptions)
	{
		$type = [];
		$this->check_field($_DB, $_TB, $type);

		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$t = [];
		foreach($field as $k=>$v){

			$t[$this->h2s($k)] = addslashes("$v");
		}
		$field = $t;

		$f = [];
		foreach($function as $k=>$v){

			$f[$this->h2s($k)] = $v;
		}
		$function = $f;

		$sfK = [];
		$sfV = [];

		foreach($field as $k=>$v)
		{
			$v = html_entity_decode($v, ENT_QUOTES);

			$PRE = "";

			$this->check_type($k, $v, $type, $PRE);

			if(in_array($type[$k]["DATA_TYPE"], $exceptions["geo"]) && ($v !== ""))
			{
				$sfK[] = "`".$k."`";

				$geo_function = "ST_GeomFromText";

				$sfV[] = $geo_function."('".$v."')";
			}
			elseif(in_array($type[$k]["DATA_TYPE"], $exceptions["bin"]))
			{
				if(($type[$k]["DATA_TYPE"] !== "varbinary") && ($type[$k]["DATA_TYPE"] !== "binary"))
				{
					if(isset($file[$this->s2h($k)]) && ($file[$this->s2h($k)] !== "")){

						$sfK[] = "`".$k."`";

						$sfV[] = "x'".$this->s2h(base64_decode($file[$this->s2h($k)]))."'";
					}
				}
				elseif($v !== "")
				{
					$sfK[] = "`".$k."`";

					if(isset($function[$k]) && ($function[$k] !== "")){

						$sfV[] = "(".stripslashes($v).")";
					}
					elseif($file[$this->s2h($k)] === $v){

						$sfV[] = "x'".$v."'";
					}
					else{

						$sfV[] = "'".$v."'";
					}
				}
			}
			elseif($v !== "")
			{
				$sfK[] = "`".$k."`";

				if(isset($function[$k]) && ($function[$k] !== "")){

					$sfV[] = "(".stripslashes($v).")";
				}
				else{

					$sfV[] = $PRE."'".$v."'";
				}
			}
			else
			{
				$sfK[] = "`".$k."`";
				$sfV[] = "NULL";
			}
		}

		$this->use_db($_DBS);

		$this->request("INSERT INTO `".$_TBS."` (".implode(", ", $sfK).") VALUES (".implode(", ", $sfV).");", __LINE__);

		if(($_DBS === "mysql") && (($_TBS === "user") || ($_TBS === "db") || ($_TBS === "tables_priv") || ($_TBS === "columns_priv")))
		{
			$this->request("FLUSH PRIVILEGES;", __LINE__);
		}
	}


	public function update_rc($_DB, $_TB, $key, $field, $file, $function, $exceptions)
	{
		$type = [];
		$this->check_field($_DB, $_TB, $type);

		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$t = [];
		foreach($key as $k=>$v){

			$t[$this->h2s($k)] = $this->h2s($v);
		}
		$key = $t;

		$t = [];
		foreach($field as $k=>$v){

			$t[$this->h2s($k)] = addslashes("$v");
		}
		$field = $t;

		$f = [];
		foreach($function as $k=>$v){

			$f[$this->h2s($k)] = $v;
		}
		$function = $f;

		$sfV = [];
		foreach($field as $k=>$v)
		{
			$v = html_entity_decode($v, ENT_QUOTES);

			$PRE = "";

			$this->check_type($k, $v, $type, $PRE);

			if(in_array($type[$k]["DATA_TYPE"], $exceptions["geo"]) && ($v !== ""))
			{
				if($v != ""){

					$geo_function = "ST_GeomFromText";

					$sfV[] = "`".$k."`=".$geo_function."('".$v."')";
				}
				else{

					$sfV[] = "`".$k."`=NULL";
				}
			}
			elseif(in_array($type[$k]["DATA_TYPE"], $exceptions["bin"]))
			{
				if(($type[$k]["DATA_TYPE"] !== "varbinary") && ($type[$k]["DATA_TYPE"] !== "binary"))
				{
					if(isset($file[$this->s2h($k)]) && ($file[$this->s2h($k)] !== "")){

						$sfV[] = "`".$k."`=x'".$this->s2h(base64_decode($file[$this->s2h($k)]))."'";
					}
					else{

						$sfV[] = "`".$k."`=NULL";
					}
				}
				elseif($v !== "")
				{
					if(isset($function[$k]) && ($function[$k] !== "")){

						$sfV[] = "`".$k."`=(".stripslashes($v).")";
					}
					elseif(($file[$this->s2h($k)] === $v) && ($v !== "")){

						$sfV[] = "`".$k."`=x'".$v."'";
					}
					else{

						$sfV[] = "`".$k."`=x'".$this->s2h($v)."'";
					}
				}
			}
			elseif($v !== "")
			{
				if(isset($function[$k]) && ($function[$k] !== "")){

					$sfV[] = "`".$k."`=(".stripslashes($v).")";
				}
				else{

					$sfV[] = "`".$k."`=".$PRE."'".$v."'";
				}
			}
			else
			{
				$sfV[] = "`".$k."`=NULL";
			}

		}

		$sfK = [];
		foreach($key as $k=>$v)
		{
			$PRE = "";

			$this->check_type($k, $v, $type, $PRE);

			$sfK[] = "`".$k."`=".$PRE."'".addslashes($v)."' ";
		}

		$this->use_db($_DBS);

		if(count($sfV) !== 0){

			$this->request("UPDATE `".$_TBS."` SET ".implode(", ", $sfV)." WHERE ".implode(" AND ", $sfK)." LIMIT 1;", __LINE__);
		}

		if(($_DBS === "mysql") && (($_TBS === "user") || ($_TBS === "db") || ($_TBS === "tables_priv") || ($_TBS === "columns_priv")))
		{
			$this->request("FLUSH PRIVILEGES;", __LINE__);
		}
	}


	public function delete_rc($_DB, $_TB, $key, $DS)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $DS))
		{
			$type = [];
			$this->check_field($_DB, $_TB, $type);

			$_DBS = $this->h2s($_DB);
			$_TBS = $this->h2s($_TB);

			$t = [];
			foreach($key as $k=>$v){

				$t[$this->h2s($k)] = $this->h2s($v);
			}
			$key = $t;

			$sfK = [];
			foreach($key as $k=>$v)
			{
				$PRE = "";

				$this->check_type($k, $v, $type, $PRE);

				$sfK[] = "`".$k."`=".$PRE."'".addslashes($v)."'";
			}

			$this->use_db($_DBS);

			$this->request("DELETE FROM `$_TBS` WHERE ".implode(" AND ", $sfK).";", __LINE__);
		}
	}


	public function sqls_eval_list($text_script, $use)
	{
		$use = $this->h2s($use);

		if($text_script === ""){return;}

		$text_script = trim($text_script);
		if(!preg_match("/;$/", $text_script)){ $text_script .= ";";}

		$list_script = [];
		$text_script_temp = str_replace("\'", "\-", $text_script);
		$text_script_temp = str_replace('\"', '\-', $text_script_temp);

		$query = "";
		$open_value = false;
		$quote = "'";
		$pos_erq = 0;

		$strlen_text_script = strlen($text_script);

		for($i=0;$i<$strlen_text_script;$i++)
		{
			if(($text_script_temp[$i] == "#") && ($open_value == false)){

				while ( ($text_script_temp[$i] != "\n") && ($i < ($strlen_text_script-1)) ){ $i += 1; }
			}
			elseif(($text_script_temp[$i] == "-") && ($open_value == false)){

				if(isset($text_script_temp[$i+1]) && ($text_script_temp[$i+1] == "-")){

					while ( ($text_script_temp[$i] != "\n") && ($i < ($strlen_text_script-1)) ){ $i += 1; }
				}
			}
			elseif(($text_script_temp[$i] == "/") && ($open_value == false)){

				if((isset($text_script_temp[$i+1]) && ($text_script_temp[$i+1] === "*"))
					&&(isset($text_script_temp[$i+2]) && ($text_script_temp[$i+2] !== "!"))){

					while ( ($text_script_temp[$i].$text_script_temp[$i+1] != "*/") && ($i < ($strlen_text_script-1)) ){

						$i += 1;
					}

					if(isset($text_script_temp[$i+1])){ $i += 1; }
					if(isset($text_script_temp[$i+1])){ $i += 1; }
				}
			}

			elseif($text_script_temp[$i] == "`" && ($open_value == false)){

				$open_value = true;
				$quote = "`";
			}
			elseif($text_script_temp[$i] == "'" && ($open_value == false)){

				$open_value = true;
				$quote = "'";
			}
			elseif($text_script_temp[$i] == "\"" && ($open_value == false)){

				$open_value = true;
				$quote = "\"";
			}
			elseif($text_script_temp[$i] == $quote && ($open_value == true)){

				$open_value = false;
			}

			$query .= $text_script[$i];

			if($text_script_temp[$i] == ";" && ($open_value == false)){

				$list_script[] = $query;
				$query = "";
				$pos_erq = $i;
			}
		}

		if($open_value === true)
		{
			$string = substr($text_script, $pos_erq);
			$this->_LOG["MESSAGE"][] = "Error in your SQL syntax near<br>".$this->html($string);
		}

		$this->use_db($use);

		foreach($list_script as $script)
		{
			if((trim($script) != "") && (trim($script) != ";")){

				$this->sqls_eval($script);
			}
		}

		$this->dbc->query( "SET sql_mode = '".$this->sql_mode."';" );

		$this->dbc->query( "SET names = '".$this->character_name."';" );
	}


	private function check_field($_DB, $_TB, &$type)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$result = $this->request("select COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE
			from information_schema.columns
			where TABLE_SCHEMA=x'".$_DB."'
			AND table_name = x'".$_TB."';", __LINE__);

		while($row = $this->fetch_assoc($result[1])){

			$type[$row["COLUMN_NAME"]]["DATA_TYPE"] = $row["DATA_TYPE"];
			$type[$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] = $row["COLUMN_DEFAULT"];
			$type[$row["COLUMN_NAME"]]["IS_NULLABLE"] = $row["IS_NULLABLE"];
		}
	}


	private function check_type($k, &$v, $type, &$PRE)
	{
		if(isset($type[$k]["DATA_TYPE"]))
		{
			if($type[$k]["DATA_TYPE"] == "bit"){

				$PRE = "b";
			}

			if($type[$k]["DATA_TYPE"] == "year"){

				$v = base_convert("$v", 10, 2);
				$PRE = "b";
			}
		}
	}

	private function get_sub($_DB, $_DBS, $tb, $target, $create, $searching, $add)
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

	private function get_wr($nv, $field, $exceptions)
	{
		$WHERE = "";

		$fl_value_rc = addslashes($nv["fl_value_rc"]);

		$FLL = "";
		if(($nv["fl_operator_rc"] === "LIKE") || ($nv["fl_operator_rc"] === "NOT LIKE"))
		{
			$FLL = "LIKE";
		}

		if(($nv["fl_operator_rc"] === "IS NULL") || ($nv["fl_operator_rc"] === "IS NOT NULL"))
		{
			$WHERE = "WHERE `".$this->h2s($nv["fl_field_rc"])."` ".$nv["fl_operator_rc"]." ";
		}
		elseif($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "bit")
		{
			$WHERE = ($FLL === "LIKE") ?
				"WHERE LPAD(BIN(`".$this->h2s($nv["fl_field_rc"])."`) , ".
				$field[$this->h2s($nv["fl_field_rc"])]["NUMERIC_PRECISION"].", '0') ".$nv["fl_operator_rc"].
				" '%".$nv["fl_value_rc"]."%'" :
				"WHERE LPAD(BIN(`".$this->h2s($nv["fl_field_rc"])."`), ".
				$field[$this->h2s($nv["fl_field_rc"])]["NUMERIC_PRECISION"].", '0') ".
				$nv["fl_operator_rc"]." '".$nv["fl_value_rc"]."'";
		}
		elseif($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "float")
		{
			$WHERE = ($FLL === "LIKE") ?
				"WHERE abs(`".$this->h2s($nv["fl_field_rc"])."`) ".$nv["fl_operator_rc"].
				" '%".$fl_value_rc."%'" :
				"WHERE `".$this->h2s($nv["fl_field_rc"])."` ".
				$nv["fl_operator_rc"]." '".$fl_value_rc."'";
		}
		elseif(in_array($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"], $exceptions["geo"]))
		{
			$FNS = "ST_AsText";

			$WHERE = ($FLL === "LIKE") ?
				"WHERE ".$FNS."(".$this->h2s($nv["fl_field_rc"]).") ".$nv["fl_operator_rc"].
				" '%".$fl_value_rc."%'" :
				"WHERE ".$FNS."(".$this->h2s($nv["fl_field_rc"]).") ".
				$nv["fl_operator_rc"]." '".$fl_value_rc."'";
		}
		elseif(
			($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "char") ||
			($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "varchar") ||
			($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "tinytext") ||
			($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "text") ||
			($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "mediumtext") ||
			($field[$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "longtext")
		)
		{
			$WHERE = ($FLL === "LIKE") ?
				"WHERE `".$this->h2s($nv["fl_field_rc"])."` ".$nv["fl_operator_rc"].
				" '%".$fl_value_rc."%'" :
				"WHERE CAST(`".$this->h2s($nv["fl_field_rc"])."` AS CHAR) ".
				$nv["fl_operator_rc"]." '".$fl_value_rc."'";
		}
		else
		{
			$WHERE = ($FLL === "LIKE") ?
				"WHERE `".$this->h2s($nv["fl_field_rc"])."` ".$nv["fl_operator_rc"].
				" '%".$fl_value_rc."%'" :
				"WHERE `".$this->h2s($nv["fl_field_rc"])."` ".
				$nv["fl_operator_rc"]." '".$fl_value_rc."'";
		}

		return $WHERE;
	}


}
