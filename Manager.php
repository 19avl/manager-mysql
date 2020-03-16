<?php

/*
Copyright (c) 2018-2020 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();

Class Manager extends Wr_sql
{
	use Convert;

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
		$RT["FIELD_ST"] = ["schema_name", "charset_name", "collation_name"];
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



		$result = $this->request("SELECT COUNT(*)
			FROM information_schema.SCHEMATA ".$WHERE." ;", __LINE__);
		if($result[0]){	if($result[1]->fetch_row()[0] <= $nv["from_db"]){$nv["from_db"] = "0";} }

		$result = $this->request("SELECT SQL_CALC_FOUND_ROWS
			SCHEMA_NAME, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
			FROM information_schema.SCHEMATA ".$WHERE." ORDER BY ".
			($nv["order_db"]+1)." LIMIT ".$nv["from_db"].", ".$nv["page_db"].";", __LINE__);

		if($result[0]){

			$count = $this->request("SELECT FOUND_ROWS();", __LINE__);
			$RT["COUNT"] = $count[1]->fetch_row()[0];

			while( $row = $result[1]->fetch_assoc() )
			{
				$RT["DB"][$row["SCHEMA_NAME"]] = $row;
				$RT["DB"][$row["SCHEMA_NAME"]]["COUNT"] = "";

				$lines = $this->request("SELECT COUNT(*)
					FROM information_schema.TABLES WHERE `TABLE_SCHEMA`=x'".
					$this->s2h($row["SCHEMA_NAME"])."';", __LINE__);

				if($lines[0]){

					$RT["DB"][$row["SCHEMA_NAME"]]["COUNT"] = $lines[1]->fetch_row()[0];
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

	public function info()
	{
		$RT = [];

		if(isset($GLOBALS["_SERVER"]["SERVER_SOFTWARE"])){

			$RT[] = $GLOBALS["_SERVER"]["SERVER_SOFTWARE"];
		}

		$RT[] = "&nbsp;";

		$RT[] = "CLIENT_INFO: ".$this->dbc->client_info;
		$RT[] = "SERVER_INFO: ".$this->dbc->server_info;
		$RT[] = "CHARACTER_SET_SERVER: ".$this->dbc->character_set_name();
		$RT[] = "&nbsp;";

		$result = $this->request("SELECT CURRENT_USER();", __LINE__);
		if($result[0]){

			$RT[] = "USER: ".$result[1]->fetch_row()[0];
		}

		return $RT;
	}

	public function tb($_DB, $nv, $LIMIT)
	{
		$_DBS = $this->h2s($_DB);

		$RT = [];
		$RT["DB"] = $_DBS;
		$RT["CREATE"] = [];
		$RT["TRIGGERS"] = [];
		$RT["PROCEDURE"] = [];
		$RT["FUNCTION"] = [];
		$RT["EVENTS"] = [];
		$RT["TABLES"] = [];
		$RT["COUNT"] = "";
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["TABLES"];
		$RT["FIELD_ST"] = ["table_name", "create_time", "update_time", "engine", "table_collation"];
		$RT["FIELD_SE"] = ["CREATE_TIME", "UPDATE_TIME", "ENGINE", "TABLE_COLLATION", "AUTO_INCREMENT"];
		$RT["FILTER_EX"] = ["...","=","<>","LIKE"];

		$VIEW = [];
		$OPEN_TABLES = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", __LINE__);

		if($CREATE[0])
		{
			$RT["CREATE"]["DB"] = $CREATE[1]->fetch_row()[1];

			$this->use_db($_DBS);

			$RT["TRIGGERS"] = $this->get_sub($_DB, $_DBS,
				"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER", "SQL Original Statement", "");

			$RT["PROCEDURE"] = $this->get_sub($_DB, $_DBS,
				"ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE", "Create Procedure", "ROUTINE_TYPE='PROCEDURE' AND");

			$RT["FUNCTION"] = $this->get_sub($_DB, $_DBS,
				"ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION", "Create Function", "ROUTINE_TYPE='FUNCTION' AND");

			$RT["EVENTS"] = $this->get_sub($_DB, $_DBS,
				"EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", "");

			$result = $this->request("SELECT TABLE_NAME
				FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_DB."';", __LINE__);

			if($result[0]){

				while( $row = $result[1]->fetch_assoc() ){ $VIEW[] = $row["TABLE_NAME"];}
			}

			$result = $this->request("SHOW OPEN TABLES FROM `$_DBS` WHERE In_use>0;", __LINE__);

			while( $row = $result[1]->fetch_assoc() ){ $OPEN_TABLES[] = $row["Table"];}

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

				if($result[1]->fetch_row()[0] <= $nv["from_tb"]){$nv["from_tb"] = "0";}
			}

			$result = $this->request("SELECT SQL_CALC_FOUND_ROWS
				TABLE_NAME, CREATE_TIME, UPDATE_TIME, ENGINE, TABLE_COLLATION, AUTO_INCREMENT
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_DB."' ".$WHERE." ORDER BY ".
				($nv["order_tb"]+1)." LIMIT ".$nv["from_tb"].", ".$nv["page_tb"].";", __LINE__);

			if($result[0]){

				$count = $this->request("SELECT FOUND_ROWS();", __LINE__);
				$RT["COUNT"] = $count[1]->fetch_row()[0];

				while( $row = $result[1]->fetch_assoc() )
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

							$RT["TABLES"][$row["TABLE_NAME"]]["COUNT"] = $lines[1]->fetch_row()[0];
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


	public function rc($_DB, $_TB, $nv, $exceptions, $LIMIT)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$RT = [];
		$RT["DB"] = $_DBS;
		$RT["TB"] = $_TBS;
		$RT["CREATE"] = [];
		$RT["PRI"] = false;
		$RT["EXCEPT"] = false;
		$RT["VIEW"] = false;
		$RT["FIELDS"] = [];
		$RT["RECORDS"] = [];
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["RECORDS"];
		$RT["COUNT"] = 0;
		$RT["FIELD_ST"] = [];
		$RT["FILTER_EX"] = ["...","=","<>",">","<","LIKE"];

		$CONSTRAINT = [];
		$LIST = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_DBS`", __LINE__);

		if($CREATE[0]){

			$RT["CREATE"]["DB"] = $CREATE[1]->fetch_row()[1];
		}

		$CREATE = $this->request("SHOW CREATE TABLE `$_DBS`.`$_TBS`;", __LINE__);

		if($CREATE[0])
		{
			$RT["CREATE"]["TB"] = $CREATE[1]->fetch_row()[1];

			$result = $this->request("SELECT COUNT(*)
				FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$_TB."';", __LINE__);

			while( $row = $result[1]->fetch_row() ){

				if($row[0] !== "0"){$RT["VIEW"] = true;}
			}

			$result = $this->request("SELECT kc.COLUMN_NAME, tc.CONSTRAINT_NAME, tc.CONSTRAINT_TYPE
				FROM information_schema.TABLE_CONSTRAINTS tc JOIN information_schema.KEY_COLUMN_USAGE kc
				ON tc.TABLE_SCHEMA = kc.TABLE_SCHEMA AND tc.TABLE_NAME = kc.TABLE_NAME
				AND tc.CONSTRAINT_NAME = kc.CONSTRAINT_NAME
				WHERE tc.TABLE_SCHEMA = x'".$_DB."' AND tc.TABLE_NAME = x'".$_TB."';", __LINE__);

			while( $row = $result[1]->fetch_row() ){

				$CONSTRAINT[$row[0]][] = $row[2];
			}

			$result = $this->request("select
				COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA
				from information_schema.columns where TABLE_SCHEMA=x'".$_DB."'
				AND table_name = x'".$_TB."' ORDER BY ORDINAL_POSITION;", __LINE__);

			if($result[0])
			{
				while($row = $result[1]->fetch_assoc())
				{
					$RT["FIELDS"][$row["COLUMN_NAME"]] = $row;

					if(isset($CONSTRAINT[$row["COLUMN_NAME"]])){

						$RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"] = $CONSTRAINT[$row["COLUMN_NAME"]];
					}
					else{

					$RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"] = [];
					}

					$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_TYPE"] =
						preg_replace("/\'\'/", "'", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_TYPE"]);

					$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = [];

					if(($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "enum") ||
						($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "set")){

						$temp = preg_replace("/^(enum|set)\('/", "", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_TYPE"]);
						$temp = preg_replace("/'\)$/", "", $temp);
						$temp = str_replace("\\\\", "\\", $temp);

						$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = explode("','", $temp);
					}

					$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = false;

					if(in_array("FOREIGN KEY", $RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"]))
					{
						$constraint = $this->request("SELECT
							REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
							FROM information_schema.KEY_COLUMN_USAGE
							WHERE TABLE_SCHEMA = x'".$_DB."' AND TABLE_NAME = x'".$_TB."' AND
							COLUMN_NAME = '".$row['COLUMN_NAME']."' AND
							CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME is not null;", __LINE__);

						$row_constraint = $constraint[1]->fetch_assoc();

						$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = [];

						if($row_constraint["REFERENCED_COLUMN_NAME"] && ($row_constraint["REFERENCED_COLUMN_NAME"] !== ""))
						{
							$constraint_value = $this->request("SELECT ".
								$row_constraint["REFERENCED_COLUMN_NAME"]." FROM `".
								$row_constraint["REFERENCED_TABLE_SCHEMA"]."`.`".
								$row_constraint["REFERENCED_TABLE_NAME"]."`;", __LINE__);

							if($constraint_value[0]){

								while($row_constraint_value = $constraint_value[1]->fetch_row()){

									$RT["FIELDS"][$row['COLUMN_NAME']]["COLUMN_VALUE"][] = $row_constraint_value[0];
								}
							}

							$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = true;
						}
					}

					if($row["COLUMN_KEY"] == "PRI"){$RT["PRI"] = true;}
					if(in_array( $row["COLUMN_TYPE"], $exceptions)){$RT["EXCEPT"] = true;}

					$RT["FIELD_ST"][] =  $row["COLUMN_NAME"];

					if($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "bit"){

						$LIST[] = "BIN(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
					}
					elseif(in_array( $row["COLUMN_TYPE"], $exceptions)){

						if($this->server_version < 80000){

							$LIST[] = "AsText(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
						}
						else{

							$LIST[] = "ST_AsText(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
						}
					}
					else{

						$LIST[] = "`".$row["COLUMN_NAME"]."`";
					}
				}

				if(!$RT["VIEW"]){

					if(!$RT["PRI"]){ $this->_LOG["MESSAGE"][] = _MESSAGE_MISSING_PRI; }
					if($RT["EXCEPT"]){ $this->_LOG["MESSAGE"][] = _MESSAGE_SPATIAL_TYPE_PROCESSED; }
				}
			}

			if(in_array($nv["fl_operator_rc"], $RT["FILTER_EX"]))
			{
				if(($RT["FIELDS"][$this->h2s($nv["fl_field_rc"])]["DATA_TYPE"] === "bit") &&
					preg_match("/^[01]{1,}$/", $nv["fl_value_rc"]))
				{
					$WHERE = ($nv["fl_operator_rc"] === "LIKE") ?
						"WHERE BIN(`".$this->h2s($nv["fl_field_rc"])."`) LIKE '%".$nv["fl_value_rc"]."%'" :
						"WHERE CAST(`".$this->h2s($nv["fl_field_rc"])."` AS CHAR) ".
						$nv["fl_operator_rc"]." b'".$nv["fl_value_rc"]."'";
				}
				else
				{
					$fl_value_rc = addslashes($nv["fl_value_rc"]);

					$WHERE = ($nv["fl_operator_rc"] === "LIKE") ?
						"WHERE `".$this->h2s($nv["fl_field_rc"])."` LIKE '%".addslashes($fl_value_rc)."%'" :
						"WHERE CAST(`".$this->h2s($nv["fl_field_rc"])."` AS CHAR) ".
						$nv["fl_operator_rc"]." '".$fl_value_rc."'";
				}
			}
			else{ $WHERE = ""; }

			if($nv["page_rc"] === "0"){$nv["page_rc"] = $RT["ON_PAGE"][0];}

			$result = $this->request("SELECT COUNT(*)
				FROM `$_DBS`.`$_TBS` ".$WHERE." ;", __LINE__);
			if($result[0]){	if($result[1]->fetch_row()[0] <= $nv["from_rc"]){$nv["from_rc"] = "0";} }

			$result = $this->request("SELECT SQL_CALC_FOUND_ROWS ".implode(", ",  $LIST).
				" FROM `$_DBS`.`$_TBS` ".$WHERE." ORDER BY ".
				($nv["order_rc"]+1)." LIMIT ".$nv["from_rc"].", ".$nv["page_rc"].";", __LINE__);

			if($result[0]){

				$count = $this->request("SELECT FOUND_ROWS();", __LINE__);
				$RT["COUNT"] = $count[1]->fetch_row()[0];

				while($res = $result[1]->fetch_assoc()){

					$RT["RECORDS"][] = $res;
				}
			}

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

		return $RT;
	}


	public function get_list_tb($_DB)
	{
		$RT = [];

		$_DBS = $this->h2s($_DB);

		$result = $this->request("SHOW TABLES FROM `$_DBS`;", __LINE__);

		if($result[0]){

			while($row = $result[1]->fetch_row()){

				$RT[] = $this->s2h($row[0]);
			}
		}
		return $RT;
	}


	public function searching($_DB, $list_tb, $find)
	{
		$_DBS = $this->h2s($_DB);

		$count = 0;

		$find = trim($find);

		$this->_LOG["RESULT"][] = "<br>"._MESSAGE_SEARCHING."<br>";

		if(isset($list_tb))
		{
			foreach($list_tb as $val)
			{
				$valS = $this->h2s($val);

				$result = $this->request("SELECT * FROM `$_DBS`.`$valS`;", __LINE__);

				if($result[0])
				{
					while($row = $result[1]->fetch_assoc()){

						$str = "";

						foreach($row as $k=>$v){

							$str = $k.$v;

							if(stristr($str, $find)){

								$this->_LOG["RESULT"][] = htmlentities($_DBS).".".htmlentities($valS).
									":<br>[ ".htmlentities($k)." ] - ".htmlentities($v);

								$count += 1;
							}
						}
					}
				}
			}
		}

		if($count == 0){ $this->_LOG["RESULT"][] = _MESSAGE_FIND_NOT_FOUND." ".htmlentities($find); }
	}


	public function copy_tb($_DB, $list_tb, $copy_2bd, $name_new, $pre=false)
	{
		$_DBS = $this->h2s($_DB);
		$copy_2bdS = $this->h2s($copy_2bd);

		if( isset($list_tb) )
		{
			foreach($list_tb as $val)
			{
				$VIEW = false;

				$view = $this->request("SELECT COUNT(*)
					FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$val."';", __LINE__);

				if(($view[1]->fetch_row()[0] !== "0") && ($_DBS !== $copy_2bdS)){ $VIEW = true; }

				if(!$VIEW)
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

							$this->request(
								"INSERT INTO `".$copy_2bdS."`.`".$tbs_new."` SELECT * FROM `".$_DBS."`.`".$valS."`;", __LINE__);

							$constraint = $this->request("SELECT
								COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME,
								REFERENCED_COLUMN_NAME, CONSTRAINT_NAME
								FROM information_schema.KEY_COLUMN_USAGE
								WHERE TABLE_SCHEMA = x'".$_DB."' AND TABLE_NAME = x'".$val."' AND
								CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME is not null;", __LINE__);

							while($row_constraint = $constraint[1]->fetch_assoc())
							{
								$referent = $this->request("SELECT UPDATE_RULE, DELETE_RULE
									FROM information_schema.REFERENTIAL_CONSTRAINTS
									WHERE CONSTRAINT_SCHEMA = x'".$_DB."'
									AND TABLE_NAME = x'".$val."'
									AND CONSTRAINT_NAME='".$row_constraint["CONSTRAINT_NAME"]."';", __LINE__);

								$row_referent = $referent[1]->fetch_assoc();

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


	public function export($_DB, $list_tb, $exceptions)
	{
		$_DBS = $this->h2s($_DB);

		$field = [];
		$string = "";

		foreach($list_tb as $val)
		{
			$VIEW = true;

			$result = $this->request("SELECT COUNT(*)
				FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_DB."' AND TABLE_NAME=x'".$val."';", __LINE__);

			while( $row = $result[1]->fetch_row() ){

				if($row[0] !== "0"){$VIEW = false;}
			}

			$valS = $this->h2s($val);

			$string .= PHP_EOL.PHP_EOL;

			$result = $this->request("SHOW CREATE TABLE `$_DBS`.`$valS`;", __LINE__);

			if($result[0]){

				$string .= print_r ($result[1]->fetch_row()[1], true).";".PHP_EOL.PHP_EOL;
			}

			if($VIEW)
			{
				$result = $this->request("SHOW COLUMNS FROM `$_DBS`.`$valS`;", __LINE__);

				if($result[0])
				{
					while($fl = $result[1]->fetch_assoc()){

						$field[$fl["Field"]] = $fl["Type"];
					}

					$result = $this->request("SELECT * FROM `$_DBS`.`$valS`;", __LINE__);

					if($result[0])
					{
						$r = [];

						while($row = $result[1]->fetch_assoc()){

							foreach($row as $k=>$v){

								if(strpos($field[$k], "bit") === 0){

									$row[$k] = "b'".base_convert($row[$k], 10, 2)."'";
								}
								elseif(in_array( $field[$k], $exceptions)){

									$row[$k] = "x'".$this->s2h($row[$k])."'";

								}
								else{

									if($v === NULL){ $row[$k] = "NULL"; }
									else{ $row[$k] = "'".addslashes($row[$k])."'"; }
								}
							}

							$r[] = "(".implode(",", $row).")";
						}

						if(count($r) != 0){

							$string .= "insert into `".$valS."` values".PHP_EOL.implode(",".PHP_EOL, $r).";".PHP_EOL;
						}
					}
				}
			}
		}

		return $string;
	}


	public function export_db($list_db, $exceptions)
	{
		$string = PHP_EOL."SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
		$string .= PHP_EOL."SET FOREIGN_KEY_CHECKS=0;".PHP_EOL;

		$filename = "dump_".date("d-m-Y").".txt";

		foreach($list_db as $value){

			$_DBS = $this->h2s($value);

			$result = $this->request("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
				FROM information_schema.SCHEMATA where SCHEMA_NAME=x'".$value."';", __LINE__);

			if($result[0]){

				$schema = $result[1]->fetch_row();

				$string .= PHP_EOL."CREATE DATABASE `".$_DBS."` CHARACTER SET ".$schema[0]." COLLATE ".$schema[1].";";

				$string .= PHP_EOL."USE `$_DBS`;";

				$string .= PHP_EOL.$this->export($value, $this->get_list_tb($value), $exceptions);
			}
		}

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

		$string .= PHP_EOL."SET FOREIGN_KEY_CHECKS=1;".PHP_EOL;

		$this->export_get($filename, $string);
	}


	private function export_get($filename, $string)
	{
		if(!isset($this->_LOG["MESSAGE"][0]))
		{
			ob_start();
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=".$filename);
			header("Content-Transfer-Encoding: binary");
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Content-Length: ".strlen($string));
			print $string;
			ob_get_flush();
		}
	}


	public function export_tb($_DB, $list_tb, $exceptions)
	{
		$string = PHP_EOL."SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
		$string .= PHP_EOL."SET FOREIGN_KEY_CHECKS=0;";

		$filename = "dump_".date("d-m-Y").".txt";

		$string .= $this->export($_DB, $list_tb, $exceptions);

		$string .= PHP_EOL."SET FOREIGN_KEY_CHECKS=1;".PHP_EOL;

		$this->export_get($filename, $string);
	}


	public function clear_db($list_db)
	{
		$A = ["information_schema","mysql","performance_schema"];

		foreach($list_db as $val){

			$valS = $this->h2s($val);

			$result = $this->request("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
				FROM information_schema.SCHEMATA where SCHEMA_NAME=x'".$val."';", __LINE__);

			if($result[0])
			{
				$schema = $result[1]->fetch_row();
				$CREATE = "CREATE DATABASE `".$valS."` CHARACTER SET ".$schema[0]." COLLATE ".$schema[1].";";

				if(($val !== "") && (!in_array($valS, $A))){

					$this->request("DROP DATABASE `$valS`;", __LINE__);

					$this->request($CREATE, __LINE__);
				}
			}
		}
	}


	public function delete_db($list_db)
	{
		$A = ["information_schema","mysql","performance_schema"];

		foreach($list_db as $val)
		{
			$valS = $this->h2s($val);

			if(($val !== "") && (!in_array($valS, $A))){

				$this->request("DROP DATABASE `$valS`;", __LINE__);
			}
		}
	}

	public function list_db_filter($nv)
	{
		$A = ["information_schema","mysql","performance_schema"];

		$RT = [];

		$FILTER_EX = ["...","=","<>",">","<","LIKE"];

		if(in_array($nv["fl_operator_db"], $FILTER_EX)){

			$WHERE = ($nv["fl_operator_db"] === "LIKE") ?
				"WHERE `".$this->h2s($nv["fl_field_db"])."` LIKE '%".addslashes($nv["fl_value_db"])."%'" :
				"WHERE `".$this->h2s($nv["fl_field_db"])."`".$nv["fl_operator_db"]."'".
				addslashes($nv["fl_value_db"])."'";
		}
		else{ $WHERE = ""; }

		$result = $this->request("SELECT  SCHEMA_NAME FROM information_schema.SCHEMATA ".$WHERE.";", __LINE__);

		if($result[0]){

			while( $row = $result[1]->fetch_assoc() )
			{
				if(($row["SCHEMA_NAME"] !== "") && (!in_array($row["SCHEMA_NAME"], $A))){

					$RT[] = $this->s2h($row["SCHEMA_NAME"]);
				}
			}
		}

		return $RT;
	}


	public function rename_tb($_DB, $tb_name, $tb_name_new)
	{
		$_DBS = $this->h2s($_DB);
		$tb_name = $this->h2s($tb_name);

		$this->use_db($_DBS);

		$this->request("RENAME TABLE `".$tb_name."` TO `".$tb_name_new."`;", __LINE__);

		if(!isset($this->_LOG["MESSAGE"])){return true;}
		return false;
	}


	public function clear_tb($_DB, $list_tb)
	{
		$_DBS = $this->h2s($_DB);

		if( isset($list_tb) )
		{
			foreach($list_tb as $val)
			{
				$valS = $this->h2s($val);

				if($val !== ""){

					$this->request("DELETE FROM `$_DBS`.`$valS`;", __LINE__);
				}
			}
		}
	}


	public function delete_tb($_DB, $list_tb)
	{
		$_DBS = $this->h2s($_DB);

		$VIEW = [];

		$result = $this->request("SELECT TABLE_NAME
			FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_DB."';", __LINE__);

		while( $row = $result[1]->fetch_assoc() ){ $VIEW[] = $row["TABLE_NAME"];}

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


	public function list_tb_filter($_DB, $nv)
	{
		$RT = [];

		if($nv["fl_operator_tb"] !== ""){

			$WHERE = ($nv["fl_operator_tb"] === "LIKE") ?
				"AND `".$this->h2s($nv["fl_field_tb"])."` LIKE '%".addslashes($nv["fl_value_tb"])."%'" :
				"AND `".$this->h2s($nv["fl_field_tb"])."`".$nv["fl_operator_tb"]."'".
				addslashes($nv["fl_value_tb"])."'";
		}
		else{ $WHERE = ""; }

		$result = $this->request("SELECT TABLE_NAME
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_DB."' ".$WHERE.";", __LINE__);

		if($result[0]){

			while( $row = $result[1]->fetch_assoc() ){

				$RT[] = $this->s2h($row["TABLE_NAME"]);
			}
		}

		return $RT;
	}


	public function insert_cl($_DB, $_TB, $cl_def, $cl_in)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);
		$cl_in = $this->h2s($cl_in);

		$position = "";
		if($cl_in == ""){ $position = "FIRST";}
		else{ $position = "AFTER `$cl_in`"; }

		$this->use_db($_DBS);

		$this->request("ALTER TABLE `".$_TBS."` ADD ".$cl_def." ".$position.";", __LINE__);
	}


	public function update_cl($_DB, $_TB, $cl_change, $cl_def)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);
		$cl_change = $this->h2s($cl_change);

		$this->use_db($_DBS);

		$this->request("ALTER TABLE `".$_TBS."` CHANGE COLUMN `".$cl_change."` ".$cl_def.";", __LINE__);
	}


	public function delete_cl($_DB, $_TB, $cl_del)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);
		$cl_del = $this->h2s($cl_del);

		$this->use_db($_DBS);

		$this->request("ALTER TABLE `".$_TBS."` DROP COLUMN `".$cl_del."`;", __LINE__);
	}


	public function update_tb($_DB, $_TB, $tb_def)
	{
		$_DBS = $this->h2s($_DB);
		$_TBS = $this->h2s($_TB);

		$this->use_db($_DBS);

		$this->request("ALTER TABLE `".$_TBS."` ".$tb_def.";", __LINE__);
	}


	public function insert_rc($_DB, $_TB, $field)
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

		$sfK = [];
		$sfV = [];

		foreach($field as $k=>$v)
		{
			$v = html_entity_decode($v, ENT_QUOTES);

			$PRE = "";

			$this->check_type($k, $v, $type, $PRE);

			$sfK[] = "`".$k."`";

			$sfV[] = $PRE."'".$v."'";
		}

		$this->use_db($_DBS);

		$this->request("INSERT INTO `".$_TBS."` (".implode(", ", $sfK).") VALUES (".implode(", ", $sfV).");", __LINE__);
	}


	public function update_rc($_DB, $_TB, $key, $field)
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

		$sfV = [];
		foreach($field as $k=>$v)
		{
			$v = html_entity_decode($v, ENT_QUOTES);

			$PRE = "";

			$this->check_type($k, $v, $type, $PRE);

			$sfV[] = "`".$k."`=".$PRE."'".$v."'";
		}

		$sfK = [];
		foreach($key as $k=>$v)
		{
			$PRE = "";

			$this->check_type($k, $v, $type, $PRE);

			$sfK[] = "`".$k."`=".$PRE."'".addslashes($v)."' ";
		}

		$this->use_db($_DBS);

		$this->request("UPDATE `".$_TBS."` SET ".implode(", ", $sfV)." WHERE ".implode(" AND ", $sfK)." LIMIT 1;", __LINE__);
	}


	public function delete_rc($_DB, $_TB, $key)
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


	private function check_field($_DB, $_TB, &$type)
	{
		$result = $this->request("select
			COLUMN_NAME, CHARACTER_SET_NAME, DATA_TYPE, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH
			from information_schema.columns
			where TABLE_SCHEMA=x'".$_DB."'
			AND table_name = x'".$_TB."';", __LINE__);

		while($row = $result[1]->fetch_assoc()){

			$type[$row["COLUMN_NAME"]]["DATA_TYPE"] = $row["DATA_TYPE"];
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

				if(isset($text_script_temp[$i+1]) && ($text_script_temp[$i+1] == "*")){

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
			$this->_LOG["MESSAGE"][] = "Error in your SQL syntax near<br>".htmlentities($string);
		}

		$this->use_db($use);

		foreach($list_script as $script)
		{
			if((trim($script) != "") && (trim($script) != ";")){

				$this->sqls_eval($script, $use);
			}
		}
	}


	private function sqls_eval($script, $use)
	{
		$result = $this->request($script, __LINE__);

		if($result[0]){

			$ST = "<br><b>"._MESSAGE_EXECUTED."</b><br><br>".
				preg_replace("/".PHP_EOL."/", "", htmlentities($script))."<br>";

			if($result[1]){

				while($row = $result[1]->fetch_assoc())
				{
					$ST .= "<br>";
					foreach($row as $k=>$v){

						$ST .= htmlentities($k).": ".htmlentities($v, ENT_SUBSTITUTE)."<br>";
					}
				}
			}

			$this->_LOG["RESULT"][] = $ST;
		}
	}


}
