<?php

/*
Copyright (c) 2018-2023 Andrey Lyskov
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


	public function sh($nv, $LIMIT)
	{
		$RT = [];
		$RT["SH"] = [];
		$RT["COUNT"] = "";
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["SCHEMA"];

		$RT["FIELD_ST"] = ["SCHEMA_NAME","DEFAULT_COLLATION_NAME"];

		$RT["FIELD_ST_NAV"] = ["SCHEMA_NAME", "DEFAULT_COLLATION_NAME"];

		$RT["FIELD_SE"] = ["DEFAULT_COLLATION_NAME", "DEFAULT_CHARACTER_SET_NAME"];

		$RT["ACS"] = [
			'_DELETE_SH'=>_ACTION_DELETE,
			'_CLEAR_SH'=>_ACTION_CLEAR,
			'_EXPORT_SQL_SH'=>_ACTION_EXPORT_SQL];

		$RT["FILTER_EX"] = [];

		if($nv["page_sh"] === "0"){$nv["page_sh"] = $RT["ON_PAGE"][0];}

		foreach($RT["FIELD_ST_NAV"] as $v){

			$RT["FILTER_EX"][$v] = $this->get_fv($v, [], "sh");
		}

		$WHERE = $this->get_wr($nv, [], "sh");

		if($WHERE !== ""){$WHERE = " WHERE ".$WHERE;}

		$result = $this->request("SELECT COUNT(*) FROM information_schema.SCHEMATA ".$WHERE.";",
			"", [], __LINE__);

		if($result[0]){

			$RT["COUNT"] = $this->fetch_row($result[1])[0];

			if($RT["COUNT"] <= $nv["from_sh"]){$nv["from_sh"] = "0";}
		}

		$result = $this->request("SELECT
			s.SCHEMA_NAME, s.DEFAULT_CHARACTER_SET_NAME, s.DEFAULT_COLLATION_NAME,
			(SELECT COUNT(*) FROM information_schema.TABLES WHERE `TABLE_SCHEMA`=s.SCHEMA_NAME) as count
			FROM information_schema.SCHEMATA s ".$WHERE." ORDER BY ".
			($RT["FIELD_ST"][$nv["order_sh"]])." ".$nv["order_desc_sh"].
			" LIMIT ".$nv["from_sh"].", ".$nv["page_sh"].";", "", [], __LINE__);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT["SH"][$row["SCHEMA_NAME"]] = $row;
			}
		}

		$count_page = 0;
		do{

			$RT["FROM"][] = $count_page;
			$count_page = $count_page + $nv["page_sh"];
		}
		while($count_page < $RT["COUNT"]);

		return $RT;
	}

	public function tb($_SH, $nv, $cl_sl, $LIMIT)
	{
		$_SHS = $this->h2s($_SH);

		$RT = [];

		$RT["SH"] = $_SHS;
		$RT["CREATE"] = [];

		$RT["SU"] = [];
		$RT["SU"]["LIST"] = [];
		$RT["SU"]["SUB"]["ID"] = "";
		$RT["SU"]["SUB"]["NM"] = "";
		$RT["SU"]["SUB"]["SL"] = "";
		$RT["SU"]["SUB"]["PR"] = "";

		$RT["TABLES"] = [];
		$RT["COUNT"] = "";
		$RT["FROM"] = [];
		$RT["ON_PAGE"] = $LIMIT["TABLES"];

		$RT["FIELD_ST"] = [
			"table_name","create_time","update_time","engine","table_collation","table_type","row_format"];

		$RT["FIELD_ST_NAV"] = [
			"table_name","create_time","update_time","engine","table_collation","table_type"];

		$RT["FIELD_SE"] = ["CREATE_TIME", "UPDATE_TIME", "ENGINE", "TABLE_COLLATION",
			"TABLE_TYPE", "ROW_FORMAT", "TABLE_COMMENT"];

		$RT["ACS"] = [
			'_DELETE_TB'=>_ACTION_DELETE,
			'_CLEAR_TB'=>_ACTION_CLEAR,
			'_EXPORT_SQL_TB'=>_ACTION_EXPORT_SQL];

		$RT["FILTER_EX"] = [];

		$RT["PRIVILEGES"] = [];

		$RT["SQL"] = [];

		$OPEN_TABLES = [];

		$SUB_PR = [];
		$SUB_PR_SET = [];
		$SUB_PR_OUT = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_SHS`", "", [], __LINE__);

		if($CREATE[0])
		{
			$RT["CREATE"]["SH"] = $this->fetch_row($CREATE[1])[1];

			$RT["SQL"] = [
				"-- SCHEMA" => "",
				"CREATE" => $RT["CREATE"]["SH"],
				"ALTER CHARACTER SET" => "ALTER DATABASE `".$_SHS."` CHARACTER SET utf8 DEFAULT COLLATE utf8mb4_bin;",
				"ALTER ENCRYPTION" => "ALTER DATABASE `".$_SHS."` ENCRYPTION 'N';\n\n",
				"ALTER READ ONLY" => "ALTER DATABASE `".$_SHS."` READ ONLY = 0;"
			];

			$RT["SU"]["LIST"]["events"] = $this->get_sub($_SH, $_SHS,
				"EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", "");

			$RT["SU"]["LIST"]["triggers"] = $this->get_sub($_SH, $_SHS,
				"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER", "SQL Original Statement", "");

			$RT["SU"]["LIST"]["procedures"] = $this->get_sub($_SH, $_SHS,
				"ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE", "Create Procedure", "ROUTINE_TYPE='PROCEDURE' AND");

			$RT["SU"]["LIST"]["functions"] = $this->get_sub($_SH, $_SHS,
				"ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION", "Create Function", "ROUTINE_TYPE='FUNCTION' AND");

			foreach($RT["SU"]["LIST"]["events"] as $k=>$v)
			{
				$RT["SQL"]["-- EVENTS"] = "";
				$RT["SQL"][$k." "] = "DROP EVENT IF EXISTS `".$k."`;\n\n".$v.";";
			}

			foreach($RT["SU"]["LIST"]["triggers"] as $k=>$v)
			{
				$RT["SQL"]["-- TRIGGERS"] = "";
				$RT["SQL"][$k." "] = "DROP TRIGGER IF EXISTS `".$k."`;\n\n".$v.";";
			}

			foreach($RT["SU"]["LIST"]["procedures"] as $k=>$v)
			{
				$RT["SQL"]["-- PROCEDURES"] = "";
				$RT["SQL"][$k." "] = "DROP PROCEDURE IF EXISTS `".$k."`;\n\n".$v.";";

				$SUB_PR = [];
				$SUB_PR_OUT = [];
				$SUB_PR_SET = [];

				$result = $this->request("SELECT PARAMETER_MODE, PARAMETER_NAME
					FROM information_schema.PARAMETERS where SPECIFIC_SCHEMA=x'".$_SH."'
					AND SPECIFIC_NAME='".$k."' AND ROUTINE_TYPE='PROCEDURE'
					AND PARAMETER_MODE<>'';", "", [], __LINE__);

				if($result[0])
				{
					while( $row = $this->fetch_assoc($result[1]) )
					{
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

				$RT["SQL"][$k." "] .=
					"\n\n".implode(" ",$SUB_PR_SET)."CALL ".$k."(".implode(", ",$SUB_PR).");";

				if(count($SUB_PR_OUT) !== 0){

					$RT["SQL"][$k." "] .= "\nSELECT ".implode(", ",$SUB_PR_OUT).";";
				}

			}

			foreach($RT["SU"]["LIST"]["functions"] as $k=>$v)
			{
				$RT["SQL"]["-- FUNCTIONS"] = "";
				$RT["SQL"][$k." "] = "DROP FUNCTION IF EXISTS `".$k."`;\n\n".$v.";";

				$SUB_PR = [];

				$result = $this->request("SELECT PARAMETER_MODE, PARAMETER_NAME
					FROM information_schema.PARAMETERS where SPECIFIC_SCHEMA=x'".$_SH."'
					AND SPECIFIC_NAME='".$k."'
					AND PARAMETER_MODE<>'';", "", [], __LINE__);

				if($result[0]){

					while( $row = $this->fetch_assoc($result[1]) ){

						$SUB_PR[] = $row["PARAMETER_NAME"];
					}
				}

				$RT["SQL"][$k." "] .= "\n\nSELECT ".$k."(".implode(", ",$SUB_PR).");";
			}

			$result = $this->request("SHOW OPEN TABLES FROM `$_SHS` WHERE In_use>0;", "", [], __LINE__);

			while( $row = $this->fetch_assoc($result[1]) ){ $OPEN_TABLES[] = $row["Table"];}

			if($nv["page_tb"] === "0"){$nv["page_tb"] = $RT["ON_PAGE"][0];}

			foreach($RT["FIELD_ST_NAV"] as $v){

				$RT["FILTER_EX"][$v] = $this->get_fv($v, [], "tb");
			}

			$WHERE = $this->get_wr($nv, [], "tb");

			if($WHERE !== ""){$WHERE = " AND (".$WHERE.") ";}

			$result = $this->request("SELECT COUNT(*)
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_SH."' ".$WHERE." ;",
				"", [], __LINE__);

			if($result[0]){

				$RT["COUNT"] = $this->fetch_row($result[1])[0];

				if($RT["COUNT"] <= $nv["from_tb"]){$nv["from_tb"] = "0";}
			}

			$result = $this->request("SELECT
				TABLE_NAME, CREATE_TIME, UPDATE_TIME, ENGINE, TABLE_COLLATION,
				TABLE_TYPE, ROW_FORMAT, TABLE_COMMENT
				FROM information_schema.TABLES where TABLE_SCHEMA=x'".$_SH."' ".$WHERE." ORDER BY ".
				($RT["FIELD_ST"][$nv["order_tb"]])." ".$nv["order_desc_tb"].
				" LIMIT ".$nv["from_tb"].", ".$nv["page_tb"].";",
				"", [], __LINE__);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) )
				{
					$RT["TABLES"][$row["TABLE_NAME"]] = $row;
					$RT["TABLES"][$row["TABLE_NAME"]]["count"] = "";

					if(in_array($row["TABLE_NAME"], $OPEN_TABLES)){

						$RT["TABLES"][$row["TABLE_NAME"]]["count"] = "IN USE";
					}
					else{

						$lines = $this->request("SELECT COUNT(*) FROM `$_SHS`.`".$row["TABLE_NAME"]."`;"
						, "", [], __LINE__);

						if($lines[0]){

							$RT["TABLES"][$row["TABLE_NAME"]]["count"] = $this->fetch_row($lines[1])[0];
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


	public function rc($_SH, $_TB, $nv, $LIMIT, $mode)
	{
		$_SHS = $this->h2s($_SH);
		$_TBS = $this->set_name($this->h2s($_TB));

		$RT = [];
		$RT["SH"] = $_SHS;
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
		$RT["SH_LIST"] = [];

		$RT["SQL_ADD"]["DROP"] = [];

		$C_L = [];
		$C_F = [];

		$LIST = [];
		$LIST_KEY = [];

		$CREATE = $this->request("SHOW CREATE DATABASE `$_SHS`", "", [], __LINE__, false);

		$RT["CREATE"]["SH"] = "";
		if($CREATE[0]){

			$RT["CREATE"]["SH"] = $this->fetch_row($CREATE[1])[1];
		}

		$CREATE = $this->request("SHOW CREATE TABLE `$_SHS`.`$_TBS`;", "", [], __LINE__, false);

		$RT["CREATE"]["TB"] = "";

		$prc = 0;
		$fpr = 0;
		$str = "";

		if($CREATE[0])
		{
			$RT["CREATE"]["TB"] = $this->fetch_row($CREATE[1])[1];

			$create_tb = explode("\n", $RT["CREATE"]["TB"]);

			foreach($create_tb as $v)
			{
				if(preg_match("/^\) ENGINE=/", $v) || preg_match("/^\) \/\*\!/", $v) || ($fpr === 1)){

					$fpr = 1;
					$str .= $v."\n";

					if(preg_match("/^\/\*\![0-9]{5} PARTITION /",$v)){$prc = 1;}
				}
			}

			$result = $this->request("SELECT `TABLE_TYPE`, `ENGINE` FROM information_schema.TABLES where
				TABLE_SCHEMA=x'".$_SH."' AND TABLE_NAME=x'".$_TB."';", "", [], __LINE__, false);

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT["TABLE_TYPE"] = $row["TABLE_TYPE"];
				$RT["ENGINE"] = $row["ENGINE"];
			}

			if($mode === "")
			{
				$result = $this->request("SELECT
					tc.CONSTRAINT_NAME, tc.CONSTRAINT_TYPE,
					kc.COLUMN_NAME, kc.REFERENCED_TABLE_SCHEMA, kc.REFERENCED_TABLE_NAME, kc.REFERENCED_COLUMN_NAME
					FROM information_schema.TABLE_CONSTRAINTS tc
					LEFT JOIN information_schema.KEY_COLUMN_USAGE kc
					ON tc.TABLE_SCHEMA = kc.TABLE_SCHEMA
					AND tc.TABLE_NAME = kc.TABLE_NAME
					AND tc.CONSTRAINT_NAME = kc.CONSTRAINT_NAME
					WHERE tc.TABLE_SCHEMA = x'".$_SH."' AND tc.TABLE_NAME = x'".$_TB."';", "", [], __LINE__, false);

				while( $row = $this->fetch_assoc($result[1]) )
				{
					if($row["CONSTRAINT_TYPE"] !== NULL){

						$C_L[$row["COLUMN_NAME"]][] = $row["CONSTRAINT_TYPE"];
					}

					if(trim($row["REFERENCED_COLUMN_NAME"]) !== ""){

						$C_F[$row["COLUMN_NAME"]][] = $row;
					}

					if(trim($row["CONSTRAINT_TYPE"]) === "CHECK"){

						$RT["SQL_ADD"]["DROP"]["DROP CHECK ".$row["CONSTRAINT_NAME"]] =
							"ALTER TABLE `".$_TBS."` "."DROP CHECK `".$row["CONSTRAINT_NAME"]."`;";
					}
					elseif(trim($row["CONSTRAINT_TYPE"]) === "FOREIGN KEY"){

						$RT["SQL_ADD"]["FOREIGN KEY"][] = $row;

						$RT["SQL_ADD"]["DROP"]["DROP FOREIGN KEY ".$row["CONSTRAINT_NAME"]] =
							"ALTER TABLE `".$_TBS."` "."DROP FOREIGN KEY `".$row["CONSTRAINT_NAME"]."`;";
					}

				}
			}

			$result = $this->request("select COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_KEY,
				COLUMN_DEFAULT, IS_NULLABLE, EXTRA, NUMERIC_PRECISION
				from information_schema.columns where TABLE_SCHEMA=x'".$_SH."'
				AND table_name = x'".$_TB."' ORDER BY ORDINAL_POSITION;", "", [], __LINE__, false);

			if($result[0])
			{
				while($row = $this->fetch_assoc($result[1]))
				{
					$RT["FIELDS"][$row["COLUMN_NAME"]] = $row;

					if($mode === "")
					{
						if(!in_array( $row["DATA_TYPE"], $this->ext["blob"]) &&
							!in_array( $row["DATA_TYPE"], $this->ext["binary"])){

							$RT["FIELD_ST_NAV"][] = $row["COLUMN_NAME"];
						}

						$RT["FIELD_ST"][] = $row["COLUMN_NAME"];

						if(in_array( $row["DATA_TYPE"], $this->ext["binary"])  &&
							($RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] != ""))
						{
							if($this->server_info[0] > 5)
							{
								$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] =
									preg_replace("/^0x/", "", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"]);
							}
							else{

								$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] =
									$this->s2h($RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_DEFAULT"]);
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
								$vf["REFERENCED_TABLE_NAME"]."`;", "", [], __LINE__, false);

							if($constraint_value[0]){

								while($row_constraint_value = $this->fetch_row($constraint_value[1])){

									$RT["FIELDS"][$row['COLUMN_NAME']]["COLUMN_VALUE"][] = $row_constraint_value[0];
								}
							}

							$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = true;
						}
					}

					if($row["COLUMN_KEY"] === "PRI"){

						$RT["PRI"] = true;

						$LIST_KEY[] = $row['COLUMN_NAME'];
					}

					if($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "bit"){

						$LIST[] = "LPAD(BIN(`".$row["COLUMN_NAME"]."`), ".
							$RT["FIELDS"][$row["COLUMN_NAME"]]["NUMERIC_PRECISION"].", '0') AS `".
							$row["COLUMN_NAME"]."`";
					}
					elseif(in_array( $row["DATA_TYPE"], $this->ext["blob"]) ||
						in_array( $row["DATA_TYPE"], $this->ext["binary"])){

						$LIST[] = "HEX(`".$row["COLUMN_NAME"]."`) AS `".$row["COLUMN_NAME"]."`";
					}
					elseif($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "float"){

						$LIST[] = "(SIGN(`".$row["COLUMN_NAME"]."`) *  abs(`".$row["COLUMN_NAME"]."`)) AS `".
							$row["COLUMN_NAME"]."`";
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

			$result = $this->request("SELECT COUNT(*) FROM `$_SHS`.`$_TBS` ".$WHERE." ;",
				"", [], __LINE__, false);

			if($result[0]){

				$RT["COUNT"] = $this->fetch_row($result[1])[0];

				if($RT["COUNT"] <= $nv["from_rc"]){$nv["from_rc"] = "0";}
			}

			$LIMIT = " LIMIT ".$nv["from_rc"].", ".$nv["page_rc"];

			if($mode !== ""){$LIMIT = "";}

			$ORDER_LIST	= array_keys($LIST);
			unset($ORDER_LIST[0]);

			if(count($ORDER_LIST) === 0){$order_list_st = "1";}
			else{

				$order_list_st = ($nv["order_rc"]+1)." ".$nv["order_desc_rc"].",".
				implode(" ".$nv["order_desc_rc"].", ", $ORDER_LIST);
			}

			$result = $this->request("SELECT ".implode(", ",  $LIST).
				" FROM `".$_SHS."`.`".$_TBS."` ".$WHERE." ORDER BY ".$order_list_st.$LIMIT.";",
				"", [], __LINE__, false);

			if($result[0])
			{
				while($res = $this->fetch_assoc($result[1])){

					$RT["RECORDS"][] = $res;
				}
			}
			elseif(($result[1] === 1038) && (count($LIST_KEY)) !== 0)
			{
				$result = $this->request("SELECT ".implode(", ",  $LIST_KEY).
					" FROM `".$_SHS."`.`".$_TBS."` ".$WHERE." ORDER BY ".$order_list_st[0].$LIMIT.";",
					"", [], __LINE__, false);

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

		if($mode !== ""){ $this->_LOG["MESSAGE"] = []; }

		$RT["SQL"] = [];

		if($mode === "")
		{
			$result = $this->request("SELECT CURRENT_USER();", "", [], __LINE__, false);
			if($result[0]){

				$user = $this->fetch_row($result[1])[0];

				$_user = explode("@", $user);
			}

			$result = $this->request("SELECT PRIVILEGE_TYPE, COLUMN_NAME
				FROM information_schema.COLUMN_PRIVILEGES
				WHERE GRANTEE = '\'".$_user[0]."\'@\'".$_user[1]."\''
				AND TABLE_SCHEMA=x'".$_SH."' AND TABLE_NAME=x'".$_TB."';", "", [], __LINE__, false);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) ){

					$RT["PRIVILEGES"]["COLUMN_PRIVILEGES"][$row["PRIVILEGE_TYPE"]][] = $row["COLUMN_NAME"];
				}
			}

			$result = $this->request("SELECT PRIVILEGE_TYPE
				FROM information_schema.TABLE_PRIVILEGES
				WHERE GRANTEE = '\'".$_user[0]."\'@\'".$_user[1]."\''
				AND TABLE_SCHEMA=x'".$_SH."' AND TABLE_NAME=x'".$_TB."';", "", [], __LINE__, false);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) ){

					$RT["PRIVILEGES"]["TABLE_PRIVILEGES"][] = $row["PRIVILEGE_TYPE"];
				}
			}

			if(($RT["ENGINE"] === "MRG_MyISAM") || ($RT["ENGINE"] === "MRG_MYISAM"))
			{
				$SCA["-- TABLE"] = "";

				$SCA["CREATE"] = "USE `".$_SHS."`;\n\n".
					$RT["CREATE"]["TB"].";";

				$SCA["CREATE LIKE"] = "USE `".$_SHS."`;\n\n".
					"CREATE TABLE `".$_TBS."` LIKE `".$_SHS."`.`".$_TBS."`;";
			}
			elseif($RT["TABLE_TYPE"] === "VIEW")
			{
				$SCA["-- VIEW"] = "";

				$SCA["CREATE"] = $RT["CREATE"]["TB"].";\n\n";
			}
			else
			{
				$SCA["-- TABLE"] = "";

				$SCA["CREATE"] = "USE `".$_SHS."`;\n\n".
					"SET FOREIGN_KEY_CHECKS=0;\n\n".
					$RT["CREATE"]["TB"].";\n\n".
					"INSERT INTO `".$_SHS."`.`".$_TBS."` SELECT * FROM `".$_SHS."`.`".$_TBS."`;\n\n".
					"SET FOREIGN_KEY_CHECKS=1;";

				$FKC = "";
				$count = 1;

				if(isset($RT["SQL_ADD"]["FOREIGN KEY"]))
				{
					foreach($RT["SQL_ADD"]["FOREIGN KEY"] as $fk)
					{
						$referent = $this->request("SELECT UPDATE_RULE, DELETE_RULE
							FROM information_schema.REFERENTIAL_CONSTRAINTS
							WHERE CONSTRAINT_SCHEMA = x'".$_SH."'
							AND TABLE_NAME = '".$_TBS."'
							AND CONSTRAINT_NAME='".$fk["CONSTRAINT_NAME"]."';",
							"", [], __LINE__, false);

						$row_referent = $this->fetch_assoc($referent[1]);

						$FKC .= "ALTER TABLE `".$_TBS."` ADD CONSTRAINT `".
							$count."fk".microtime()."` FOREIGN KEY (`".
							$fk["COLUMN_NAME"]."`) REFERENCES `".
							$fk["REFERENCED_TABLE_SCHEMA"]."`.`".
							$fk["REFERENCED_TABLE_NAME"]."` (`".
							$fk["REFERENCED_COLUMN_NAME"]."`) ON UPDATE ".
							$row_referent["UPDATE_RULE"]." ON DELETE ".$row_referent["DELETE_RULE"].";\n\n";

						$count += 1;
					}

					$SCA["CREATE LIKE"] = "USE `".$_SHS."`;\n\n".
						"CREATE TABLE `".$_TBS."` LIKE `".$_SHS."`.`".$_TBS."`;\n\n".
						"INSERT INTO `".$_TBS."` SELECT * FROM `".$_SHS."`.`".$_TBS."`;\n\n".
						$FKC."";
				}
			}

			if($prc === 1){

				$SCA["REMOVE PARTITIONING"] = "ALTER TABLE `".$_TBS."` "."REMOVE PARTITIONING;";
			}

			if($str !== ""){

				$SCA["ALTER TABLE"] = "ALTER TABLE `".$_TBS."` ".substr($str, 2);
			}

			if(($RT["TABLE_TYPE"] === "BASE TABLE") &&
				(($RT["ENGINE"] !== "MRG_MyISAM") && ($RT["ENGINE"] !== "MRG_MYISAM")))
			{
				$SCA["ANALYZE"] = "ANALYZE TABLE `".$_TBS."`;";
				$SCA["CHECK"] = "CHECK TABLE `".$_TBS."`;";
				$SCA["OPTIMIZE"] = "OPTIMIZE TABLE `".$_TBS."`;";
				$SCA["REPAIR"] = "REPAIR TABLE `".$_TBS."`;";
				$SCA["ADD PRIMARY KEY"] = "ALTER TABLE `".$_TBS."` "."ADD PRIMARY KEY (`...`);";
				$SCA["ADD CONSTRAINT FOREIGN"] =
					"ALTER TABLE `".$_TBS."` "."ADD CONSTRAINT `...` \nFOREIGN KEY (`...`) \nREFERENCES `...` (`...`);";
				$SCA["ADD UNIQUE"] = "ALTER TABLE `".$_TBS."` "."ADD UNIQUE (`...`);";
				$SCA["ADD INDEX"] = "ALTER TABLE `".$_TBS."` "."ADD INDEX (`...`);";
				$SCA["ADD SPATIAL INDEX"] = "ALTER TABLE `".$_TBS."` "."ADD SPATIAL INDEX(`...`);";
				$SCA["ADD FULLTEXT INDEX"] = "ALTER TABLE `".$_TBS."` "."ADD FULLTEXT INDEX(`...`);";

				if($this->server_info[0] > 5){

					$SCA["ADD CHECK"] = "ALTER TABLE `".$_TBS."` "."ADD CHECK (...);";
				}

				$result = $this->request("SELECT INDEX_NAME
					FROM information_schema.STATISTICS
					WHERE TABLE_SCHEMA = x'".$_SH."' AND TABLE_NAME = x'".$_TB."';", "", [], __LINE__, false);

				while( $row = $this->fetch_row($result[1]) )
				{
					if(trim($row[0]) !== "PRIMARY"){

						$SCA["DROP INDEX ".$row[0]] = "ALTER TABLE `".$_TBS."` DROP INDEX `".$row[0]."`;";
					}
					else{

						$RT["SQL_ADD"]["DROP"]["DROP PRIMARY KEY"] = "ALTER TABLE `".$_TBS."` DROP PRIMARY KEY;";
					}
				}
			}

			$COLUMN = array_keys($RT["FIELDS"]);

			$SCA_ADD = [];
			$SCA_CHANGE = [];
			$SCA_DROP = [];

			if(($RT["TABLE_TYPE"] === "BASE TABLE") &&
				(($RT["ENGINE"] !== "MRG_MyISAM") && ($RT["ENGINE"] !== "MRG_MYISAM")))
			{
				$SCA_ADD["-- COLUMN"] = "";
				$SCA_ADD["ADD COLUMN FIRST"] = "ALTER TABLE `".$_TBS."` "."ADD\n... \nFIRST;";

				foreach($COLUMN as $VCOLUMN)
				{
					$SCA_ADD["ADD COLUMN AFTER ".$VCOLUMN.""] =
						"ALTER TABLE `".$_TBS."` "."ADD\n... \nAFTER `".$VCOLUMN."`;";

					$SCA_CHANGE["CHANGE COLUMN ".$VCOLUMN.""] =
						"ALTER TABLE `".$_TBS."` "."CHANGE COLUMN `".$VCOLUMN."` \n...\n;";

					$SCA_DROP["DROP COLUMN ".$VCOLUMN.""] =
						"ALTER TABLE `".$_TBS."` "."DROP COLUMN `".$VCOLUMN."`;";
				}
			}

			$RT["SQL"] = array_merge($SCA, $RT["SQL_ADD"]["DROP"], $SCA_ADD, $SCA_CHANGE, $SCA_DROP);
		}

		return $RT;
	}


	public function get_list_sh()
	{
		$RT = [];

		$result = $this->request("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA;",
			"", [], __LINE__);

		if($result[0]){

			while($row = $this->fetch_row($result[1])){

				$RT[] = $this->s2h($row[0]);
			}
		}

		return $RT;
	}

	public function get_list_tb($_SH)
	{
		$RT = [];

		$_SHS = $this->h2s($_SH);

		$result = $this->request("SHOW TABLES FROM `$_SHS`;", "", [], __LINE__);

		if($result[0]){

			while($row = $this->fetch_row($result[1])){

				$RT[] = $this->s2h($row[0]);
			}
		}
		return $RT;
	}

	public function searching($lsh, $ltb, $find, $mode)
	{
		$count = 0;

		$find = trim($find);

		$this->_LOG["RESULT"][] = "<br>"._MESSAGE_SEARCHING.": ".$this->html($find)."<br>";

		foreach($lsh as $_SH)
		{
			$_SHS = $this->h2s($_SH);

			if(count($ltb) === 0){

				$list_tb = $this->get_list_tb($_SH);
			}
			else{

				$list_tb = $ltb;
			}

			foreach($list_tb as $val)
			{
				$field = [];

				$result = $this->request("select COLUMN_NAME, DATA_TYPE, NUMERIC_PRECISION
					from information_schema.columns where TABLE_SCHEMA=x'".$_SH."'
					AND table_name = x'".$val."' ORDER BY ORDINAL_POSITION;", "", [], __LINE__);

				if($result[0]){

					while($row = $this->fetch_assoc($result[1])){

						if($row["DATA_TYPE"] === "float"){

							$field[] = "(SIGN(`".$row["COLUMN_NAME"]."`) * abs(`".$row["COLUMN_NAME"]."`))";
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

				$result = $this->request("SELECT ".$tc." FROM `$_SHS`.`$valS`;", "", [], __LINE__);

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

									$this->_LOG["RESULT"][] = $this->html($_SHS).".".$this->html($valS).
										":<br>[ ".$this->html($k)." ] - ".$this->html($v);

									$count += 1;
								}
							}
							elseif($mode === "1")
							{
								if($str === trim($find)){

									$this->_LOG["RESULT"][] = $this->html($_SHS).".".$this->html($valS).
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


	public function export($_SH, $list_tb, $nv, $mode, $type)
	{
		$RT = [];

		foreach($list_tb as $val)
		{
			$RT[] = $this->rc( $_SH, $val, $nv, ["RECORDS"=>["1"]], $mode );
		}

		return $RT;
	}


	public function export_sql($list_sh, $list_tb, $nv, $mode)
	{
		$filename = date("d-m-Y").".sql";

		$crt_view_temp = "";
		$crt_view = "";
		$sT = PHP_EOL." -- Server info: ".$this->server_info.PHP_EOL.PHP_EOL;

		$sT .= PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';";

		$sT .= PHP_EOL."SET FOREIGN_KEY_CHECKS=0;".PHP_EOL;

		foreach($list_sh as $value)
		{
			$_SHS = $this->h2s($value);

			if($mode === "SH"){

				$CREATE = $this->request("SHOW CREATE DATABASE `$_SHS`", "", [], __LINE__);

				if($CREATE[0]){

					$sT .= PHP_EOL.$this->fetch_row($CREATE[1])[1].";";

					$sT .= PHP_EOL."USE `$_SHS`;";
				}

				$RT = $this->export($value, $this->get_list_tb($value), $nv, $mode, "sql");
			}
			else{

				$RT = $this->export($value, $list_tb, $nv, $mode, "sql");
			}

			foreach($RT as $v)
			{
				$row = [];

				if($v["TABLE_TYPE"] !== "VIEW")
				{
					if($mode !== "RC"){

						$sT .= PHP_EOL.PHP_EOL.$v["CREATE"]["TB"].";".PHP_EOL;
					}

					if(($v["ENGINE"] !== "MRG_MyISAM") && ($v["ENGINE"] !== "MRG_MYISAM"))
					{
						foreach($v["RECORDS"] as $vr)
						{
							$vrex = [];

							foreach($vr as $kf=>$vf)
							{
								if((count($nv["field_rc"]) === 0) || (in_array($this->s2h($kf), $nv["field_rc"])))
								{

									if(($v["FIELDS"][$kf]["EXTRA"] === "VIRTUAL GENERATED") ||
										($v["FIELDS"][$kf]["EXTRA"] === "STORED GENERATED")){
									}
									elseif(in_array($v["FIELDS"][$kf]["DATA_TYPE"], $this->ext["geo"])){

										if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

											$vrex[$kf] = "NULL";
										}
										else{

											$vrex[$kf] = "ST_GeomFromText('".$vf."')";
										}
									}
									elseif(in_array($v["FIELDS"][$kf]["DATA_TYPE"], $this->ext["blob"]) ||
										in_array($v["FIELDS"][$kf]["DATA_TYPE"], $this->ext["binary"])){

										if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

											$vrex[$kf] = "NULL";
										}
										else{

											$vrex[$kf] = "x'".$vf."'";
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
							}

							$row[] = "(".implode(",", $vrex).")";
						}

						if(count($row) !== 0){

							$sT .= PHP_EOL."insert into `".
								$v["TB"]."` (`".implode("`,`", array_keys($vrex))."`) values".
								PHP_EOL.implode(",".PHP_EOL, $row).";".PHP_EOL;
						}
					}
				}
				else
				{
						if($mode === "SH"){

							$crt_view_temp .= PHP_EOL."USE `".$_SHS."`;".PHP_EOL;
							$crt_view .= PHP_EOL."USE `".$_SHS."`;".PHP_EOL;
						}

						$result = $this->request("SELECT COLUMN_NAME FROM information_schema.COLUMNS
							WHERE TABLE_SCHEMA='".
							addslashes($_SHS)."' AND TABLE_NAME='".
							addslashes($v["TB"])."';",
							"", [], __LINE__);

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

							$this->request("USE `".$_SHS."`;", "", [], __LINE__);

							$listview = $this->request("SHOW CREATE TABLE `".$_SHS."`.`".$v["TB"]."`;",
								"", [], __LINE__);

							if($listview[0]){

								$CT = $this->fetch_row($listview[1])[1];

								$crt_view .= PHP_EOL.$CT.";".PHP_EOL;
							}
						}
				}
			}

			if($mode === "SH")
			{
				$this->request("USE `".$_SHS."`;", "", [], __LINE__);

				$triggers = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS,
					"TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER",
					"SQL Original Statement", "")).";";

				if($triggers !== ";"){

					$sT .= PHP_EOL."USE `".$_SHS."`;".PHP_EOL;
					$sT .= PHP_EOL."/* TRIGGER */".PHP_EOL;
					$sT .= PHP_EOL.$triggers.PHP_EOL;
				}

				$procedures = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS,
					"ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE",
					"Create Procedure", "ROUTINE_TYPE='PROCEDURE' AND")).";";

				if($procedures !== ";"){

					$sT .= PHP_EOL."USE `".$_SHS."`;".PHP_EOL;
					$sT .= PHP_EOL."/* PROCEDURES */".PHP_EOL;
					$sT .= PHP_EOL.$procedures.PHP_EOL;
				}

				$functions = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS,
					"ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION",
					"Create Function", "ROUTINE_TYPE='FUNCTION' AND")).";";

				if($functions !== ";"){

					$sT .= PHP_EOL."USE `".$_SHS."`;".PHP_EOL;
					$sT .= PHP_EOL."/* FUNCTIONS */".PHP_EOL;
					$sT .= PHP_EOL.$functions.PHP_EOL;
				}

				$events = implode(";".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS,
					"EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", "")).";";

				if($events !== ";"){

					$sT .= PHP_EOL."USE `".$_SHS."`;".PHP_EOL;
					$sT .= PHP_EOL."/* EVENTS */".PHP_EOL;
					$sT .= PHP_EOL.$events.PHP_EOL;
				}
			}
		}

		if($crt_view_temp !== ""){

			$sT .= PHP_EOL."/* VIEWS */".PHP_EOL;
		}

		$sT .= $crt_view_temp;
		$sT .= $crt_view;
		$sT .= PHP_EOL."SET FOREIGN_KEY_CHECKS=1;".PHP_EOL;

		$this->export_get($filename, $sT);
	}


	public function clear_sh($list_sh)
	{
		foreach($list_sh as $val){

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


	public function delete_sh($list_sh)
	{
		foreach($list_sh as $val)
		{
			$valS = $this->h2s($val);

			if(($val !== "") && (!in_array($valS, $this->DS))){

				$this->request("DROP DATABASE `$valS`;", "", [], __LINE__);
			}
		}
	}


	public function clear_tb($_SH, $list_tb)
	{
		$_SHS = $this->h2s($_SH);

		if(!in_array($_SHS, $this->DS))
		{
			if( isset($list_tb) )
			{
				foreach($list_tb as $val)
				{
					$valS = $this->h2s($val);

					if($val !== ""){

						$this->request("DELETE FROM `$_SHS`.`$valS`;", "", [], __LINE__);
					}
				}
			}
		}
	}


	public function delete_tb($_SH, $list_tb)
	{
		$_SHS = $this->h2s($_SH);

		if(!in_array($_SHS, $this->DS))
		{
			$VIEW = [];

			$result = $this->request("SELECT TABLE_NAME
				FROM information_schema.VIEWS where TABLE_SCHEMA=x'".$_SH."';", "", [], __LINE__);

			while( $row = $this->fetch_assoc($result[1]) ){ $VIEW[] = $row["TABLE_NAME"];}

			$A = [];
			$B = [];

			foreach($list_tb as $val){

				$valS = $this->h2s($val);

				if(in_array($valS, $VIEW)){	$A[] = "`".$valS."`"; }
				else{ $B[] = "`".$valS."`"; }
			}

			$this->request("USE `".$_SHS."`;", "", [], __LINE__);

			if(count($A) > 0){ $this->request("DROP VIEW ".implode(", ", $A).";", "", [], __LINE__); }

			if(count($B) > 0){ $this->request("DROP TABLE ".implode(", ", $B).";", "", [], __LINE__); }
		}
	}

	public function update_rc($_SH, $_TB, $key, $field, $file, $blob_ch, $function, $action)
	{
		$type = [];
		$this->check_field($_SH, $_TB, $type);
		$tk = array_keys($type);

		$_SHS = $this->h2s($_SH);
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
			elseif(in_array($type[$k]["DATA_TYPE"], $this->ext["binary"])){

				$sfK[] = "`".$k."`=x'".$v."' ";
			}
			elseif(in_array($k, $tk)){

				$sfK[] = "`".$k."`='".$v."' ";
			}
			else{ return; }
		}

		$this->request("USE `".$_SHS."`;", "", [], __LINE__);

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
				elseif($action === "_INSERT_RC")
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
				elseif($action === "_INSERT_RC")
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
			elseif(in_array($type[$k]["DATA_TYPE"], $this->ext["binary"]))
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
				elseif($action === "_INSERT_RC")
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
				elseif($action === "_INSERT_RC")
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
				elseif($action === "_INSERT_RC")
				{
					$sfC[] = "`".$k."`";
					$sfV[] = "NULL";
				}
			}
			else{ return; }
		}

		if($action === "_INSERT_RC")
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

		if(($_SHS === "mysql") && (($_TBS === "user") || ($_TBS === "sh") || ($_TBS === "tables_priv") || ($_TBS === "columns_priv")))
		{
			$this->request("FLUSH PRIVILEGES;", "", [], __LINE__);
		}
	}


	public function delete_rc($_SH, $_TB, $key)
	{
		$_SHS = $this->h2s($_SH);

		if(!in_array($_SHS, $this->DS))
		{
			$type = [];
			$this->check_field($_SH, $_TB, $type);

			$_SHS = $this->h2s($_SH);
			$_TBS = $this->h2s($_TB);

			$sfK = [];
			foreach($key as $kh=>$vh)
			{
				$k = $this->h2s($kh);

				$v = $this->escape($this->h2s($vh));

				if($type[$k]["DATA_TYPE"] == "bit"){

					$sfK[] = "`".$k."`=b'".$v."' ";
				}
				elseif(in_array($type[$k]["DATA_TYPE"], $this->ext["binary"])){

					$sfK[] = "`".$k."`=x'".$v."' ";
				}
				else{

					$sfK[] = "`".$k."`='".$v."' ";
				}
			}

			$this->request("USE `".$_SHS."`;", "", [], __LINE__);

			$this->request("DELETE FROM `$_TBS` WHERE ".implode(" AND ", $sfK).";", "", [], __LINE__);
		}
	}

	public function sqlsm($text_script, $use)
	{
		$use = $this->h2s($use);
		if($use !== ""){ $this->request("USE `".$use."`;", "", [], __LINE__); }

		if($text_script === ""){return;}

		$this->multi_request($text_script);

		$this->request("SET sql_mode = '".$this->sql_mode."';", "", [], __LINE__);
		$this->request("SET names '".$this->character_name."';", "", [], __LINE__);
	}

	private function check_field($_SH, $_TB, &$type)
	{
		$result = $this->request("select COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE
			from information_schema.columns
			where TABLE_SCHEMA=x'".$_SH."'
			AND table_name = x'".$_TB."';", "", [], __LINE__);

		while($row = $this->fetch_assoc($result[1])){

			$type[$row["COLUMN_NAME"]]["DATA_TYPE"] = $row["DATA_TYPE"];
			$type[$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] = $row["COLUMN_DEFAULT"];
			$type[$row["COLUMN_NAME"]]["IS_NULLABLE"] = $row["IS_NULLABLE"];
		}
	}


	private function get_sub($_SH, $_SHS, $tb, $target, $create, $searching, $add)
	{
		$RT = [];

		$result = $this->request("SELECT ".$target."_NAME
			FROM information_schema.".$tb." WHERE ".$add." ".$target."_SCHEMA=x'".$_SH."';",
			"", [], __LINE__);

		if($result[0])
		{
			while( $row = $this->fetch_assoc($result[1]) ){

				$s = $this->request($create." `$_SHS`.`".$row[$target."_NAME"]."`;", "", [], __LINE__);

				while( $row_s = $this->fetch_assoc($s[1]) ){

					$row_s[$searching] = preg_replace("/;$/", "", $row_s[$searching]);

					$RT[$row[$target."_NAME"]] = $row_s[$searching];
				}
			}
		}

		return $RT;
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

		elseif($fl === "sh"){

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

			$nvt["fl_value_".$fl] = addcslashes($nvt["fl_value_".$fl], "\\");
			$nvt["fl_value_".$fl] = "'%".addslashes($nvt["fl_value_".$fl])."%'";
		}
		elseif($nvt["fl_operator_".$fl] === "NOT LIKE %...%"){

			$nvt["fl_operator_".$fl] = "NOT LIKE";

			$nvt["fl_value_".$fl] = addcslashes($nvt["fl_value_".$fl], "\\");
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
			$WHERE .= "(SIGN(`".$nvt["fl_field_rc"]."`) * abs(`".$nvt["fl_field_rc"]."`)) ".
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
