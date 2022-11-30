<?php

/*
Copyright (c) 2018-2022 Andrey Lyskov
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

		$this->DS = [];

		$this->ext = [
			"geo" => ["geometry", "point", "linestring", "polygon",
				"multipoint", "multilinestring", "multipolygon","geomcollection","geometrycollection"],
			"blob" => ["tinyblob", "blob", "mediumblob", "longblob"],
			"binary" => ["varbinary", "binary"],
			"text" => ["tinytext", "text", "mediumtext", "longtext"],
			"char" => ["varchar", "char"],
		];
	}

	public function mk($script_id, $_sql)
	{
		$SQL_SL = "";

		if($script_id !== ""){

			$SQL_SL = $_sql[$script_id];
		}

		return $SQL_SL;
	}

	public function db($nv, $LIMIT)
	{
		$RT = [];
		$RT["DB"] = [];
		$RT["COUNT"] = "";
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["SCHEMA"];

		$RT["FIELD_ST"] = ["SCHEMA_NAME","DEFAULT_COLLATION_NAME"];

		$RT["FIELD_ST_NAV"] = ["SCHEMA_NAME", "DEFAULT_COLLATION_NAME"];

		$RT["FIELD_SE"] = ["DEFAULT_COLLATION_NAME", "DEFAULT_CHARACTER_SET_NAME"];

		$RT["FILTER_EX"] = [];

		if($nv["page_db"] === "0"){$nv["page_db"] = $RT["ON_PAGE"][0];}

		foreach($RT["FIELD_ST_NAV"] as $v){

			$RT["FILTER_EX"][$v] = $this->get_fv($v, [], "db");
		}

		$WHERE = $this->get_wr($nv, [], "db");

		if($WHERE !== ""){$WHERE = " WHERE ".$WHERE;}

		$result = $this->request("SELECT COUNT(*) FROM information_schema.SCHEMATA ".$WHERE.";",
			"", [], __LINE__);

		if($result[0]){

			$RT["COUNT"] = $this->fetch_row($result[1])[0];

			if($RT["COUNT"] <= $nv["from_db"]){$nv["from_db"] = "0";}
		}

		$result = $this->request("SELECT
			SCHEMA_NAME, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
			FROM information_schema.SCHEMATA ".$WHERE." ORDER BY ".
			($RT["FIELD_ST"][$nv["order_db"]])." ".$nv["order_desc_db"].
			" LIMIT ".$nv["from_db"].", ".$nv["page_db"].";", "", [], __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) )
			{
				$RT["DB"][$row["SCHEMA_NAME"]] = $row;
				$RT["DB"][$row["SCHEMA_NAME"]]["COUNT"] = "";

				$lines = $this->request("SELECT COUNT(*)
					FROM information_schema.TABLES WHERE `TABLE_SCHEMA`=x'".
					$this->s2h($row["SCHEMA_NAME"])."';", "", [], __LINE__);

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

		$RT[] = "SQL_MODE: ".$this->sql_mode;

		$RT[] = "&nbsp;";

		$result = $this->request("SHOW STATUS like 'Ssl_cipher';", "", [], __LINE__);
		if($result[0]){

			$ssl_cipher = $this->fetch_row($result[1])[1];

			if($ssl_cipher !== ""){

				$RT[] = "SSL_CIPHER: ".$ssl_cipher;
			}
			else{

				$RT[] = "SSL_CIPHER: "._NOTE_IN_USE;
			}
		}

		$result = $this->request("SELECT CURRENT_USER();", "", [], __LINE__);
		if($result[0]){

			$user = $this->fetch_row($result[1])[0];

			$_user = explode("@", $user);

			$result = $this->request("SELECT ssl_type FROM mysql.user
				WHERE Host='".$_user[1]."' AND User='".$_user[0]."';", "", [], __LINE__, false);

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

			$result = $this->request("SHOW GRANTS FOR `".$_user[0]."`@`".$_user[1]."`;", "", [], __LINE__);

			if($result[0]){

				$RT[] = "".$this->fetch_row($result[1])[0];
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

		$RT["FIELD_ST"] = [
			"table_name","create_time","update_time","engine","table_collation","table_type","row_format"];

		$RT["FIELD_ST_NAV"] = [
			"table_name","create_time","update_time","engine","table_collation","table_type"];

		$RT["FIELD_SE"] = ["CREATE_TIME", "UPDATE_TIME", "ENGINE", "TABLE_COLLATION",
			"TABLE_TYPE", "ROW_FORMAT", "DATA_LENGTH", "TABLE_COMMENT", "AUTO_INCREMENT"];

		$RT["FILTER_EX"] = [];

		$RT["PRIVILEGES"] = [];

		$OPEN_TABLES = [];

		$SUB_PR = [];
		$SUB_PR_SET = [];
		$SUB_PR_OUT = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", "", [], __LINE__);

		if($CREATE[0])
		{
			$RT["CREATE"]["DB"] = $this->fetch_row($CREATE[1])[1];

			$this->request("USE `".$_DBS."`;", "", [], __LINE__);

			$result = $this->request("SELECT TABLE_NAME
				FROM information_schema.VIEWS WHERE TABLE_SCHEMA=x'".$_DB."';", "", [], __LINE__, false);

			if($result[0])
			{
				while( $row = $this->fetch_assoc($result[1]) ){

					$CREATE = $this->request("SHOW CREATE TABLE `$_DBS`.`".$row["TABLE_NAME"]."`;", "", [], __LINE__);

					if($CREATE[0]){

						$RT["VIEWS"][$row["TABLE_NAME"]] = $this->fetch_row($CREATE[1])[1];
					}
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
						AND PARAMETER_MODE<>'';", "", [], __LINE__);

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
						AND PARAMETER_MODE<>'';", "", [], __LINE__);

					if($result[0]){

						while( $row = $this->fetch_assoc($result[1]) ){

							$SUB_PR[] = $row["PARAMETER_NAME"];
						}
					}

					$RT["SUB"]["PR"] = "SELECT ".$this->h2s($cl_sl["function"])."(".implode(", ",$SUB_PR).");";
				}
				$RT["SUB"]["ID"] = "function";
			}

			$result = $this->request("SHOW OPEN TABLES FROM `$_DBS` WHERE In_use>0;", "", [], __LINE__);

			while( $row = $this->fetch_assoc($result[1]) ){ $OPEN_TABLES[] = $row["Table"];}

			if($nv["page_tb"] === "0"){$nv["page_tb"] = $RT["ON_PAGE"][0];}

			foreach($RT["FIELD_ST_NAV"] as $v){

				$RT["FILTER_EX"][$v] = $this->get_fv($v, [], "tb");
			}

			$WHERE = $this->get_wr($nv, [], "tb");

			if($WHERE !== ""){$WHERE = " AND (".$WHERE.") ";}

			$result = $this->request("SELECT COUNT(*)
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_DB."' ".$WHERE." ;",
				"", [], __LINE__);

			if($result[0]){

				$RT["COUNT"] = $this->fetch_row($result[1])[0];

				if($RT["COUNT"] <= $nv["from_tb"]){$nv["from_tb"] = "0";}
			}

			$result = $this->request("SELECT
				TABLE_NAME, CREATE_TIME, UPDATE_TIME, ENGINE, TABLE_COLLATION,
				TABLE_TYPE, ROW_FORMAT, DATA_LENGTH, TABLE_COMMENT, AUTO_INCREMENT
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_DB."' ".$WHERE." ORDER BY ".
				($RT["FIELD_ST"][$nv["order_tb"]])." ".$nv["order_desc_tb"].
				" LIMIT ".$nv["from_tb"].", ".$nv["page_tb"].";",
				"", [], __LINE__);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) )
				{
					$RT["TABLES"][$row["TABLE_NAME"]] = $row;
					$RT["TABLES"][$row["TABLE_NAME"]]["COUNT"] = "";

					if(in_array($row["TABLE_NAME"], $OPEN_TABLES)){

						$RT["TABLES"][$row["TABLE_NAME"]]["COUNT"] = "IN USE";
					}
					else{

						$lines = $this->request("SELECT COUNT(*) FROM `$_DBS`.`".$row["TABLE_NAME"]."`;"
						, "", [], __LINE__);

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


	public function rc($_DB, $_TB, $nv, $LIMIT, $mode)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->set_name($this->h2s($_TB));

		$RT = [];
		$RT["DB"] = $_DBS;
		$RT["TB"] = $_TBS;
		$RT["CREATE"] = [];
		$RT["PRI"] = false;
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
		$RT["FILTER_EX"] = [];
		$RT["PRIVILEGES"] = [];
		$RT["DB_LIST"] = [];

		if($mode === "")
		{
			$RT["ALTER_TABLE"] = [
			"ADD"=>[
				"ADD PRIMARY KEY (`...`);",
				"ADD CONSTRAINT `...` \nFOREIGN KEY (`...`) \nREFERENCES `...` (`...`);",
				"ADD UNIQUE (`...`);",
				"ADD INDEX (`...`);",
			],
			"CHANGE"=>[],
			"DROP"=>[]
			];

			if($this->server_info[0] > 5){

				array_push($RT["ALTER_TABLE"]["ADD"], "ADD CHECK (...);");
			}

			array_push($RT["ALTER_TABLE"]["ADD"], "ADD\n... \nFIRST;");
		}

		$C_L = [];
		$C_F = [];

		$LIST = [];

		$LIST_KEY = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", "", [], __LINE__);

		$RT["CREATE"]["DB"] = "";
		if($CREATE[0]){

			$RT["CREATE"]["DB"] = $this->fetch_row($CREATE[1])[1];
		}

		$CREATE = $this->request("SHOW CREATE TABLE `$_DBS`.`$_TBS`;", "", [], __LINE__);

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
				TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$_TB."';", "", [], __LINE__);

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

				$result = $this->request("SELECT INDEX_NAME
					FROM information_schema.STATISTICS
					WHERE TABLE_SCHEMA = x'".$_DB."' AND TABLE_NAME = x'".$_TB."';", "", [], __LINE__);

				while( $row = $this->fetch_row($result[1]) )
				{
					if(trim($row[0]) !== "PRIMARY"){

						array_push($RT["ALTER_TABLE"]["DROP"], "DROP INDEX `".$row[0]."`;");
					}
				}

				$result = $this->request("SELECT
					tc.CONSTRAINT_NAME, tc.CONSTRAINT_TYPE,
					kc.COLUMN_NAME, kc.REFERENCED_TABLE_SCHEMA, kc.REFERENCED_TABLE_NAME, kc.REFERENCED_COLUMN_NAME
					FROM information_schema.TABLE_CONSTRAINTS tc
					LEFT JOIN information_schema.KEY_COLUMN_USAGE kc
					ON tc.TABLE_SCHEMA = kc.TABLE_SCHEMA
					AND tc.TABLE_NAME = kc.TABLE_NAME
					AND tc.CONSTRAINT_NAME = kc.CONSTRAINT_NAME
					WHERE tc.TABLE_SCHEMA = x'".$_DB."' AND tc.TABLE_NAME = x'".$_TB."';", "", [], __LINE__);

				while( $row = $this->fetch_assoc($result[1]) )
				{
					if($row["CONSTRAINT_TYPE"] !== NULL){

						$C_L[$row["COLUMN_NAME"]][] = $row["CONSTRAINT_TYPE"];
					}

					if(trim($row["REFERENCED_COLUMN_NAME"]) !== ""){

						$C_F[$row["COLUMN_NAME"]][] = $row;
					}

					if(trim($row["CONSTRAINT_TYPE"]) === "FOREIGN KEY"){

						array_push($RT["ALTER_TABLE"]["DROP"], "DROP FOREIGN KEY `".$row["CONSTRAINT_NAME"]."`;");
					}
					elseif((trim($row["CONSTRAINT_TYPE"]) === "PRIMARY KEY") &&
						(!in_array("DROP PRIMARY KEY;", $RT["ALTER_TABLE"]["DROP"]))){

						array_push($RT["ALTER_TABLE"]["DROP"], "DROP PRIMARY KEY;");
					}
					elseif(trim($row["CONSTRAINT_TYPE"]) === "CHECK"){

						array_push($RT["ALTER_TABLE"]["DROP"], "DROP CHECK `".$row["CONSTRAINT_NAME"]."`;");
					}
				}
			}

			$result = $this->request("select COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_KEY,
				COLUMN_DEFAULT, IS_NULLABLE, EXTRA, NUMERIC_PRECISION
				from information_schema.columns where TABLE_SCHEMA=x'".$_DB."'
				AND table_name = x'".$_TB."' ORDER BY ORDINAL_POSITION;", "", [], __LINE__);

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

						if(!in_array( $row["DATA_TYPE"], $this->ext["blob"]) &&
							!in_array( $row["DATA_TYPE"], $this->ext["binary"])){

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

					if(isset($C_L[$row["COLUMN_NAME"]])){

						$RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"] = $C_L[$row["COLUMN_NAME"]];
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

						foreach($C_F[$row["COLUMN_NAME"]] as $vf)
						{
							$constraint_value = $this->request("SELECT `".
								$vf["REFERENCED_COLUMN_NAME"]."` FROM `".
								$vf["REFERENCED_TABLE_SCHEMA"]."`.`".
								$vf["REFERENCED_TABLE_NAME"]."`;", "", [], __LINE__);

							if($constraint_value[0]){

								while($row_constraint_value = $this->fetch_row($constraint_value[1])){

									$RT["FIELDS"][$row['COLUMN_NAME']]["COLUMN_VALUE"][] = $row_constraint_value[0];
								}
							}

							$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = true;
						}
					}

					if(($row["COLUMN_KEY"] === "PRI") || ($row["COLUMN_KEY"] === "UNI")){

						$RT["PRI"] = true;

						$LIST_KEY[] = $row['COLUMN_NAME'];
					}

					if($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "bit"){

						$LIST[] = "LPAD(BIN(`".$row["COLUMN_NAME"]."`), ".
							$RT["FIELDS"][$row["COLUMN_NAME"]]["NUMERIC_PRECISION"].", '0') AS `".
							$row["COLUMN_NAME"]."`";
					}
					elseif($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "float"){

						$LIST[] = "abs(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
					}
					elseif(in_array( $row["COLUMN_TYPE"], $this->ext["geo"])){

						$LIST[] = "ST_AsText(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
					}
					else{

						$LIST[] = "`".$row["COLUMN_NAME"]."`";
					}
				}

				if(($RT["TABLE_TYPE"] !== "VIEW") && ($RT["TABLE_TYPE"] !== "SYSTEM VIEW") && ($mode === "")){

					if(!$RT["PRI"]){ $this->_LOG["RESULT"][] = _MESSAGE_UNIQUE_COLUMN; }
				}
			}

			foreach($RT["FIELD_ST_NAV"] as $v){

				$RT["FILTER_EX"][$v] = $this->get_fv($v, $RT["FIELDS"], "rc");
			}

			$WHERE = $this->get_wr($nv, $RT["FIELDS"], "rc");

			if($WHERE !== ""){$WHERE = " WHERE ".$WHERE;}

			if($nv["page_rc"] === "0"){$nv["page_rc"] = $RT["ON_PAGE"][0];}
			elseif(($nv["page_rc"] === "1") && !in_array("1", $RT["ON_PAGE"])){array_unshift($RT["ON_PAGE"], "1");}

			$result = $this->request("SELECT COUNT(*) FROM `$_DBS`.`$_TBS` ".$WHERE." ;",
				"", [], __LINE__);

			if($result[0]){

				$RT["COUNT"] = $this->fetch_row($result[1])[0];

				if($RT["COUNT"] <= $nv["from_rc"]){$nv["from_rc"] = "0";}
			}

			$LIMIT = " LIMIT ".$nv["from_rc"].", ".$nv["page_rc"];

			if($mode !== ""){$LIMIT = "";}

			$ORDER_LIST	= array_keys($LIST);
			unset($ORDER_LIST[0]);

			if(count($ORDER_LIST) === 0){$order_list_st = "1";}
			else{$order_list_st =
				($nv["order_rc"]+1)." ".$nv["order_desc_rc"].",".
				implode(" ".$nv["order_desc_rc"].", ", $ORDER_LIST);}

			$result = $this->request("SELECT ".implode(", ",  $LIST).
				" FROM `".$_DBS."`.`".$_TBS."` ".$WHERE." ORDER BY ".$order_list_st.$LIMIT.";",
				"", [], __LINE__);

			if($result[0])
			{
				while($res = $this->fetch_assoc($result[1])){

					$RT["RECORDS"][] = $res;
				}
			}
			elseif(($result[1] === 1038) && (count($LIST_KEY)) !== 0)
			{
				$result = $this->request("SELECT ".implode(", ",  $LIST_KEY).
					" FROM `".$_DBS."`.`".$_TBS."` ".$WHERE." ORDER BY ".$order_list_st[0].$LIMIT.";",
					"", [], __LINE__);

				while($res = $this->fetch_assoc($result[1]))
				{
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

		$result = $this->request("SELECT CURRENT_USER();", "", [], __LINE__);
		if($result[0]){

			$user = $this->fetch_row($result[1])[0];

			$_user = explode("@", $user);
		}

		$result = $this->request("SELECT PRIVILEGE_TYPE, COLUMN_NAME
			FROM `information_schema`.`COLUMN_PRIVILEGES`
			WHERE `GRANTEE` = '\'".$_user[0]."\'@\'".$_user[1]."\''
			AND TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$_TB."';", "", [], __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT["PRIVILEGES"]["COLUMN_PRIVILEGES"][$row["PRIVILEGE_TYPE"]][] = $row["COLUMN_NAME"];
			}
		}

		if(!isset($RT["PRIVILEGES"]["COLUMN_PRIVILEGES"]))
		{
			$result = $this->request("SELECT PRIVILEGE_TYPE
				FROM `information_schema`.`TABLE_PRIVILEGES`
				WHERE `GRANTEE` = '\'".$_user[0]."\'@\'".$_user[1]."\''
				AND TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$_TB."';", "", [], __LINE__);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) ){

					$RT["PRIVILEGES"]["TABLE_PRIVILEGES"][] = $row["PRIVILEGE_TYPE"];
				}
			}
		}

		$db_list = $this->request("SHOW DATABASES;", "", [], __LINE__);
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

		$result = $this->request("SHOW TABLES FROM `$_DBS`;", "", [], __LINE__);

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
				$field = [];

				$result = $this->request("select COLUMN_NAME, DATA_TYPE, NUMERIC_PRECISION
					from information_schema.columns where TABLE_SCHEMA=x'".$_DB."'
					AND table_name = x'".$val."' ORDER BY ORDINAL_POSITION;", "", [], __LINE__);

				if($result[0]){

					while($row = $this->fetch_assoc($result[1])){

						if($row["DATA_TYPE"] === "float"){

							$field[] = "abs(`".$row["COLUMN_NAME"]."`)";
						}
						elseif($row["DATA_TYPE"] === "bit"){

							$field[] = "LPAD(BIN(`".$row["COLUMN_NAME"]."`), ".$row["NUMERIC_PRECISION"].", '0')";
						}
						elseif(in_array($row["DATA_TYPE"], $this->ext["geo"])){

							$field[] = "ST_AsText(`".$row["COLUMN_NAME"]."`)";
						}
						else{

							$field[] = "`".$row["COLUMN_NAME"]."`";
						}
					}
				}

				$tc = implode(",", $field);

				$valS = $this->h2s($val);

				$result = $this->request("SELECT ".$tc." FROM `$_DBS`.`$valS`;", "", [], __LINE__);

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

		if($count == 0){ $this->_LOG["RESULT"][] = _MESSAGE_FIND_NOT_FOUND; }
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
					TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$val."';", "", [], __LINE__);

				$TABLE_TYPE = "";
				$ENGINE = "";

				if($result[0]){

					while($row = $this->fetch_assoc($result[1])){

						$TABLE_TYPE = $row["TABLE_TYPE"];
						$ENGINE = $row["ENGINE"];
					}
				}

				$valS = $this->h2s($val);

				if($pre === false){

					$tbs_new = $valS;
				}
				elseif($pre === true){

					if(($name_new !== $valS) || ($_DB !== $copy_2bd)){

						$tbs_new = $name_new;
					}
					else{

						$tbs_new = $valS."_copy";
					}
				}

				if(($TABLE_TYPE !== "VIEW"))
				{
					if((($ENGINE === "MRG_MyISAM") || ($ENGINE === "MRG_MYISAM")) && ($pre === false))
					{
						$CREATE = $this->request("SHOW CREATE TABLE `".$_DBS."`.`".$valS."`;", "", [], __LINE__);

						if($CREATE[0]){

							$this->request("USE `".$copy_2bdS."`;", "", [], __LINE__, false);

							$result = $this->request($this->fetch_row($CREATE[1])[1], "", [], __LINE__);
						}
					}
					else
					{
						if($val !== "")
						{
							$result = $this->request(
								"CREATE TABLE `".$copy_2bdS."`.`".$tbs_new."` LIKE `".$_DBS."`.`".$valS."`;",
								"", [], __LINE__);

							if($result[0])
							{
								$result = $this->request("SET FOREIGN_KEY_CHECKS=0;", "", [], __LINE__);

								$ex = $this->request("select COLUMN_NAME FROM information_schema.COLUMNS where
									TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$val."'
									AND EXTRA<>'STORED GENERATED' AND EXTRA<>'VIRTUAL GENERATED'
									ORDER BY ORDINAL_POSITION;", "", [], __LINE__);

								$_EX = [];

								if($ex[0]){

									while($row_ex = $this->fetch_row($ex[1])){

										$_EX[] = $row_ex[0];
									}
								}

								$this->request(
									"INSERT INTO `".$copy_2bdS."`.`".$tbs_new."` (`".implode("`,`", $_EX)."`)
									SELECT `".implode("`,`", $_EX)."` FROM `".$_DBS."`.`".$valS."`;",
									"", [], __LINE__);

								$constraint = $this->request("SELECT
									COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME,
									REFERENCED_COLUMN_NAME, CONSTRAINT_NAME
									FROM information_schema.KEY_COLUMN_USAGE
									WHERE TABLE_SCHEMA = x'".$_DB."' AND TABLE_NAME = x'".$val."' AND
									CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME is not null;",
									"", [], __LINE__);

								$count = 1;

								while($row_constraint = $this->fetch_assoc($constraint[1]))
								{
									$referent = $this->request("SELECT UPDATE_RULE, DELETE_RULE
										FROM information_schema.REFERENTIAL_CONSTRAINTS
										WHERE CONSTRAINT_SCHEMA = x'".$_DB."'
										AND TABLE_NAME = x'".$val."'
										AND CONSTRAINT_NAME='".$row_constraint["CONSTRAINT_NAME"]."';",
										"", [], __LINE__);

									$row_referent = $this->fetch_assoc($referent[1]);

									$action = "ON UPDATE ".$row_referent["UPDATE_RULE"]." ON DELETE ".$row_referent["DELETE_RULE"];

									if(($_DBS !== $copy_2bdS) && ($row_constraint["REFERENCED_TABLE_SCHEMA"] === $_DBS)){

										$row_constraint["REFERENCED_TABLE_SCHEMA"] = $copy_2bdS;
									}

									if($pre === false){

										$_DBST = $row_constraint["REFERENCED_TABLE_SCHEMA"];
									}
									else{

										if($copy_2bdS === $row_constraint["REFERENCED_TABLE_SCHEMA"]){

											$_DBST = $_DBS;
										}
										else{

											$_DBST = $row_constraint["REFERENCED_TABLE_SCHEMA"];
										}
									}

									$this->request("ALTER TABLE `".$copy_2bdS."`.`".$tbs_new."` ADD CONSTRAINT ".
									$count."fk".time()." FOREIGN KEY (`".
									$row_constraint["COLUMN_NAME"]."`) REFERENCES `".
									$_DBST."`.`".
									$row_constraint["REFERENCED_TABLE_NAME"]."` (`".
									$row_constraint["REFERENCED_COLUMN_NAME"]."`) ".$action.";",
									"", [], __LINE__);

									$count += 1;
								}

								$result = $this->request("SET FOREIGN_KEY_CHECKS=1;", "", [], __LINE__);
							}
						}
					}
				}
				else
				{
					$listview = $this->request("SHOW CREATE TABLE `".$_DBS."`.`".$valS."`;", "", [], __LINE__);

					if($listview[0]){

						$CT = $this->get_view_tr($this->fetch_row($listview[1])[1], $_DBS, $valS, $copy_2bdS, $tbs_new);

						$this->request($CT, "", [], __LINE__);
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

		$result = $this->request("SELECT * FROM information_schema.SCHEMATA where SCHEMA_NAME=x'".$_db."';",
			"", [], __LINE__);

		if($result[0])
		{
			$schema = $this->fetch_assoc($result[1]);

			$ENCRYPTION = "";
			if(isset($schema["DEFAULT_ENCRYPTION"])){

				$ENCRYPTION = " ENCRYPTION='".$schema["DEFAULT_ENCRYPTION"][0]."' ";
			}

			$result = $this->request(
				"CREATE DATABASE `".$dbs_new."` CHARACTER SET ".$schema["DEFAULT_CHARACTER_SET_NAME"].
				" COLLATE ".$schema["DEFAULT_COLLATION_NAME"].$ENCRYPTION.";", "", [], __LINE__);

			if($result[0])
			{
				$this->request("USE `".$dbs_new."`;", "", [], __LINE__);

				$this->copy_tb($_db, $this->get_list_tb($_db), $db_new, "", false);

				$triggers = $this->get_sub($_db, $valueS,
					"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER", "SQL Original Statement", "");

				foreach($triggers as $vt){$result = $this->request($vt, "", [], __LINE__);}
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


	public function export($_DB, $list_tb, $nv, $mode, $type)
	{
		$RT = [];

		foreach($list_tb as $val)
		{
			$RT[] = $this->rc( $_DB, $val, $nv, ["RECORDS"=>["1"]], $mode );
		}

		return $RT;
	}

	public function export_sql($list_db, $list_tb, $nv, $mode)
	{
		$filename = date("d-m-Y").".sql";

		$crt_view_temp = "";
		$crt_view = "";
		$str = PHP_EOL." -- Server info: ".$this->server_info.PHP_EOL.PHP_EOL;

		$str .= PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';";

		$str .= PHP_EOL."SET FOREIGN_KEY_CHECKS=0;".PHP_EOL;

		foreach($list_db as $value)
		{
			$_DBS = $this->h2s($value);

			if($mode === "DB"){

				$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", "", [], __LINE__);

				if($CREATE[0]){

					$str .= PHP_EOL.$this->fetch_row($CREATE[1])[1].";";

					$str .= PHP_EOL."USE `$_DBS`;";
				}

				$RT = $this->export($value, $this->get_list_tb($value), $nv, $mode, "sql");
			}
			else{

				$RT = $this->export($value, $list_tb, $nv, $mode, "sql");
			}

			foreach($RT as $k=>$v)
			{
				$row = [];

				if(($v["TABLE_TYPE"] !== "VIEW") && ($v["ENGINE"] !== "MRG_MyISAM") && ($v["ENGINE"] !== "MRG_MYISAM"))
				{
					$str .= PHP_EOL.PHP_EOL.$v["CREATE"]["TB"].";".PHP_EOL;

					foreach($v["RECORDS"] as $kr=>$vr)
					{
						$vrex = [];

						foreach($vr as $kf=>$vf)
						{
							if(($v["FIELDS"][$kf]["EXTRA"] === "VIRTUAL GENERATED") ||
									($v["FIELDS"][$kf]["EXTRA"] === "STORED GENERATED")){
							}
							elseif(in_array($v["FIELDS"][$kf]["COLUMN_TYPE"], $this->ext["geo"])){

								if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

									$vrex[$kf] = "NULL";
								}
								else{

									$vrex[$kf] = "ST_GeomFromText('".$vf."')";
								}
							}
							elseif(($v["FIELDS"][$kf]["DATA_TYPE"] === "varbinary") ||
								preg_match("/blob$/", $v["FIELDS"][$kf]["DATA_TYPE"])){

								if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

									$vrex[$kf] = "NULL";
								}
								else{

									$vrex[$kf] = "x'".$this->s2h($vf)."'";
								}
							}
							elseif($v["FIELDS"][$kf]["DATA_TYPE"] === "bit"){

								if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

									$vrex[$kf] = "NULL";
								}
								else{

									$vrex[$kf] = "b'".$vf."'";
								}
							}
							else{

								if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

									$vrex[$kf] = "NULL";
								}
								else{

									$vrex[$kf] = "'".addslashes($vf)."'";
								}
							}
						}

						$row[] = "(".implode(",", $vrex).")";
					}

					if(count($row) !== 0){

						$str .= PHP_EOL."insert into `".
							$v["TB"]."` (`".implode("`,`", array_keys($vrex))."`) values".
							PHP_EOL.implode(",".PHP_EOL, $row).";".PHP_EOL;
					}
				}
				else
				{
					if($v["TABLE_TYPE"] === "VIEW")
					{
						if($mode === "DB"){

							$crt_view_temp .= PHP_EOL."USE `".$_DBS."`;".PHP_EOL;
							$crt_view .= PHP_EOL."USE `".$_DBS."`;".PHP_EOL;
						}

						$result = $this->request("SELECT COLUMN_NAME FROM information_schema.COLUMNS
							WHERE TABLE_SCHEMA='".
							addslashes($_DBS)."' AND TABLE_NAME='".
							addslashes($v["TB"])."';", "", [], __LINE__);

						$RT = [];

						if($result[0])
						{
							while( $row = $this->fetch_assoc($result[1]) ){

								$RT[] = $row["COLUMN_NAME"];
							}

							$crt_view_temp .= "-- Temporary view structure for view `".$v["TB"]."`";

							$crt_view_temp .= PHP_EOL."CREATE VIEW `".$v["TB"]."` AS SELECT  "."1 AS `".
								implode("`, 1 AS `", $RT)."`;".PHP_EOL;

							$crt_view .= "/*!50001 DROP VIEW IF EXISTS `".$v["TB"]."`*/;";

							if($mode !== "DB"){

								$this->request("USE `mysql`;", "", [], __LINE__);
							}
							else{

								$this->request("USE `".$_DBS."`;", "", [], __LINE__);
							}

							$listview = $this->request("SHOW CREATE TABLE `".$_DBS."`.`".$v["TB"]."`;",
								"", [], __LINE__);

							if($listview[0]){

								$CT = $this->fetch_row($listview[1])[1];

								if($mode !== "DB"){

									$CT = $this->get_view_tr($CT, $_DBS, $v["TB"], "", $v["TB"]);
								}

								$crt_view .= PHP_EOL.$CT.";".PHP_EOL;
							}
						}
					}
					elseif(($v["ENGINE"] === "MRG_MyISAM") || ($v["ENGINE"] === "MRG_MYISAM"))
					{
						$str .= PHP_EOL.$v["CREATE"]["TB"].";".PHP_EOL;
					}
				}
			}

			if($mode === "DB")
			{
				$this->request("USE `".$_DBS."`;", "", [], __LINE__);

				$str .= PHP_EOL."USE `".$_DBS."`;".PHP_EOL;

				$trigger = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
					"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER",
					"SQL Original Statement", "")).";";

				if($trigger !== ";"){

					$str .= PHP_EOL."/* TRIGGER */".PHP_EOL;
					$str .= PHP_EOL.$trigger.PHP_EOL;
				}

				$procedure = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
					"ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE",
					"Create Procedure", "ROUTINE_TYPE='PROCEDURE' AND")).";";

				if($procedure !== ";"){

					$str .= PHP_EOL."/* PROCEDURES */".PHP_EOL;
					$str .= PHP_EOL.$procedure.PHP_EOL;
				}

				$function = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
					"ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION",
					"Create Function", "ROUTINE_TYPE='FUNCTION' AND")).";";

				if($function !== ";"){

					$str .= PHP_EOL."/* FUNCTIONS */".PHP_EOL;
					$str .= PHP_EOL.$function.PHP_EOL;
				}

				$events = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_DBS,
					"EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", "")).";";

				if($events !== ";"){

					$str .= PHP_EOL."/* EVENTS */".PHP_EOL;
					$str .= PHP_EOL.$events.PHP_EOL;
				}
			}
		}

		if($crt_view_temp !== ""){

			$str .= PHP_EOL."/* VIEWS */".PHP_EOL;
		}

		$str .= $crt_view_temp;
		$str .= $crt_view;
		$str .= PHP_EOL."SET FOREIGN_KEY_CHECKS=1;".PHP_EOL;

		$this->export_get($filename, $str);
	}


	public function clear_db($list_db)
	{
		foreach($list_db as $val){

			$valS = $this->h2s($val);

			$result = $this->request("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
				FROM information_schema.SCHEMATA where SCHEMA_NAME=x'".$val."';", "", [], __LINE__);

			if($result[0])
			{
				$schema = $this->fetch_row($result[1]);

				$CREATE = "CREATE DATABASE `".$valS."` CHARACTER SET ".$schema[0]." COLLATE ".$schema[1].";";

				if(($val !== "") && (!in_array($valS, $this->DS))){

					$this->request("DROP DATABASE `$valS`;", "", [], __LINE__);

					$this->request($CREATE, "", [], __LINE__);
				}
			}
		}
	}


	public function delete_db($list_db)
	{
		foreach($list_db as $val)
		{
			$valS = $this->h2s($val);

			if(($val !== "") && (!in_array($valS, $this->DS))){

				$this->request("DROP DATABASE `$valS`;", "", [], __LINE__);
			}
		}
	}


	public function create_sub($_DB, $cl_df)
	{
		$_DBS = $this->h2s($_DB);
		$cl_df = "CREATE ".$cl_df;

		$this->request("USE `".$_DBS."`;", "", [], __LINE__);

		$this->request($cl_df, "", [], __LINE__);
	}


	public function update_sub($_DB, $cl_tr, $cl_in, $cl_df, $cl_dl)
	{
		$_DBS = $this->h2s($_DB);

		$cl_df = "CREATE ".$cl_df;
		$cl_dl = "CREATE ".$this->h2s($cl_dl);

		$this->request("USE `".$_DBS."`;", "", [], __LINE__);

		$this->delete_sub($_DB, $cl_tr, $cl_in);

		$result = $this->request($cl_df, "", [], __LINE__);

		if(!$result[0]){

			$this->request($cl_dl, "", [], __LINE__);
		}
	}


	public function delete_sub($_DB, $cl_tr, $cl_in)
	{
		$_DBS = $this->h2s($_DB);
		$cl_inS = $this->h2s($cl_in);

		$this->request("USE `".$_DBS."`;", "", [], __LINE__);

		if($cl_tr === "views"){

			$this->request("DROP VIEW `".$cl_inS."`;", "", [], __LINE__);
		}
		elseif($cl_tr === "events"){

			$this->request("DROP EVENT `".$cl_inS."`;", "", [], __LINE__);
		}
		elseif($cl_tr === "triggers"){

			$this->request("DROP TRIGGER `".$cl_inS."`;", "", [], __LINE__);
		}
		elseif($cl_tr === "procedure"){

			$this->request("DROP PROCEDURE `".$cl_inS."`;", "", [], __LINE__);
		}
		elseif($cl_tr === "function"){

			$this->request("DROP FUNCTION `".$cl_inS."`;", "", [], __LINE__);
		}
	}


	public function rename_tb($_DB, $tb_name, $tb_name_new)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $this->DS))
		{
			$tb_name = $this->h2s($tb_name);

			$tb_name_new = $this->set_name($tb_name_new);

			$this->request("USE `".$_DBS."`;", "", [], __LINE__);

			$result = $this->request("RENAME TABLE `".$tb_name."` TO `".$tb_name_new."`;", "", [], __LINE__);

			return $result[0];
		}
	}


	public function clear_tb($_DB, $list_tb)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $this->DS))
		{
			if( isset($list_tb) )
			{
				foreach($list_tb as $val)
				{
					$valS = $this->h2s($val);

					if($val !== ""){

						$this->request("DELETE FROM `$_DBS`.`$valS`;", "", [], __LINE__);
					}
				}
			}
		}
	}


	public function delete_tb($_DB, $list_tb)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $this->DS))
		{
			$VIEW = [];

			$result = $this->request("SELECT TABLE_NAME
				FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_DB."';", "", [], __LINE__);

			while( $row = $this->fetch_assoc($result[1]) ){ $VIEW[] = $row["TABLE_NAME"];}

			$A = [];
			$B = [];

			foreach($list_tb as $val){

				$valS = $this->h2s($val);

				if(in_array($valS, $VIEW)){	$A[] = "`".$valS."`"; }
				else{ $B[] = "`".$valS."`"; }
			}

			$this->request("USE `".$_DBS."`;", "", [], __LINE__);

			if(count($A) > 0){ $this->request("DROP VIEW ".implode(", ", $A).";", "", [], __LINE__); }

			if(count($B) > 0){ $this->request("DROP TABLE ".implode(", ", $B).";", "", [], __LINE__); }
		}
	}


	public function update_tb($_DB, $_TB, $cl_df)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$this->request("USE `".$_DBS."`;", "", [], __LINE__);

		$this->request("ALTER TABLE `".$_TBS."` ".$cl_df, "", [], __LINE__);
	}


	public function update_rc($_DB, $_TB, $key, $field, $file, $blob_ch, $function, $action)
	{
		$type = [];
		$this->check_field($_DB, $_TB, $type);
		$tk = array_keys($type);

		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$sfC = [];
		$sfV = [];
		$sfK = [];

		foreach($key as $kh=>$vh)
		{
			$k = $this->h2s($kh);

			$v = $this->escape($this->h2s($vh));

			if($type[$k]["DATA_TYPE"] == "bit"){

				$sfK[] = "`".$k."`=b'".$v."' ";
			}
			elseif(in_array($k, $tk)){

				$sfK[] = "`".$k."`='".$v."' ";
			}
			else{ return; }
		}

		$this->request("USE `".$_DBS."`;", "", [], __LINE__);

		foreach($field as $kh=>$vh)
		{
			$k = $this->h2s($kh);

			$v = $this->escape($vh);

			$PRE = "";

			if($type[$k]["DATA_TYPE"] === "bit"){

				$PRE = "b";
			}

			if(in_array($type[$k]["DATA_TYPE"], $this->ext["geo"]) && ($v !== ""))
			{
				if($action === "_UPDATE_RC")
				{
					if($v != ""){

						$sfV[] = "`".$k."`=ST_GeomFromText('".$v."')";
					}
					else{

						$sfV[] = "`".$k."`=NULL";
					}
				}
				elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
				{
					$sfC[] = "`".$k."`";

					$sfV[] = "ST_GeomFromText('".$v."')";
				}
			}
			elseif(in_array($type[$k]["DATA_TYPE"], $this->ext["blob"]))
			{
				if($action === "_UPDATE_RC")
				{
					if(isset($blob_ch[$kh]) && ($blob_ch[$kh] === "2"))
					{
						if(isset($file[$kh]) && ($file[$kh] !== "")){

							$sfV[] = "`".$k."`=x'".$this->s2h(base64_decode($file[$kh]))."'";
						}
						elseif(isset($field[$kh]) && ($field[$kh] !== "")){

							$sfV[] = "`".$k."`=x'".$this->s2h($v)."'";
						}
						else{

							$sfV[] = "`".$k."`=NULL";
						}
					}
				}
				elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
				{
					if(isset($blob_ch[$kh]) && ($blob_ch[$kh] === "2"))
					{
						if(isset($file[$kh]) && ($file[$kh] !== "")){

							$sfC[] = "`".$k."`";

							$sfV[] = "x'".$this->s2h(base64_decode($file[$this->s2h($k)]))."'";
						}
						elseif(isset($field[$kh]) && ($field[$kh] !== "")){

							$sfC[] = "`".$k."`";

							$sfV[] = "x'".$this->s2h($v)."'";
						}
					}
					elseif((isset($blob_ch[$kh]) && ($blob_ch[$kh] === "1")) &&
						($action === "_COPY_RC"))
					{
						$sfC[] = "`".$k."`";

						$result = $this->request(
							"SELECT `".$k."` FROM `".$_TBS."` WHERE ".implode(" AND ", $sfK)." LIMIT 1;",
							"", [], __LINE__);

						if($result[0]){

							$res = $this->fetch_assoc($result[1]);

							$sfV[] = "x'".$this->s2h($res[$k])."'";
						}
					}
				}
			}
			elseif(($type[$k]["DATA_TYPE"] === "varbinary") || ($type[$k]["DATA_TYPE"] === "binary"))
			{
				if($action === "_UPDATE_RC")
				{
					if(isset($function[$kh]) && ($function[$kh] !== "")){

						$sfV[] = "`".$k."`=(".stripslashes($v).")";
					}
					elseif(($file[$this->s2h($k)] === $v) && ($v !== "")){

						$sfV[] = "`".$k."`=x'".$v."'";
					}
					else{

						$sfV[] = "`".$k."`=x'".$this->s2h($v)."'";
					}
				}
				elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
				{
					$sfC[] = "`".$k."`";

					if(isset($function[$kh]) && ($function[$kh] !== "")){

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
			elseif(in_array($k, $tk) && ($v !== ""))
			{
				if($action === "_UPDATE_RC")
				{
					if(isset($function[$kh]) && ($function[$kh] !== "")){

						$sfV[] = "`".$k."`=(".stripslashes($v).")";
					}
					else{

						$sfV[] = "`".$k."`=".$PRE."'".$v."'";
					}
				}
				elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
				{
					$sfC[] = "`".$k."`";

					if(isset($function[$kh]) && ($function[$kh] !== "")){

						$sfV[] = "(".stripslashes($v).")";
					}
					else{

						$sfV[] = $PRE."'".$v."'";
					}
				}
			}
			elseif(in_array($k, $tk) && ($v === ""))
			{
				if($action === "_UPDATE_RC"){

					$sfV[] = "`".$k."`=NULL";
				}
				elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC")){

					$sfC[] = "`".$k."`";
					$sfV[] = "NULL";
				}
			}
			else{ return; }
		}

		if(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
		{
			if(count($sfC) !== 0){

				$this->request(
					"INSERT INTO `".$_TBS."` (".implode(", ", $sfC).") VALUES (".implode(", ", $sfV).");",
					"", [], __LINE__);
			}
		}
		else
		{
			$WHERE = "";
			if(count($sfK) !== 0){

				$WHERE = "WHERE ".implode(" AND ", $sfK);
			}

			if(count($sfV) !== 0){

				$this->request(
					"UPDATE `".$_TBS."` SET ".implode(", ", $sfV)." ".$WHERE." LIMIT 1;",
					"", [], __LINE__);
			}
		}

		if(($_DBS === "mysql") && (($_TBS === "user") || ($_TBS === "db") || ($_TBS === "tables_priv") || ($_TBS === "columns_priv")))
		{
			$this->request("FLUSH PRIVILEGES;", "", [], __LINE__);
		}
	}


	public function delete_rc($_DB, $_TB, $key)
	{
		$_DBS = $this->h2s($_DB);

		if(!in_array($_DBS, $this->DS))
		{
			$type = [];
			$this->check_field($_DB, $_TB, $type);

			$_DBS = $this->h2s($_DB);
			$_TBS = $this->h2s($_TB);

			$sfK = [];
			foreach($key as $kh=>$vh)
			{
				$k = $this->h2s($kh);

				$v = $this->escape($this->h2s($vh));

				if($type[$k]["DATA_TYPE"] == "bit"){

					$sfK[] = "`".$k."`=b'".$v."' ";
				}
				else{

					$sfK[] = "`".$k."`='".$v."' ";
				}
			}

			$this->request("USE `".$_DBS."`;", "", [], __LINE__);

			$this->request("DELETE FROM `$_TBS` WHERE ".implode(" AND ", $sfK).";", "", [], __LINE__);
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

		if($use !== ""){ $this->request("USE `".$use."`;", "", [], __LINE__); }

		foreach($list_script as $script)
		{
			if((trim($script) != "") && (trim($script) != ";")){

				$this->sqls_eval($script);
			}
		}

		$this->dbc->query( "SET sql_mode = '".$this->sql_mode."';" );

		$this->dbc->query( "SET names '".$this->character_name."';" );
	}


	private function check_field($_DB, $_TB, &$type)
	{
		$result = $this->request("select COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE
			from information_schema.columns
			where TABLE_SCHEMA=x'".$_DB."'
			AND table_name = x'".$_TB."';", "", [], __LINE__);

		while($row = $this->fetch_assoc($result[1])){

			$type[$row["COLUMN_NAME"]]["DATA_TYPE"] = $row["DATA_TYPE"];
			$type[$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] = $row["COLUMN_DEFAULT"];
			$type[$row["COLUMN_NAME"]]["IS_NULLABLE"] = $row["IS_NULLABLE"];
		}
	}


	private function get_sub($_DB, $_DBS, $tb, $target, $create, $searching, $add)
	{
		$RT = [];

		$result = $this->request("SELECT ".$target."_NAME
			FROM information_schema.".$tb." WHERE ".$add." ".$target."_SCHEMA=x'".$_DB."';",
			"", [], __LINE__, false);

		if($result[0])
		{
			while( $row = $this->fetch_assoc($result[1]) ){

				$trigger = $this->request($create." `$_DBS`.`".$row[$target."_NAME"]."`;", "", [], __LINE__);

				while( $row_trigger = $this->fetch_assoc($trigger[1]) ){

					$RT[$row[$target."_NAME"]] = $row_trigger[$searching];
				}
			}
		}

		return $RT;
	}


	private function get_view_tr($CT, $_DBS, $valS, $copy_2bdS, $_TBST)
	{
		if(preg_match("/ANSI/", $this->sql_mode))
		{
			$_DBS = preg_replace("/\"/", "\"\"",$_DBS);
			$valS = preg_replace("/\"/", "\"\"",$valS);

			$copy_2bdS = preg_replace("/\"/", "\"\"",$copy_2bdS);
			$_TBST = preg_replace("/\"/", "\"\"",$_TBST);

			if($copy_2bdS !== ""){$copy_2bdS = ' "'.$copy_2bdS.'".';}

			$CT = preg_replace('/ VIEW "'.
				addcslashes($_DBS,"/\^.$|()[]*+?{},").'"."'.
				addcslashes($valS,"/\^.$|()[]*+?{},").'" /',
				' VIEW '.$copy_2bdS.'"'.$_TBST.'" ', $CT);
		}
		else
		{
			if($copy_2bdS !== ""){$copy_2bdS = " `".$copy_2bdS."`.";}

			$CT = preg_replace("/ VIEW `".
				addcslashes($_DBS,"/\^.$|()[]*+?{},")."`.`".
				addcslashes($valS,"/\^.$|()[]*+?{},")."` /",
				" VIEW ".$copy_2bdS."`".$_TBST."` ", $CT);
		}

		return $CT;
	}


	private function get_fv($nv, $field, $fl)
	{
		if($fl === "rc")
		{
			if(in_array($field[$nv]["DATA_TYPE"], $this->ext["geo"]) ||
				in_array($field[$nv]["DATA_TYPE"], $this->ext["char"]) ||
				in_array($field[$nv]["DATA_TYPE"], $this->ext["text"]) ||
				($field[$nv]["DATA_TYPE"] === "set") ||
				($field[$nv]["DATA_TYPE"] === "enum")
			){
				$FILTER_EX = [
					_NOTE_FILTER_OPERATOR,"=","<>","LIKE %...%","NOT LIKE %...%",
					"REGEXP","NOT REGEXP","IS NULL","IS NOT NULL"];
			}
			else
			{
				$FILTER_EX = [
					_NOTE_FILTER_OPERATOR,"=","<>",">","<","LIKE %...%","NOT LIKE %...%",
					"REGEXP","NOT REGEXP","IS NULL","IS NOT NULL"];
			}
		}

		elseif($fl === "tb"){

			$FILTER_EX = [
				_NOTE_FILTER_OPERATOR,"=","<>","LIKE %...%","NOT LIKE %...%",
				"REGEXP","NOT REGEXP","IS NULL","IS NOT NULL"];
		}

		elseif($fl === "db"){

			$FILTER_EX = [
			_NOTE_FILTER_OPERATOR,"=","<>","LIKE %...%","NOT LIKE %...%",
			"REGEXP","NOT REGEXP"];
		}

		return $FILTER_EX;
	}


	private function get_wr($nv, $field, $fl)
	{
		$WA = [];

		foreach($nv["fl_value_".$fl] as $k=>$v)
		{
			if((($nv["fl_operator_".$fl][$k] !== _NOTE_FILTER_OPERATOR) && ($v !== "")) ||
			($nv["fl_operator_".$fl][$k] === "IS NULL")	||
			($nv["fl_operator_".$fl][$k] === "IS NOT NULL"))
			{
				$nvt["fl_field_".$fl] = $nv["fl_field_".$fl][$k];
				$nvt["fl_value_".$fl] = $nv["fl_value_".$fl][$k];
				$nvt["fl_operator_".$fl] = $nv["fl_operator_".$fl][$k];

				$WA[] = $nv["fl_and_".$fl][$k];

				$WA[] = $this->get_wra($nvt, $field, $fl);
			}
		}

		if(isset($WA[0]) && in_array($WA[0], ["AND","OR"])){

			array_shift($WA);
		}

		return implode(" ", $WA);
	}


	private function get_wra($nvt, $field, $fl)
	{
		$WHERE = "";

		if($nvt["fl_operator_".$fl] === "LIKE %...%"){

			$nvt["fl_operator_".$fl] = "LIKE";
			$nvt["fl_value_".$fl] = "'%".addslashes($nvt["fl_value_".$fl])."%'";
		}
		elseif($nvt["fl_operator_".$fl] === "NOT LIKE %...%"){

			$nvt["fl_operator_".$fl] = "NOT LIKE";
			$nvt["fl_value_".$fl] = "'%".addslashes($nvt["fl_value_".$fl])."%'";
		}
		elseif(($nvt["fl_operator_".$fl] === "IS NULL") || ($nvt["fl_operator_".$fl] === "IS NOT NULL"))
		{
			$nvt["fl_value_".$fl] = "";
		}
		else{

			$nvt["fl_value_".$fl] = "'".addslashes($nvt["fl_value_".$fl])."'";
		}

		if($fl !== "rc"){

			$WHERE .= " `".$nvt["fl_field_".$fl]."` ".
				$nvt["fl_operator_".$fl]." ".$nvt["fl_value_".$fl]."";

			return $WHERE;
		}

		if($field[$nvt["fl_field_rc"]]["DATA_TYPE"] === "bit")
		{
			$WHERE .= " LPAD(BIN(`".$nvt["fl_field_rc"]."`), ".
				$field[$nvt["fl_field_rc"]]["NUMERIC_PRECISION"].", '0') ".
				$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."";
		}
		elseif($field[$nvt["fl_field_rc"]]["DATA_TYPE"] === "float")
		{
			$WHERE .= " abs(`".$nvt["fl_field_rc"]."`) ".
				$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."";
		}
		elseif(in_array($field[$nvt["fl_field_rc"]]["DATA_TYPE"], $this->ext["geo"]))
		{
			$WHERE .= " ST_AsText(".$nvt["fl_field_rc"].") ".
				$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."";
		}
		elseif(
				in_array($field[$nvt["fl_field_rc"]]["DATA_TYPE"], $this->ext["char"]) ||
				in_array($field[$nvt["fl_field_rc"]]["DATA_TYPE"], $this->ext["text"]))
		{
			$WHERE .= (($nvt["fl_operator_rc"] === "LIKE") || ($nvt["fl_operator_rc"] === "NOT LIKE")) ?
				" `".$nvt["fl_field_rc"]."` ".$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."" :
				" CAST(`".$nvt["fl_field_rc"]."` AS CHAR) ".$nvt["fl_operator_rc"]." ".
				$nvt["fl_value_rc"]."";
		}
		else
		{
			$WHERE .= " `".$nvt["fl_field_rc"]."` ".$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."";
		}


		return $WHERE;
	}


}
