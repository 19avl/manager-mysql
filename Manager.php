<?php

/*
Copyright (c) 2018-2026 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();


Class Manager
{
	var $dbc;

	public $_RS;

	public $connect;
	private $DS;
	private $DT;
	public $GT;

	public $character_name;
	private $sql_mode;

	public $client_info;
	public $server_info;
	public $current_user;

	private $INI;

	public function __construct(array $LIMIT)
	{
		$this->_RS = [];
		$this->connect = false;

		$this->DS = ["information_schema","mysql","performance_schema"];

		$this->DT = [];

		$this->GT = [
			"geo" => ["geometry", "point", "linestring", "polygon",
				"multipoint", "multilinestring", "multipolygon","geomcollection","geometrycollection"],
			"blob" => ["tinyblob", "blob", "mediumblob", "longblob"],
			"binary" => ["varbinary", "binary"],
			"text" => ["tinytext", "text", "mediumtext", "longtext"],
			"char" => ["varchar", "char"],
			"number" => ["int","tinyint","smallint","mediumint","bigint","double","float","decimal"],
			"bit" => ["bit"],
		];

		$this->INI = [
			"SH" => "",
			"TB" => "",
			"CREATE" => [],
			"PRI" => "",
			"TABLE_TYPE" => "",
			"ENGINE" => "",
			"FIELDS" => [],
			"DATA" => [],
			"DATA_NEW" => [],
			"FROM" => [],
			"ON_PAGE" => $LIMIT,
			"COUNT" => "",
			"FIELD_SE_ORDER" => [],
			"FIELD_SE_FILTER" => [],
			"FIELD_SE_VIEW" => [],
			"FILTER_EX" => [],
			"PRIVILEGES" => [],

			"SQL" => [],
			"ACF" => [],
			"ACS" => []
		];
	}


	public function reset_ve($nv, $LIMIT){

		$nv["page_rc"] = $LIMIT[0];
		$nv["from_rc"] = 0;
		$nv["order_rc"] = 0;

		return $nv;
	}

	public function reset_fl($nv){

		$nv["field_rc"] = [];
		$nv["fl_field_rc"] = [];
		$nv["fl_value_rc"] = [];
		$nv["fl_operator_rc"] = [];
		$nv["fl_and_rc"] = [];
		$nv["fl_count_rc"] = _WHERE_CN_DEF;

		return $nv;
	}

	public function reset_nv($nv, $LIMIT){

		if(!$this->ex_sh($nv["_SH"])){

			$nv = $this->reset_ve($this->reset_fl($nv), $LIMIT);
			$nv["_SH"] = "";
			$nv["_TB"] = "";
		}

		if(!$this->ex_tb($nv["_SH"], $nv["_TB"])){

			$nv = $this->reset_ve($this->reset_fl($nv), $LIMIT);
			$nv["_TB"] = "";
		}

		return $nv;
	}

	public function ex_sh($_SH)
	{
		if($_SH !== "")
		{
			$result = $this->request("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA "."WHERE SCHEMA_NAME = x'".$_SH."';","", [], __LINE__, false);

			if($result[0]){

				if(!isset($this->fetch_assoc($result[1])["SCHEMA_NAME"])){

					return false;
				}
			}
		}

		return true;
	}

	public function ex_tb($_SH, $_TB)
	{
		if($_TB !== "")
		{
			$result = $this->request("SELECT TABLE_NAME FROM information_schema.TABLES "."WHERE TABLE_SCHEMA = x'".$_SH."' AND TABLE_NAME = x'".$_TB."';",
				"", [], __LINE__, false);

			if($result[0]){

				if(!isset($this->fetch_assoc($result[1])["TABLE_NAME"])){

					return false;
				}
			}
		}

		return true;
	}

	public function sqlsm($text_script, $use)
	{
		if($text_script === ""){return;}

		$use = $this->esN(hex2bin((string)$use));

		if($use !== ""){ $this->request("USE ".$use.";", "", [], __LINE__); }

		$this->multi_request($text_script);

		$this->request("SET SQL_MODE = '".$this->sql_mode."';", "", [], __LINE__);
		$this->request("SET NAMES '".$this->character_name."';", "", [], __LINE__);
	}


	public function sh(array $nv)
	{
		$RT = $this->INI;
		$RT["SCHEMA_NAME"] = "SCHEMA_NAME";

		$RT["FIELD_SE_ORDER"] = ["SCHEMA_NAME", "DEFAULT_COLLATION_NAME"];

		$RT["FIELD_SE_FILTER"] = ["SCHEMA_NAME", "DEFAULT_COLLATION_NAME", "DEFAULT_CHARACTER_SET_NAME"];

		$RT["FIELD_SE_VIEW"] = ["SCHEMA_NAME", "DEFAULT_COLLATION_NAME", "DEFAULT_CHARACTER_SET_NAME", "TABLES"];

		foreach($RT["FIELD_SE_VIEW"] as $v)
		{
			$RT["FIELDS"][$v] = [
				"COLUMN_NAME" => $v,
				"DATA_TYPE" => "",
				"COLUMN_TYPE" => "",
				"COLUMN_KEY" => "",
				"COLUMN_DEFAULT" => "",
				"IS_NULLABLE" => "",
				"EXTRA" => "",
				"CONSTRAINT" => [],
				"FOREIGN" => ""
			];
		}

		$RT["FILTER_EX"] = ["","=","<>","LIKE %...%","NOT LIKE %...%","REGEXP","NOT REGEXP"];

		$WHERE = $this->get_wr($nv, $RT["FIELDS"]);

		if($WHERE !== ""){$WHERE = " WHERE ".$WHERE;}

		$result = $this->request("SELECT COUNT(*) FROM information_schema.SCHEMATA ".$WHERE.";",
			"", [], __LINE__, false);

		if($result[0]){

			$RT["COUNT"] = $this->fetch_row($result[1])[0];

			if($RT["COUNT"] <= $nv["from_rc"]){$nv["from_rc"] = "0";}
		}

		$result = $this->request("SELECT ".implode(", s.", $RT["FIELD_SE_FILTER"]).", ".
			"(SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=CAST(s.SCHEMA_NAME AS BINARY)) as TABLES ".
			"FROM information_schema.SCHEMATA s ".$WHERE." ORDER BY ".
			($RT["FIELD_SE_ORDER"][$nv["order_rc"]])." ".$nv["order_desc_rc"].
			" LIMIT ".$nv["from_rc"].", ".$nv["page_rc"].";", "", [], __LINE__, false);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT["DATA"][] = $row;
			}
		}

		$count_page = 0;
		do{

			$RT["FROM"][] = $count_page;
			$count_page = $count_page + $nv["page_rc"];
		}
		while($count_page < $RT["COUNT"]);

		$RT["ACF"] = [
			'_DELETE_SH_FILTER'=>_ACTION_DELETE,
			'_CLEAR_SH_FILTER'=>_ACTION_CLEAR,
			''=>'-- '._ACTION_EXPORT,
			'_VIEW_SQL_SH_FILTER'=>_ACTION_VIEW_SQL,
			'_SAVE_SQL_SH_FILTER'=>_ACTION_SAVE_SQL];

		$RT["ACS"] = [
			'_DELETE_SH'=>_ACTION_DELETE,
			'_CLEAR_SH'=>_ACTION_CLEAR,
			''=>'-- '._ACTION_EXPORT,
			'_VIEW_SQL_SH'=>_ACTION_VIEW_SQL,
			'_SAVE_SQL_SH'=>_ACTION_SAVE_SQL];

		return $RT;
	}

	private function get_sub_T($_SH, $_SHS, $tb, $target, $searching, $add)
	{
		$RT = [];

		$result = $this->request("SELECT ".$target."_NAME, SQL_MODE, CHARACTER_SET_CLIENT, ".$searching." as 'DEFINITION' FROM information_schema.".$tb." ".
			"WHERE ".$add." ".$target."_SCHEMA=x'".$_SH."';",
			"", [], __LINE__, false);

		if($result[0])
		{
			while( $row = $this->fetch_assoc($result[1]) )
			{
				$RT[$row[$target."_NAME"]] = $row;
			}
		}

		return $RT;
	}

	private function get_sub($_SH, $_SHS, $tb, $target, $create, $searching, $add)
	{
		$RT = [];

		$result = $this->request("SELECT ".$target."_NAME, SQL_MODE, CHARACTER_SET_CLIENT FROM information_schema.".$tb." WHERE ".$add." ".$target."_SCHEMA=x'".$_SH."';",
			"", [], __LINE__, false);

		if($result[0])
		{
			while( $row = $this->fetch_assoc($result[1]) )
			{
				$this->request("SET NAMES '".$row["CHARACTER_SET_CLIENT"]."';", "", [], __LINE__);
				$this->request("SET sql_mode = '".$row["SQL_MODE"]."';", "", [], __LINE__,false);

				$s = $this->request($create." ".$this->esN(hex2bin($_SH), $row["SQL_MODE"]).".".$this->esN($row[$target."_NAME"], $row["SQL_MODE"]).";", "", [], __LINE__, false);

				while( $row_sub = $this->fetch_assoc($s[1]) ){

					if(!preg_match("/;$/", $row_sub[$searching])){ $row_sub[$searching] .= ";";}

					$RT[$row[$target."_NAME"]] = "SET SQL_MODE = '".$row_sub["sql_mode"]."';".PHP_EOL.
						"SET NAMES ".$row["CHARACTER_SET_CLIENT"].";".PHP_EOL.PHP_EOL.$row_sub[$searching];
				}
			}

			$this->request("SET SQL_MODE = '".$this->sql_mode."';", "", [], __LINE__,false);
			$this->request("SET NAMES '".$this->character_name."';", "", [], __LINE__);
		}

		return $RT;
	}

	private function view_sub($list, $title, $arg=[])
	{
		$_SC = [];
		$_SD = [];
		$_SE = [];

		if(count($list) !== 0)
		{
			foreach($list as $k=>$v)
			{
				$_SC["SHOW CREATE ".$k] = "SHOW CREATE ".$title." ".$this->esN($k).";\n\n";
				$_SD["DROP ".$k] =  "DROP ".$title." IF EXISTS ".$this->esN($k).";\n\n";

				if($title === "PROCEDURE")
				{
					if(isset($arg[$k])){

						$_SE["CALL ".$k] = ((trim((string)$arg[$k]["SET"]) !== "") ? $arg[$k]["SET"]."\n" : "").
							"CALL ".$k."(".$arg[$k]["ARG"].");\n".
							((trim((string)$arg[$k]["RUN"]) !== "") ? "SELECT ".$arg[$k]["RUN"].";" : "");
					}
					else{

						$_SE["CALL ".$k] = "CALL ".$k."();";
					}
				}

				if($title === "FUNCTION")
				{
					if(isset($arg[$k])){

						$_SE["CALL ".$k] = "SELECT ".$k."(".$arg[$k]["ARG"].");";
					}
					else{

						$_SE["CALL ".$k] = "SELECT ".$k."();";
					}
				}
			}
		}

		return array_merge($_SC, $_SD, $_SE);
	}

	public function tb(array $nv)
	{
		$_SHS = hex2bin($nv["_SH"]);
		$_SHS_EDQ = $this->esN($_SHS);

		$RT = $this->INI;
		$RT["SH"] = $_SHS;
		$RT["TABLE_NAME"] = "TABLE_NAME";

		$RT["SQL"]["schema"] = [];

		$RT["FIELD_SE_ORDER"] = ["TABLE_NAME", "ENGINE", "TABLE_COLLATION", "TABLE_TYPE"];

		$RT["FIELD_SE_FILTER"] = ["TABLE_NAME", "ENGINE", "TABLE_COLLATION", "TABLE_TYPE", "TABLE_COMMENT"];

		$RT["FIELD_SE_VIEW"] = ["TABLE_NAME", "ENGINE", "TABLE_COLLATION", "TABLE_TYPE", "TABLE_COMMENT"];

		foreach($RT["FIELD_SE_VIEW"] as $v)
		{
			$RT["FIELDS"][$v] = [
				"COLUMN_NAME" => $v,
				"DATA_TYPE" => "",
				"COLUMN_TYPE" => "",
				"COLUMN_KEY" => "",
				"COLUMN_DEFAULT" => "",
				"IS_NULLABLE" => "",
				"EXTRA" => "",
				"CONSTRAINT" => [],
				"FOREIGN" => ""
			];
		}

		$RT["CREATE"]["SH"] = "";

		$CREATE = $this->request("SHOW CREATE DATABASE ".$_SHS_EDQ.";", "", [], __LINE__, false);

		if(!$CREATE[0]){return $RT;}

		$RT["CREATE"]["SH"] = $this->fetch_row($CREATE[1])[1];

		$RT["FILTER_EX"] = ["","=","<>","LIKE %...%","NOT LIKE %...%","REGEXP","NOT REGEXP","IS NULL","IS NOT NULL"];

		$WHERE = $this->get_wr($nv, $RT["FIELDS"]);

		if($WHERE !== ""){$WHERE = " AND (".$WHERE.") ";}

		$result = $this->request("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=x'".$nv["_SH"]."' ".$WHERE." ;", "", [], __LINE__, false);

		if($result[0]){

			$RT["COUNT"] = $this->fetch_row($result[1])[0];

			if($RT["COUNT"] <= $nv["from_rc"]){$nv["from_rc"] = "0";}
		}

		$result = $this->request("SELECT ".implode(", ", $RT["FIELD_SE_FILTER"])." ".
			"FROM information_schema.TABLES WHERE TABLE_SCHEMA=x'".$nv["_SH"]."' ".$WHERE." ORDER BY ".
			($RT["FIELD_SE_ORDER"][$nv["order_rc"]])." ".$nv["order_desc_rc"]." LIMIT ".$nv["from_rc"].", ".$nv["page_rc"].";",
			"", [], __LINE__, false);

		if($result[0])
		{
			while( $row = $this->fetch_assoc($result[1]) )
			{
				$RT["DATA"][$row["TABLE_NAME"]] = $row;
			}
		}

		$count_page = 0;
		do{

			$RT["FROM"][] = $count_page;
			$count_page = $count_page + $nv["page_rc"];
		}
		while($count_page < $RT["COUNT"]);

		$RT["ACF"] = [
			'_DELETE_TB_FILTER'=>_ACTION_DELETE,
			'_CLEAR_TB_FILTER'=>_ACTION_CLEAR,
			''=>'-- '._ACTION_EXPORT,
			'_VIEW_SQL_TB_FILTER'=>_ACTION_VIEW_SQL,
			'_SAVE_SQL_TB_FILTER'=>_ACTION_SAVE_SQL];

		$RT["ACS"] = [
			'_DELETE_TB'=>_ACTION_DELETE,
			'_CLEAR_TB'=>_ACTION_CLEAR,
			''=>'-- '._ACTION_EXPORT,
			'_VIEW_SQL_TB'=>_ACTION_VIEW_SQL,
			'_SAVE_SQL_TB'=>_ACTION_SAVE_SQL];

		$RT["SQL"]["schema"] = [
			"CREATE" => $RT["CREATE"]["SH"].";",
			"ALTER CHARACTER SET" => "ALTER DATABASE ".$_SHS_EDQ." CHARACTER SET utf8 DEFAULT COLLATE utf8mb4_bin;",
			"ALTER ENCRYPTION" => "ALTER DATABASE ".$_SHS_EDQ." ENCRYPTION 'N';\n\n",
			"ALTER READ" => "ALTER DATABASE ".$_SHS_EDQ." READ ONLY = 0;"
		];

		$RT["SU"] = [];
		$RT["SU"]["LIST"] = [];

		$RT["SU"]["LIST"]["events"] = $this->get_sub_T($nv["_SH"], $_SHS_EDQ, "EVENTS", "EVENT", "EVENT_DEFINITION", "");
		$RT["SU"]["LIST"]["triggers"] = $this->get_sub_T($nv["_SH"], $_SHS_EDQ, "TRIGGERS", "TRIGGER", "ACTION_STATEMENT", "");
		$RT["SU"]["LIST"]["procedures"] = $this->get_sub_T($nv["_SH"], $_SHS_EDQ, "ROUTINES", "ROUTINE", "ROUTINE_DEFINITION", "ROUTINE_TYPE='PROCEDURE' AND");
		$RT["SU"]["LIST"]["functions"] = $this->get_sub_T($nv["_SH"], $_SHS_EDQ, "ROUTINES", "ROUTINE", "ROUTINE_DEFINITION", "ROUTINE_TYPE='FUNCTION' AND");


		$RT["SQL"]["events"] = $this->view_sub($RT["SU"]["LIST"]["events"], "EVENT");

		$RT["SQL"]["triggers"] = $this->view_sub($RT["SU"]["LIST"]["triggers"], "TRIGGER");


		$_ARP = [];

		$result = $this->request("SELECT p.SPECIFIC_NAME, ".
			"GROUP_CONCAT( CASE ".
			"WHEN p.PARAMETER_MODE = 'INOUT' THEN CONCAT('SET @', p.PARAMETER_NAME, '=value;') END ".
			"ORDER BY p.ORDINAL_POSITION SEPARATOR '\n') as 'SET', ".
			"GROUP_CONCAT( CASE ".
			"WHEN p.PARAMETER_MODE = 'OUT' THEN '@' ".
			"WHEN p.PARAMETER_MODE = 'INOUT' THEN '@' ".
			"ELSE CONCAT('(', p.DTD_IDENTIFIER, ') ') END, ".
			"p.PARAMETER_NAME ORDER BY p.ORDINAL_POSITION SEPARATOR ', ') as ARG, ".
			"GROUP_CONCAT( CASE ".
			"WHEN p.PARAMETER_MODE = 'OUT' THEN '@' ".
			"WHEN p.PARAMETER_MODE = 'INOUT' THEN '@' END, ".
			"p.PARAMETER_NAME ORDER BY p.ORDINAL_POSITION SEPARATOR ', ') as RUN ".
			"FROM information_schema.PARAMETERS p ".
			"WHERE SPECIFIC_SCHEMA=x'".$nv["_SH"]."' AND p.ROUTINE_TYPE='PROCEDURE' GROUP BY SPECIFIC_NAME ORDER BY p.SPECIFIC_NAME;", "", [], __LINE__, false);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$_ARP[$row["SPECIFIC_NAME"]] = $row;
			}
		}

		$RT["SQL"]["procedures"] = $this->view_sub($RT["SU"]["LIST"]["procedures"], "PROCEDURE", $_ARP);


		$_ARF = [];

		$result = $this->request("SELECT p.SPECIFIC_NAME, ".

			"GROUP_CONCAT( CASE ".
			"WHEN p.PARAMETER_MODE = 'OUT' THEN '@' ".
			"WHEN p.PARAMETER_MODE = 'INOUT' THEN '@' ".
			"ELSE CONCAT('(', p.DTD_IDENTIFIER, ') ') END, ".
			"p.PARAMETER_NAME ORDER BY p.ORDINAL_POSITION SEPARATOR ', ') as ARG ".
			"FROM information_schema.PARAMETERS p ".
			"WHERE SPECIFIC_SCHEMA=x'".$nv["_SH"]."' AND p.ROUTINE_TYPE='FUNCTION' GROUP BY SPECIFIC_NAME ORDER BY p.SPECIFIC_NAME;", "", [], __LINE__, false);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$_ARF[$row["SPECIFIC_NAME"]] = $row;
			}
		}

		$RT["SQL"]["functions"] = $this->view_sub($RT["SU"]["LIST"]["functions"], "FUNCTION", $_ARF);

		return $RT;
	}


	public function rc(array $nv, string $mode)
	{
		$_SHS = hex2bin($nv["_SH"]);
		$_TBS = hex2bin($nv["_TB"]);

		$_SHS_EDQ = $this->esN($_SHS);
		$_TBS_EDQ = $this->esN($_TBS);

		$RT = $this->INI;
		$RT["SH"] = $_SHS;
		$RT["TB"] = $_TBS;

		$RT["SQL"]["table"] = [];
		$RT["SQL_ADD"] = [];

		$C_T = [];
		$C_F = [];

		$LIST = [];
		$ORDER_LIST = [];
		$LIST_KEY = [];

		$RT["CREATE"]["SH"] = "";
		$RT["CREATE"]["TB"] = "";

		$CREATE = $this->request("SHOW CREATE DATABASE ".$_SHS_EDQ.";", "", [], __LINE__, false);

		if(!$CREATE[0]){return $RT;}

		$RT["CREATE"]["SH"] = $this->fetch_row($CREATE[1])[1];

		$CREATE = $this->request("SHOW CREATE TABLE ".$_SHS_EDQ.".".$_TBS_EDQ.";", "", [], __LINE__, false);

		if(!$CREATE[0]){return $RT;}

		$RT["CREATE"]["TB"] = $this->fetch_row($CREATE[1])[1];

		$create_tb = explode("\n", $RT["CREATE"]["TB"]);

		$result = $this->request("SELECT TABLE_TYPE, ENGINE, CREATE_OPTIONS FROM information_schema.TABLES WHERE ".
			"TABLE_SCHEMA=x'".$nv["_SH"]."' AND TABLE_NAME=x'".$nv["_TB"]."';", "", [], __LINE__, false);

		while( $row = $this->fetch_assoc($result[1]) ){

			$RT["TABLE_TYPE"] = $row["TABLE_TYPE"];
			$RT["ENGINE"] = $row["ENGINE"];
			$RT["CREATE_OPTIONS"] = $row["CREATE_OPTIONS"];
		}

		if($mode === "")
		{

			$result = $this->request("SELECT tc.CONSTRAINT_NAME, tc.CONSTRAINT_TYPE, ".
				"kc.COLUMN_NAME, kc.REFERENCED_TABLE_SCHEMA, kc.REFERENCED_TABLE_NAME, kc.REFERENCED_COLUMN_NAME, rc.UPDATE_RULE, rc.DELETE_RULE ".
				"FROM information_schema.TABLE_CONSTRAINTS tc ".
				"LEFT JOIN information_schema.KEY_COLUMN_USAGE kc ".
				"ON tc.TABLE_SCHEMA = kc.TABLE_SCHEMA ".
				"AND tc.TABLE_NAME = kc.TABLE_NAME ".
				"AND tc.CONSTRAINT_NAME = kc.CONSTRAINT_NAME ".
				"LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ".
				"ON rc.CONSTRAINT_SCHEMA  = kc.TABLE_SCHEMA ".
				"AND rc.TABLE_NAME = kc.TABLE_NAME ".
				"AND rc.CONSTRAINT_NAME = kc.CONSTRAINT_NAME ".
				"WHERE tc.TABLE_SCHEMA = x'".$nv["_SH"]."' AND tc.TABLE_NAME = x'".$nv["_TB"]."';", "", [], __LINE__, false);

			while( $row = $this->fetch_assoc($result[1]) )
			{
				if($row["CONSTRAINT_TYPE"] !== NULL){

					$C_T[$row["COLUMN_NAME"]][] = $row["CONSTRAINT_TYPE"];
				}

				if(trim((string)$row["REFERENCED_COLUMN_NAME"]) !== ""){

					$C_F[$row["COLUMN_NAME"]][] = $row;
				}

				if(trim((string)$row["CONSTRAINT_TYPE"]) === "CHECK"){

					$RT["SQL_ADD"]["DROP CHECK"]["DROP CHECK ".$row["CONSTRAINT_NAME"]] = "ALTER TABLE ".$_TBS_EDQ." "."DROP CHECK ".$this->esN($row["CONSTRAINT_NAME"]).";";
				}
				elseif(trim((string)$row["CONSTRAINT_TYPE"]) === "FOREIGN KEY"){

					$RT["SQL_ADD"]["ADD FOREIGN KEY"][$row["CONSTRAINT_NAME"]]["COLUMN_NAME"][] = $this->esN($row["COLUMN_NAME"]);
					$RT["SQL_ADD"]["ADD FOREIGN KEY"][$row["CONSTRAINT_NAME"]]["REFERENCED_COLUMN_NAME"][] = $this->esN($row["REFERENCED_COLUMN_NAME"]);
					$RT["SQL_ADD"]["ADD FOREIGN KEY"][$row["CONSTRAINT_NAME"]]["REFERENCED_TABLE_SCHEMA"] = $row["REFERENCED_TABLE_SCHEMA"];
					$RT["SQL_ADD"]["ADD FOREIGN KEY"][$row["CONSTRAINT_NAME"]]["REFERENCED_TABLE_NAME"] = $row["REFERENCED_TABLE_NAME"];
					$RT["SQL_ADD"]["ADD FOREIGN KEY"][$row["CONSTRAINT_NAME"]]["UPDATE_RULE"] = $row["UPDATE_RULE"];
					$RT["SQL_ADD"]["ADD FOREIGN KEY"][$row["CONSTRAINT_NAME"]]["DELETE_RULE"] = $row["DELETE_RULE"];

					$RT["SQL_ADD"]["DROP FOREIGN KEY"]["DROP FOREIGN KEY ".$row["CONSTRAINT_NAME"]] =
						"ALTER TABLE ".$_TBS." "."DROP FOREIGN KEY ".$this->esN($row["CONSTRAINT_NAME"]).";";
				}
			}
		}

		$result = $this->request("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_KEY, ".
			"COLUMN_DEFAULT, IS_NULLABLE, EXTRA, NUMERIC_PRECISION, GENERATION_EXPRESSION ".
			"FROM information_schema.columns WHERE TABLE_SCHEMA=x'".$nv["_SH"]."' ".
			"AND table_name = x'".$nv["_TB"]."' ORDER BY ORDINAL_POSITION;", "", [], __LINE__, false);

		if($result[0])
		{
			while($row = $this->fetch_assoc($result[1]))
			{
				$RT["FIELDS"][$row["COLUMN_NAME"]] = $row;

				$COLUMN_NAME = $this->esN($row["COLUMN_NAME"]);

				if($mode === "")
				{
					if(!in_array( $row["DATA_TYPE"], $this->GT["blob"]) && !in_array( $row["DATA_TYPE"], $this->GT["binary"]) && !in_array( $row["DATA_TYPE"], $this->GT["geo"])){

						$RT["FIELD_SE_FILTER"][] = $row["COLUMN_NAME"];
					}

					$RT["FIELD_SE_VIEW"][] = $row["COLUMN_NAME"];

					$RT["DATA_NEW"][0][$row["COLUMN_NAME"]] = "";

					if(isset($C_T[$row["COLUMN_NAME"]])){

						$RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"] = $C_T[$row["COLUMN_NAME"]];
					}
					else{

						$RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"] = [];
					}

					$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = [];

					if(($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "enum") || ($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "set"))
					{
						$temp = preg_replace("/^(enum|set)\('/", "", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_TYPE"]);
						$temp = preg_replace("/'\)$/", "", $temp);
						$temp = str_replace("\\\\", "\\", (string)$temp);

						$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"] = explode("','", $temp);

						for($i=0;$i<count($RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"]);$i++){

							$RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"][$i] = preg_replace("/\'\'/", "'", $RT["FIELDS"][$row["COLUMN_NAME"]]["COLUMN_VALUE"][$i]);
						}
					}

					$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = false;

					if(in_array("FOREIGN KEY", $RT["FIELDS"][$row["COLUMN_NAME"]]["CONSTRAINT"]))
					{
						foreach($C_F[$row["COLUMN_NAME"]] as $vf)
						{
							$constraint_value = $this->request("SELECT ".$this->esN($vf["REFERENCED_COLUMN_NAME"])." FROM ".
								$this->esN($vf["REFERENCED_TABLE_SCHEMA"]).".".$this->esN($vf["REFERENCED_TABLE_NAME"]).";", "", [], __LINE__, false);

							if($constraint_value[0]){

								while($row_constraint_value = $this->fetch_row($constraint_value[1])){

									$RT["FIELDS"][$row['COLUMN_NAME']]["COLUMN_VALUE"][] = $row_constraint_value[0];
								}
							}

							$RT["FIELDS"][$row["COLUMN_NAME"]]["FOREIGN"] = true;
						}
					}

					$RT["FIELDS"][$row["COLUMN_NAME"]]["GENERATED"] = "";

					if(($row["GENERATION_EXPRESSION"] !== "") &&
						(($row["EXTRA"] === "VIRTUAL GENERATED") || ($row["EXTRA"] === "VIRTUAL GENERATED INVISIBLE") ||
						($row["EXTRA"] === "STORED GENERATED") || ($row["EXTRA"] === "STORED GENERATED INVISIBLE")))
					{
						$RT["FIELDS"][$row["COLUMN_NAME"]]["GENERATED"] = $row["GENERATION_EXPRESSION"];
					}

					if(($row["EXTRA"] === "auto_increment") || ($RT["FIELDS"][$row["COLUMN_NAME"]]["GENERATED"] !== ""))
					{
						$RT["FIELDS"][$row["COLUMN_NAME"]]["DISABLED"] = false;
					}
					else{$RT["FIELDS"][$row["COLUMN_NAME"]]["DISABLED"] = true;}
				}

				if($row["COLUMN_KEY"] === "PRI"){

					$RT["PRI"] = true;
				}

				if($RT["FIELDS"][$row["COLUMN_NAME"]]["DATA_TYPE"] === "bit"){

					$LIST[] = "LPAD(BIN(".$COLUMN_NAME."), ".$RT["FIELDS"][$row["COLUMN_NAME"]]["NUMERIC_PRECISION"].", '0') AS ".$COLUMN_NAME;
				}
				elseif(in_array( $row["DATA_TYPE"], $this->GT["blob"]) || in_array( $row["DATA_TYPE"], $this->GT["binary"])){

					if($nv["view_rc"] === "1"){

						$LIST[] = "HEX(".$COLUMN_NAME.") AS ".$COLUMN_NAME;
					}
					else{

						$LIST[] = "LENGTH(".$COLUMN_NAME.") AS ".$COLUMN_NAME;
					}
				}
				elseif(in_array( $row["COLUMN_TYPE"], $this->GT["text"])){

					if($nv["view_rc"] === "1"){

						$LIST[] = $COLUMN_NAME." AS ".$COLUMN_NAME;
					}
					else{

						$LIST[] = "LENGTH(".$COLUMN_NAME.") AS ".$COLUMN_NAME;
					}
				}
				elseif(in_array( $row["COLUMN_TYPE"], $this->GT["geo"])){

					$LIST[] = "ST_AsText(".$COLUMN_NAME.") AS ".$COLUMN_NAME;
				}
				else{

					$LIST[] = $COLUMN_NAME;

					if(!in_array( $row["COLUMN_TYPE"], $this->GT["text"])){

						$LIST_KEY[] = $row['COLUMN_NAME'];
					}
				}

				if(($row["COLUMN_TYPE"] !== "json") && !in_array( $row["DATA_TYPE"], $this->GT["blob"]) && !in_array( $row["COLUMN_TYPE"], $this->GT["text"]))
				{
					$ORDER_LIST[] = $COLUMN_NAME;

					if($mode === ""){

						$RT["FIELD_SE_ORDER"][] = $row["COLUMN_NAME"];
					}
				}
			}
		}

		if(($mode === "") || ($mode === "export_obj") || ($mode === "export_rc"))
		{
			$WHERE = "";
			if(($mode === "") || ($mode === "export_rc"))
			{
				$WHERE = $this->get_wr($nv, $RT["FIELDS"]);

				if($WHERE !== ""){$WHERE = " WHERE ".$WHERE;}

				$RT["SQL_ADD"]["WHERE"] = $WHERE;
			}

			$result = $this->request("SELECT COUNT(*) FROM ".$_SHS_EDQ.".".$_TBS_EDQ." ".$WHERE." ;", "", [], __LINE__, false);

			if($result[0]){

				$RT["COUNT"] = $this->fetch_row($result[1])[0];

				if($RT["COUNT"] <= $nv["from_rc"]){$nv["from_rc"] = "0";}
			}

			if($RT["COUNT"] != 0)
			{
				$LIMIT = "";
				if($mode === "")
				{
					$LIMIT = " LIMIT ".$nv["from_rc"].", ".$nv["page_rc"];
				}

				$ORDER_LIST	= array_values($ORDER_LIST);

				if(count($ORDER_LIST) === 0){$order_list_st = "";}
				else{

					$order_list_st = " ORDER BY ".$ORDER_LIST[($nv["order_rc"])]." ".$nv["order_desc_rc"].",".implode(" ".$nv["order_desc_rc"].", ", $ORDER_LIST);
				}

				$result = $this->request("SELECT ".implode(", ",  $LIST)." FROM ".$_SHS_EDQ.".".$_TBS_EDQ." ".$WHERE.$order_list_st.$LIMIT.";", "", [], __LINE__, false);

				if($result[0])
				{
					while($res = $this->fetch_assoc($result[1])){

						$RT["DATA"][] = $res;
					}
				}
			}
		}

		if($mode === "")
		{
			$RT["FILTER_EX"] = ["","=","<>",">",">=","<","<=","LIKE %...%","NOT LIKE %...%","REGEXP","NOT REGEXP","IS NULL","IS NOT NULL"];

			$count_page = 0;

			do{

				$RT["FROM"][] = $count_page;
				$count_page = $count_page + $nv["page_rc"];
			}
			while($count_page < $RT["COUNT"]);

			if(count($RT["DATA"]) === 0){

				foreach($RT["FIELDS"] as $k=>$v){

					$RT["DATA"][0][$k] = "";
				}
				$RT["COUNT"] = 0;
			}

			$result = $this->request("SELECT CURRENT_USER();", "", [], __LINE__, false);
			if($result[0]){

				$user = $this->fetch_row($result[1])[0];

				$_user = explode("@", $user);
			}

			$result = $this->request("SELECT PRIVILEGE_TYPE, COLUMN_NAME FROM information_schema.COLUMN_PRIVILEGES ".
				"WHERE GRANTEE = '\'".$_user[0]."\'@\'".$_user[1]."\'' ".
				"AND TABLE_SCHEMA=x'".$nv["_SH"]."' AND TABLE_NAME=x'".$nv["_TB"]."';", "", [], __LINE__, false);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) ){

					$RT["PRIVILEGES"]["COLUMN_PRIVILEGES"][$row["PRIVILEGE_TYPE"]][] = $row["COLUMN_NAME"];
				}
			}

			$result = $this->request("SELECT PRIVILEGE_TYPE FROM information_schema.TABLE_PRIVILEGES ".
				"WHERE GRANTEE = '\'".$_user[0]."\'@\'".$_user[1]."\'' ".
				"AND TABLE_SCHEMA=x'".$nv["_SH"]."' AND TABLE_NAME=x'".$nv["_TB"]."';", "", [], __LINE__, false);

			if($result[0]){

				while( $row = $this->fetch_assoc($result[1]) ){

					$RT["PRIVILEGES"]["TABLE_PRIVILEGES"][] = $row["PRIVILEGE_TYPE"];
				}
			}

			$RT["ACF"] = [
				'_DELETE_RC_FILTER'=>_ACTION_DELETE,
				''=>'-- '._ACTION_EXPORT,
				'_VIEW_SQL_RC_FILTER'=>_ACTION_VIEW_SQL,
				'_SAVE_SQL_RC_FILTER'=>_ACTION_SAVE_SQL];

			if($RT["TABLE_TYPE"] === "VIEW")
			{
				$RT["SQL"]["table"]["CREATE"] = $RT["CREATE"]["TB"].";\n\n";
				$RT["SQL"]["table"]["RENAME"] = "RENAME TABLE ".$_TBS_EDQ." TO table_name;\n\n";
				$RT["SQL"]["table"]["DROP"] = "DROP VIEW ".$_TBS_EDQ.";\n\n";
			}
			elseif((strtoupper((string)$RT["ENGINE"]) === "FEDERATED") || (strtoupper((string)$RT["ENGINE"]) === "MRG_MYISAM") || (strtoupper((string)$RT["ENGINE"]) === "ARCHIVE"))
			{
				$RT["SQL"]["table"]["CREATE"] = "USE ".$_SHS_EDQ.";\n\n".
					$RT["CREATE"]["TB"].";\n\n";

				$RT["SQL"]["table"]["CREATE LIKE"] = "USE ".$_SHS_EDQ.";\n\n".
					"CREATE TABLE table_name LIKE ".$_SHS_EDQ.".".$_TBS_EDQ.";\n\n".
					"INSERT INTO table_name SELECT * FROM ".$_SHS_EDQ.".".$_TBS_EDQ.";\n\n";

				$RT["SQL"]["table"]["CREATE AS"] = "USE ".$_SHS_EDQ.";\n\n".
					"CREATE TABLE table_name AS SELECT * FROM ".$_SHS_EDQ.".".$_TBS_EDQ.";\n\n";

				$RT["SQL"]["table"]["RENAME"] = "RENAME TABLE ".$_TBS_EDQ." TO table_name;\n\n";
				$RT["SQL"]["table"]["DROP"] = "DROP TABLE ".$_TBS_EDQ.";\n\n";
			}
			else
			{
				$RT["SQL"]["table"]["CREATE"] = "USE ".$_SHS_EDQ.";\n\n".
					"SET FOREIGN_KEY_CHECKS=0;\n\n".
					$RT["CREATE"]["TB"].";\n\n".
					"SET FOREIGN_KEY_CHECKS=1;";

				$FKC = "";

				if(isset($RT["SQL_ADD"]["ADD FOREIGN KEY"]))
				{
					foreach($RT["SQL_ADD"]["ADD FOREIGN KEY"] as $fk=>$fv)
					{
						$FKC .= "ALTER TABLE table_name ADD FOREIGN KEY (".implode(",", $fv["COLUMN_NAME"]).") "."REFERENCES ".
							$this->esN($fv["REFERENCED_TABLE_SCHEMA"]).".".$this->esN($fv["REFERENCED_TABLE_NAME"])." ".
							"(".implode(",", $fv["REFERENCED_COLUMN_NAME"]).") ".
							"ON UPDATE ".$fv["UPDATE_RULE"]." ON DELETE ".$fv["DELETE_RULE"].";\n\n";
					}
				}

				$RT["SQL"]["table"]["CREATE LIKE"] = "USE ".$_SHS_EDQ.";\n\n".
					"CREATE TABLE table_name LIKE ".$_SHS_EDQ.".".$_TBS_EDQ.";\n\n".$FKC.
					"INSERT INTO table_name SELECT * FROM ".$_SHS_EDQ.".".$_TBS_EDQ.";\n\n";

				$RT["SQL"]["table"]["CREATE AS"] = "USE ".$_SHS_EDQ.";\n\n".
					"CREATE TABLE table_name AS SELECT * FROM ".$_SHS_EDQ.".".$_TBS_EDQ.";\n\n".$FKC;

				$f = [];
				foreach($nv["field_rc"] as $v){

					$f[] = $this->esN(hex2bin((string)$v));
				}

				$sf = "*";
				if(count($f) !== 0){$sf = implode(", ", $f);}

				$RT["SQL"]["table"]["CREATE VIEW"] = "CREATE VIEW view_name AS SELECT ".$sf." FROM ".$_TBS_EDQ."".$RT["SQL_ADD"]["WHERE"].";";

				$RT["SQL"]["table"]["RENAME TABLE"] = "ALTER TABLE ".$_TBS_EDQ." RENAME TO table_name;";
				
				$RT["SQL"]["table"]["ADD PRIMARY KEY"] = "ALTER TABLE ".$_TBS_EDQ." "."ADD PRIMARY KEY (column_name);";
				$RT["SQL"]["table"]["ADD FOREIGN KEY"] = "ALTER TABLE ".$_TBS_EDQ." "."ADD FOREIGN KEY (column_name) REFERENCES table_name (column_name);";	
				$RT["SQL"]["table"]["ADD UNIQUE"] = "ALTER TABLE ".$_TBS_EDQ." "."ADD UNIQUE (column_name);";
				$RT["SQL"]["table"]["ADD INDEX"] = "ALTER TABLE ".$_TBS_EDQ." "."ADD INDEX (column_name);";
				$RT["SQL"]["table"]["ADD SPATIAL INDEX"] = "ALTER TABLE ".$_TBS_EDQ." "."ADD SPATIAL INDEX (column_name);";
				$RT["SQL"]["table"]["ADD FULLTEXT INDEX"] = "ALTER TABLE ".$_TBS_EDQ." "."ADD FULLTEXT INDEX (column_name);";						

				$RT["SQL"]["table"]["DROP TABLE"] = "DROP TABLE ".$_TBS_EDQ.";";				
				
				if(isset($RT["SQL_ADD"]["DROP FOREIGN KEY"]))
				{
					$RT["SQL"]["table"] = array_merge($RT["SQL"]["table"], $RT["SQL_ADD"]["DROP FOREIGN KEY"]);
				}					

				if($RT["CREATE_OPTIONS"] === "partitioned"){

					$RT["SQL"]["table"]["REMOVE PARTITIONING"] = "ALTER TABLE ".$_TBS_EDQ." "."REMOVE PARTITIONING;";
				}		

				$IN_ADD = [];
				$IN_ADD["DROP PRIMARY KEY"] = "";
				
				$result = $this->request("SELECT INDEX_NAME FROM information_schema.STATISTICS ".
					"WHERE TABLE_SCHEMA = x'".$nv["_SH"]."' AND TABLE_NAME = x'".$nv["_TB"]."' ORDER BY INDEX_NAME;", "", [], __LINE__, false);

				while( $row = $this->fetch_row($result[1]) )
				{
					if(trim((string)$row[0]) !== "PRIMARY"){

						$IN_ADD["DROP INDEX ".$row[0]] = "ALTER TABLE ".$_TBS_EDQ." DROP INDEX ".$this->esN($row[0]).";";
					}
					else{

						$IN_ADD["DROP PRIMARY KEY"] = "ALTER TABLE ".$_TBS_EDQ." DROP PRIMARY KEY;";
					}
				}

				$RT["SQL"]["table"] = array_merge($RT["SQL"]["table"], $IN_ADD);

				$COLUMN = array_keys($RT["FIELDS"]);

				$CL_ADD = [];
				$CL_CHANGE = [];
				$CL_DROP = [];

				$CL_ADD["ADD COLUMN FIRST"] = "ALTER TABLE ".$_TBS_EDQ." "."ADD \ncolumn_name column_definition \nFIRST;";

				foreach($COLUMN as $VCOLUMN)
				{
					$VCOLUMN = $this->esN($VCOLUMN);

					$CL_ADD["ADD COLUMN AFTER ".$VCOLUMN.""] = "ALTER TABLE ".$_TBS_EDQ." ADD \ncolumn_name column_definition \nAFTER ".$VCOLUMN.";";
					$CL_CHANGE["CHANGE COLUMN ".$VCOLUMN.""] = "ALTER TABLE ".$_TBS_EDQ." CHANGE COLUMN ".$VCOLUMN." \ncolumn_name column_definition;";
					$CL_DROP["DROP COLUMN ".$VCOLUMN.""] = "ALTER TABLE ".$_TBS_EDQ." DROP COLUMN ".$VCOLUMN.";";
				}

				$RT["SQL"]["columns"] = array_merge($CL_ADD, $CL_CHANGE, $CL_DROP);
			}
		}

		return $RT;
	}


	public function searching($_SH, $_TB, $find, $mode)
	{
		$count = 0;

		$find = trim((string)$find);

		$this->_RS["RESULT"][] = ""._MESSAGE_SEARCHING.": ".$find."\n\n";

		if($_SH === ""){

			$lsh = $this->list_sh();
			$ltb = [];
		}
		elseif(($_SH !== "") && ($_TB === "")){

			$lsh = [$_SH];
			$ltb = [];
		}
		elseif(($_SH !== "") && ($_TB !== "")){

			$lsh = [$_SH];
			$ltb = [$_TB];
		}

		foreach($lsh as $_SH)
		{
			$_SHS = $this->esN(hex2bin($_SH));

			if(count($ltb) === 0){

				$list_tb = $this->list_tb($_SH);
			}
			else{

				$list_tb = $ltb;
			}

			foreach($list_tb as $val)
			{
				$field = [];

				$result = $this->request("SELECT COLUMN_NAME, DATA_TYPE, NUMERIC_PRECISION FROM information_schema.columns WHERE TABLE_SCHEMA=x'".$_SH."' ".
					"AND table_name = x'".$val."' ORDER BY ORDINAL_POSITION;", "", [], __LINE__);

				if($result[0]){

					while($row = $this->fetch_assoc($result[1])){

						$COLUMN_NAME = $this->esN($row["COLUMN_NAME"]);

						if($row["DATA_TYPE"] === "float"){

							$field[] = "(SIGN(".$COLUMN_NAME.") * abs(".$COLUMN_NAME."))";
						}
						elseif($row["DATA_TYPE"] === "bit"){

							$field[] = "LPAD(BIN(".$COLUMN_NAME."), ".$row["NUMERIC_PRECISION"].", '0')";
						}
						elseif(in_array($row["DATA_TYPE"], $this->GT["geo"])){

							$field[] = "ST_AsText(".$COLUMN_NAME.")";
						}
						else{

							$field[] = "".$COLUMN_NAME."";
						}
					}
				}

				$tc = implode(",", $field);

				$valS = $this->esN(hex2bin((string)$val));

				$result = $this->request("SELECT ".$tc." FROM ".$_SHS.".".$valS.";", "", [], __LINE__);

				if($result[0])
				{
					$res = $_SHS.".".$valS.":\n";

					while($row = $this->fetch_assoc($result[1]))
					{
						$F = false;

						$res_rw = "";

						foreach($row as $k=>$v)
						{
							if(($mode === "0") && stristr((string)$v, $find))
							{
								$F = true;
								$res_rw .= " * [ ".$k." ] - ".$v."\n";
							}
							elseif(($mode === "1") && ($v === trim((string)$find)))
							{
								$F = true;
								$res_rw .= " * [ ".$k." ] - ".$v."\n";
							}
							else
							{
								$res_rw .= "[ ".$k." ] - ".$v."\n";
							}
						}

						if($F){

							$this->_RS["RESULT"][] = $res.$res_rw."\n";
							$count += 1;
						}
					}
				}
			}
		}

		if($count == 0){ $this->_RS["RESULT"][] = _MESSAGE_FIND_NOT_FOUND; }
	}


	public function export_sql($list_sh, $list_tb, $nv, $mode)
	{
		$filename = date("d-m-Y").".sql";

		$crt_view_temp = "";
		$crt_view = "";

		$sT = PHP_EOL." -- MySQL database dump";
		$sT .= PHP_EOL." -- Server version: ".$this->server_info.PHP_EOL.PHP_EOL;

		$sT .= PHP_EOL."SET NAMES ".$this->character_name.";";
		$sT .= PHP_EOL."SET FOREIGN_KEY_CHECKS=0;";
		$sT .= PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';".PHP_EOL;

		foreach($list_sh as $value)
		{
			$_SHS = $this->esN(hex2bin((string)$value));

			if($mode === "SH"){

				$CREATE = $this->request("SHOW CREATE DATABASE ".$_SHS.";", "", [], __LINE__, false);

				if($CREATE[0]){

					$sT .= PHP_EOL.$this->fetch_row($CREATE[1])[1].";".PHP_EOL;

					$sT .= PHP_EOL."USE ".$_SHS.";";
				}

				$nv["field_rc"] = [];

				$RT = $this->export($value, $this->list_tb($value), $nv, "export_obj");
			}
			elseif($mode === "TB"){

				$nv["field_rc"] = [];

				$RT = $this->export($value, $list_tb, $nv, "export_obj");
			}
			else{

				$RT = $this->export($value, $list_tb, $nv, "export_rc");
			}

			foreach($RT as $v)
			{
				$row = [];

				if($v["TABLE_TYPE"] === "VIEW")
				{
					if($mode === "SH"){

						$crt_view_temp .= PHP_EOL."USE ".$_SHS.";".PHP_EOL;
						$crt_view .= PHP_EOL."USE ".$_SHS.";".PHP_EOL;
					}

					$result = $this->request("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=x'".$value."' ".
						"AND TABLE_NAME='".addslashes($v["TB"])."';", "", [], __LINE__, false);

					$cl = [];

					if($result[0])
					{
						while( $row = $this->fetch_assoc($result[1]) ){

							$cl[] = $this->esN($row["COLUMN_NAME"]);
						}

						$crt_view_temp .= PHP_EOL."-- Temporary view structure for view ".$v["TB"]."";

						$crt_view_temp .= PHP_EOL."CREATE VIEW ".$this->esN($v["TB"])." AS SELECT 1 AS ".implode(", 1 AS ", $cl).";".PHP_EOL;

						$crt_view .= PHP_EOL."/*!50001 DROP VIEW IF EXISTS ".$this->esN($v["TB"])." */;";

						$this->request("USE ".$_SHS.";", "", [], __LINE__);

						$listview = $this->request("SHOW CREATE TABLE ".$_SHS.".".$this->esN($v["TB"]).";", "", [], __LINE__, false);

						if($listview[0]){

							$CT = $this->fetch_row($listview[1])[1];

							$crt_view .= PHP_EOL.$CT.";".PHP_EOL;
						}
					}
				}
				elseif(($v["ENGINE"] === "MRG_MyISAM") || ($v["ENGINE"] === "MRG_MYISAM"))
				{
					if($mode !== "RC"){

						$sT .= PHP_EOL.PHP_EOL.$v["CREATE"]["TB"].";".PHP_EOL;
					}
				}
				elseif($v["ENGINE"] === "FEDERATED")
				{
					if($mode !== "RC"){

						$sT .= PHP_EOL.PHP_EOL.$v["CREATE"]["TB"].";".PHP_EOL;
					}
				}
				else
				{
					if($mode !== "RC"){

						$sT .= PHP_EOL.PHP_EOL.$v["CREATE"]["TB"].";".PHP_EOL;
					}

					foreach($v["DATA"] as $vr)
					{
						$vrex = [];

						foreach($vr as $kf=>$vf)
						{
							$kf_esN = $this->esN($kf);

							if((count($nv["field_rc"]) === 0) || (in_array(bin2hex((string)$kf), $nv["field_rc"])))
							{
								if(($v["FIELDS"][$kf]["EXTRA"] === "VIRTUAL GENERATED") || ($v["FIELDS"][$kf]["EXTRA"] === "VIRTUAL GENERATED INVISIBLE") ||
									($v["FIELDS"][$kf]["EXTRA"] === "STORED GENERATED") || ($v["FIELDS"][$kf]["EXTRA"] === "STORED GENERATED INVISIBLE")){
								}
								elseif(in_array($v["FIELDS"][$kf]["DATA_TYPE"], $this->GT["geo"]))
								{
									if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

										$vrex[$kf_esN] = "NULL";
									}
									else{

										$vrex[$kf_esN] = "ST_GeomFromText('".$vf."')";
									}
								}
								elseif(in_array($v["FIELDS"][$kf]["DATA_TYPE"], $this->GT["blob"]) || in_array($v["FIELDS"][$kf]["DATA_TYPE"], $this->GT["binary"]))
								{
									if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

										$vrex[$kf_esN] = "NULL";
									}
									else{

										$vrex[$kf_esN] = "x'".$vf."'";
									}
								}
								elseif($v["FIELDS"][$kf]["DATA_TYPE"] === "bit")
								{
									if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

										$vrex[$kf_esN] = "NULL";
									}
									else{

										$vrex[$kf_esN] = "b'".$vf."'";
									}
								}
								else
								{
									if(($vf === NULL) && ($v["FIELDS"][$kf]["IS_NULLABLE"] === "YES")){

										$vrex[$kf_esN] = "NULL";
									}
									else{

										$vrex[$kf_esN] = "'".addslashes($vf)."'";
									}
								}
							}
						}

						$row[] = "(".implode(",", $vrex).")";
					}

					if(count($row) !== 0){

						$sT .= PHP_EOL."insert into ".$this->esN($v["TB"])." (".implode(",", array_keys($vrex)).") values".
							PHP_EOL.implode(",".PHP_EOL, $row).";".PHP_EOL;
					}
				}
			}

			if($mode === "SH")
			{
				$this->request("USE ".$_SHS.";", "", [], __LINE__, false);

				$triggers = implode("".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS, "TRIGGERS", "TRIGGER", "SHOW CREATE TRIGGER",
					"SQL Original Statement", ""))."";

				if($triggers !== ""){

					$sT .= PHP_EOL."/* TRIGGER */".PHP_EOL;
					$sT .= PHP_EOL.$triggers.PHP_EOL;
					$sT .= PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';".PHP_EOL;
				}

				$procedures = implode("".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS, "ROUTINES", "ROUTINE", "SHOW CREATE PROCEDURE",
					"Create Procedure", "ROUTINE_TYPE='PROCEDURE' AND"))."";

				if($procedures !== ""){

					$sT .= PHP_EOL."/* PROCEDURES */".PHP_EOL;
					$sT .= PHP_EOL.$procedures.PHP_EOL;
					$sT .= PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';".PHP_EOL;
				}

				$functions = implode("".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS, "ROUTINES", "ROUTINE", "SHOW CREATE FUNCTION",
					"Create Function", "ROUTINE_TYPE='FUNCTION' AND"))."";

				if($functions !== ""){

					$sT .= PHP_EOL."/* FUNCTIONS */".PHP_EOL;
					$sT .= PHP_EOL.$functions.PHP_EOL;
					$sT .= PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';".PHP_EOL;
				}

				$events = implode("".PHP_EOL.PHP_EOL, $this->get_sub($value, $_SHS, "EVENTS", "EVENT", "SHOW CREATE EVENT", "Create Event", ""))."";

				if($events !== ""){

					$sT .= PHP_EOL."/* EVENTS */".PHP_EOL;
					$sT .= PHP_EOL.$events.PHP_EOL;
					$sT .= PHP_EOL."SET SQL_MODE = '".$this->sql_mode."';".PHP_EOL;
				}
			}
		}

		if($crt_view_temp !== ""){

			$sT .= PHP_EOL."/* VIEWS */".PHP_EOL;
		}

		$sT .= $crt_view_temp;
		$sT .= $crt_view;

		return [$filename, $sT];
	}


	public function export($_SH, $list_tb, $nv, $mode)
	{
		$RT = [];

		foreach($list_tb as $val)
		{
			$nv["_SH"] = $_SH;
			$nv["_TB"] = $val;
			$nv["view_rc"] = "1";

			$RT[] = $this->rc( $nv, $mode );
		}

		return $RT;
	}


	public function export_get($res)
	{
		header("Content-Type: text/html");
		header("Content-Disposition: attachment; filename=".$res[0]);
		header("Content-Transfer-Encoding: binary");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Length: ".strlen((string)$res[1]));
		print $res[1];

		die();
	}


	public function res_get($res)
	{
		$this->_RS["RESULT"][] = $res[1];
	}


	public function delete_sh($list_sh)
	{
		foreach($list_sh as $val)
		{
			$valS = hex2bin((string)$val);
			$valSN = $this->esN($valS);

			if(!in_array($valS, $this->DS)){

				$this->request("DROP DATABASE ".$valSN.";", "", [], __LINE__);
			}
		}
	}

	public function clear_sh($list_sh)
	{
		foreach($list_sh as $val)
		{
			$valS = hex2bin((string)$val);
			$valSN = $this->esN($valS);

			if(!in_array($valS, $this->DS))
			{
				$result = $this->request("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=x'".$val."';",
					"", [], __LINE__, false);

				if($result[0])
				{
					$schema = $this->fetch_row($result[1]);

					$this->request("DROP DATABASE ".$valSN.";", "", [], __LINE__);

					$this->request("CREATE DATABASE ".$valSN." CHARACTER SET ".$schema[0]." COLLATE ".$schema[1].";", "", [], __LINE__);
				}
			}
		}
	}


	public function delete_tb($_SH, $list_tb)
	{
		$_SHS = hex2bin($_SH);
		$_SHS_EDQ = $this->esN($_SHS);

		if(!in_array($_SHS, $this->DS))
		{
			$VIEW = [];

			$result = $this->request("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA=x'".$_SH."';", "", [], __LINE__, false);

			while( $row = $this->fetch_assoc($result[1]) ){ $VIEW[] = $row["TABLE_NAME"];}

			$A = [];
			$B = [];

			foreach($list_tb as $val){

				$valS = hex2bin((string)$val);
				$valSN = $this->esN($valS);

				if(!in_array($valS, $this->DT)){

					if(in_array($valS, $VIEW)){	$A[] = $valSN; }
					else{ $B[] = $valSN; }
				}
			}

			$this->request("USE ".$_SHS_EDQ.";", "", [], __LINE__, false);

			if(count($A) > 0){ $this->request("DROP VIEW ".implode(", ", $A).";", "", [], __LINE__); }
			if(count($B) > 0){ $this->request("DROP TABLE ".implode(", ", $B).";", "", [], __LINE__); }
		}
	}


	public function clear_tb($_SH, $list_tb)
	{
		$_SHS = hex2bin($_SH);
		$_SHS_EDQ = $this->esN($_SHS);

		if(!in_array($_SHS, $this->DS))
		{
			if(isset($list_tb))
			{
				foreach($list_tb as $val)
				{
					$valS = hex2bin((string)$val);
					$valSN = $this->esN($valS);

					if(!in_array($valS, $this->DT)){

						$this->request("DELETE FROM ".$_SHS_EDQ.".".$valSN.";", "", [], __LINE__);
					}
				}
			}
		}
	}


	public function list_sh_filter($nv)
	{
		$RT = [];

		$WHERE = $this->get_wr($nv, []);

		if($WHERE !== ""){$WHERE = " WHERE ".$WHERE;}

		$result = $this->request("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ".$WHERE.";", "", [], __LINE__, false);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) )
			{
				$RT[] = bin2hex($row["SCHEMA_NAME"]);
			}
		}

		return $RT;
	}

	public function list_sh()
	{
		$RT = [];

		$result = $this->request("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA;", "", [], __LINE__);

		if($result[0]){

			while($row = $this->fetch_row($result[1])){

				$RT[] = bin2hex((string)$row[0]);
			}
		}

		return $RT;
	}


	public function list_tb_filter($nv)
	{
		$RT = [];

		$WHERE = $this->get_wr($nv, []);

		if($WHERE !== ""){$WHERE = " AND (".$WHERE.") ";}

		$result = $this->request("SELECT TABLE_NAME "."FROM information_schema.TABLES WHERE TABLE_SCHEMA=x'".$nv["_SH"]."' ".$WHERE.";",
			"", [], __LINE__, false);

		if($result[0]){

			while( $row = $this->fetch_assoc($result[1]) ){

				$RT[] = bin2hex($row["TABLE_NAME"]);
			}
		}

		return $RT;
	}

	public function list_tb($_SH)
	{
		$RT = [];

		if($_SH !== "")
		{
			$_SHS = $this->esN(hex2bin($_SH));

			$result = $this->request("SHOW TABLES FROM ".$_SHS.";", "", [], __LINE__, false);

			if($result[0]){

				while($row = $this->fetch_row($result[1])){

					$RT[] = bin2hex((string)$row[0]);
				}
			}
		}

		return $RT;
	}



	public function edit_rc($_SH, $_TB, $key, $list_rw, $field, $text, $file, $function, $action)
	{
		$_SHS = $this->esN(hex2bin($_SH));
		$_TBS = $this->esN( hex2bin($_TB));

		$type = [];

		$result = $this->request("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE FROM information_schema.columns WHERE TABLE_SCHEMA=x'".$_SH."' ".
			"AND table_name = x'".$_TB."';", "", [], __LINE__, false);

		while($row = $this->fetch_assoc($result[1])){

			$type[$row["COLUMN_NAME"]]["DATA_TYPE"] = $row["DATA_TYPE"];
			$type[$row["COLUMN_NAME"]]["COLUMN_DEFAULT"] = $row["COLUMN_DEFAULT"];
			$type[$row["COLUMN_NAME"]]["IS_NULLABLE"] = $row["IS_NULLABLE"];
		}

		$tk = array_keys($type);

		$sfC = [];
		$sfV = [];
		$sfK = [];

		foreach($key as $kh=>$vh)
		{
			$kq = hex2bin((string)$kh);

			$k = $this->esN(hex2bin((string)$kh));

			$v = $this->escape(hex2bin((string)$vh));

			if($type[$kq]["DATA_TYPE"] == "bit"){

				$sfK[] = $k."=b".$v." ";
			}
			elseif(in_array($type[$kq]["DATA_TYPE"], $this->GT["binary"])){

				$sfK[] = $k."=x".$v." ";
			}
			elseif(in_array($kq, $tk)){

				$sfK[] = $k."=".$v." ";
			}
			else{ return; }
		}

		if($action === "_DELETE_RC")
		{
			$this->request("DELETE FROM ".$_SHS.".".$_TBS." WHERE ".implode(" AND ", $sfK).";", "", [], __LINE__);

			return;
		}

		foreach($field as $kh=>$v)
		{
			$kq = hex2bin((string)$kh);
			$k = $this->esN(hex2bin((string)$kh));

			if(in_array($kh, $list_rw))
			{
				$PRE = "";

				if($type[$kq]["DATA_TYPE"] === "bit"){

					$PRE = "b";
				}

				if(in_array($type[$kq]["DATA_TYPE"], $this->GT["blob"]))
				{
					if($action === "_UPDATE_RC")
					{
						if(isset($file[$kh])){

							$sfV[] = $k."=x'".bin2hex(base64_decode((string)$file[$kh]))."'";
						}
						elseif(isset($text[$kh])){

							$sfV[] = $k."=x'".bin2hex((string)$text[$kh])."'";
						}
					}
					elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
					{
						if(isset($file[$kh])){

							$sfC[] = $k;
							$sfV[] = "x'".bin2hex(base64_decode((string)$file[$kh]))."'";
						}
						elseif(isset($text[$kh])){

							$sfC[] = $k;
							$sfV[] = "x'".bin2hex((string)$text[$kh])."'";
						}
						elseif($action === "_INSERT_RC")
						{
							$sfC[] = $k;
							$sfV[] = "NULL";
						}
						elseif($action === "_COPY_RC")
						{
							$result = $this->request("SELECT ".$k." FROM ".$_SHS.".".$_TBS." WHERE ".implode(" AND ", $sfK)." LIMIT 1;", "", [], __LINE__);

							if($result[0]){

								$res = $this->fetch_assoc($result[1]);

								if($res){

									$sfC[] = $k;
									$sfV[] = "x'".bin2hex((string)$res[$kq])."'";
								}
							}
						}
					}
				}
				elseif(in_array($type[$kq]["DATA_TYPE"], $this->GT["text"]))
				{
					if($action === "_UPDATE_RC")
					{
						if(isset($file[$kh])){

							$sfV[] = $k."=".$this->escape($file[$kh]);
						}
						elseif(isset($function[$kh]) && ($function[$kh] !== "")){

							$sfV[] = $k."=(".stripslashes($text[$kh]).")";
						}
						elseif(isset($text[$kh])){

							$sfV[] = $k."=".$this->escape($text[$kh]);
						}
					}
					elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
					{
						if(isset($file[$kh])){

							$sfC[] = $k;
							$sfV[] = $this->escape($file[$kh]);
						}
						elseif(isset($function[$kh]) && ($function[$kh] !== "")){

							$sfC[] = $k;
							$sfV[] = "(".stripslashes($text[$kh]).")";
						}
						elseif(isset($text[$kh])){

							$sfC[] = $k;
							$sfV[] = $this->escape($text[$kh]);
						}
					}
				}
				elseif(in_array($type[$kq]["DATA_TYPE"], $this->GT["binary"]))
				{
					if($action === "_UPDATE_RC")
					{
						if(($file[bin2hex((string)$kq)] === $v) && ($v !== "")){

							$sfV[] = $k."=x'".$v."'";
						}
						elseif(isset($function[$kh]) && ($function[$kh] !== "")){

							$sfV[] = $k."=(".stripslashes($v).")";
						}
						else{

							$sfV[] = $k."=x'".bin2hex((string)$v)."'";
						}
					}
					elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
					{
						$sfC[] = $k;

						if($file[bin2hex((string)$kq)] === $v){

							$sfV[] = "x'".$v."'";
						}
						elseif(isset($function[$kh]) && ($function[$kh] !== "")){

							$sfV[] = "(".stripslashes($v).")";
						}
						else{

							$sfV[] = $this->escape($v);
						}
					}
				}
				elseif(in_array($type[$kq]["DATA_TYPE"], $this->GT["geo"]))
				{
					if($action === "_UPDATE_RC")
					{
						if($v != ""){

							$sfV[] = $k."=ST_GeomFromText('".$v."')";
						}
						else{

							$sfV[] = $k."=NULL";
						}
					}
					elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
					{
						$sfC[] = $k;

						if($v != ""){

							$sfV[] = "ST_GeomFromText('".$v."')";
						}
						else{

							$sfV[] = "NULL";
						}
					}
				}
				elseif(in_array($kq, $tk) && ($v !== ""))
				{
					if($action === "_UPDATE_RC")
					{
						if(isset($function[$kh]) && ($function[$kh] !== "")){

							$sfV[] = $k."=(".stripslashes($v).")";
						}
						else{

							$sfV[] = $k."=".$PRE."".$this->escape($v);
						}
					}
					elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
					{
						$sfC[] = $k;

						if(isset($function[$kh]) && ($function[$kh] !== "")){

							$sfV[] = "(".stripslashes($v).")";
						}
						else{

							$sfV[] = $PRE.$this->escape($v);
						}
					}
				}
				elseif(in_array($kq, $tk) && ($v === ""))
				{
					if(in_array($type[$kq]["DATA_TYPE"], $this->GT["char"]))
					{
						if($action === "_UPDATE_RC"){

							$sfV[] = $k."=''";
						}
						elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
						{
							$sfC[] = $k;
							$sfV[] = "''";
						}
					}
					else
					{
						if($action === "_UPDATE_RC"){

							$sfV[] = $k."=NULL";
						}
						elseif(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
						{
							$sfC[] = $k;
							$sfV[] = "NULL";
						}
					}
				}
				else{ return; }
			}
			else
			{
				if($action === "_UPDATE_RC"){

					$sfV[] = $k."=DEFAULT ";
				}
			}
		}

		if(($action === "_COPY_RC") || ($action === "_INSERT_RC"))
		{
			$this->request("INSERT INTO ".$_SHS.".".$_TBS." (".implode(", ", $sfC).") VALUES (".implode(", ", $sfV).");", "", [], __LINE__);
		}
		else
		{
			if((count($sfV) !== 0) && (count($sfK) !== 0))
			{
				$this->request("UPDATE ".$_SHS.".".$_TBS." SET ".implode(", ", $sfV)." WHERE ".implode(" AND ", $sfK)." LIMIT 1;", "", [], __LINE__);
			}
		}

		if(($_SHS === "mysql") && (($_TBS === "user") || ($_TBS === "sh") || ($_TBS === "tables_priv") || ($_TBS === "columns_priv")))
		{
			$this->request("FLUSH PRIVILEGES;", "", [], __LINE__);
		}
	}

	public function delete_rc_filter($nv)
	{
		$_SHS = $this->esN(hex2bin($nv["_SH"]));
		$_TBS = $this->esN(hex2bin($nv["_TB"]));

		if(!in_array($_SHS, $this->DS))
		{
			$RT[] = $this->rc( $nv, "delete" );

			$WHERE = $this->get_wr($nv, $RT[0]["FIELDS"]);

			if($WHERE !== ""){$WHERE = " WHERE ".$WHERE;}

			$this->request("DELETE FROM ".$_SHS.".".$_TBS." ".$WHERE.";", "", [], __LINE__);
		}
	}

	private function get_wr($nv, $field)
	{
		$WA = [];

		foreach($nv["fl_value_rc"] as $k=>$v)
		{
			if(isset($field[$nv["fl_field_rc"][$k]]) || (count($field) === 0))
			{
				if($nv["fl_operator_rc"][$k] !== "")
				{
					$nvt["fl_field_rc"] = $nv["fl_field_rc"][$k];
					$nvt["fl_value_rc"] = $nv["fl_value_rc"][$k];
					$nvt["fl_operator_rc"] = $nv["fl_operator_rc"][$k];

					$WA[] = $nv["fl_and_rc"][$k];
					$WA[] = $this->get_wra($nvt, $field);
				}
			}
		}

		if(isset($WA[0]) && in_array($WA[0], ["AND","OR"])){

			array_shift($WA);
		}

		return implode(" ", $WA);
	}


	private function get_wra($nvt, $field)
	{
		$WHERE = "";

		if($nvt["fl_operator_rc"] === "LIKE %...%"){

			$nvt["fl_operator_rc"] = "LIKE";
			$nvt["fl_value_rc"] = $this->escape("%".$nvt["fl_value_rc"]."%");
		}
		elseif($nvt["fl_operator_rc"] === "NOT LIKE %...%"){

			$nvt["fl_operator_rc"] = "NOT LIKE";
			$nvt["fl_value_rc"] = $this->escape("%".$nvt["fl_value_rc"]."%");
		}
		elseif(($nvt["fl_operator_rc"] === "IS NULL") || ($nvt["fl_operator_rc"] === "IS NOT NULL"))
		{
			$nvt["fl_value_rc"] = "";
		}
		elseif($nvt["fl_operator_rc"] !== ""){

			$nvt["fl_value_rc"] = "'".addslashes($nvt["fl_value_rc"])."'";
		}


		$fl_field_rc = $this->esN($nvt["fl_field_rc"]);

		if(count($field) === 0)
		{
			$WHERE .= " ".$fl_field_rc." ".$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."";
		}
		else
		{
			if(($nvt["fl_operator_rc"] !== "") && in_array($field[$nvt["fl_field_rc"]]["DATA_TYPE"], $this->GT["bit"]))
			{
				$WHERE .= " LPAD(BIN(".$fl_field_rc."), ".$field[$nvt["fl_field_rc"]]["NUMERIC_PRECISION"].", '0') ".$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."";
			}
			else
			{
				$WHERE .= " ".$fl_field_rc." ".$nvt["fl_operator_rc"]." ".$nvt["fl_value_rc"]."";
			}
		}

		return $WHERE;
	}


	private function init_connect($SERVER)
	{
		$this->dbc = mysqli_init();

		if(!isset($SERVER["pass"])){

			$SERVER["pass"] = "";
		}

		if(!isset($SERVER["port"]) || ($SERVER["port"] === "")){

			$SERVER["port"] = NULL;
		}

		if(!isset($SERVER["socket"]) || ($SERVER["socket"] === "")){

			$SERVER["socket"] = NULL;
		}

		if((isset($SERVER["ssl-key"]) && ($SERVER["ssl-key"] !== "")) && (isset($SERVER["ssl-cert"]) && ($SERVER["ssl-cert"] !== "")) &&
			(isset($SERVER["ssl-ca"]) && ($SERVER["ssl-ca"] !== "")))
		{
			$this->dbc->ssl_set($SERVER["ssl-key"], $SERVER["ssl-cert"], $SERVER["ssl-ca"], NULL, NULL);
		}

		$CLIENT_SSL = 0;
		if(isset($SERVER["require_secure_transport"]) && $SERVER["require_secure_transport"]){

			$CLIENT_SSL = MYSQLI_CLIENT_SSL;
		}

		mysqli_report(MYSQLI_REPORT_OFF);

		try
		{
			$this->dbc->real_connect(
				$SERVER["host"], $SERVER["user"], $SERVER["pass"], "",
				$SERVER["port"], $SERVER["socket"], $CLIENT_SSL);

			if(!mysqli_connect_errno())
			{
				if(isset($SERVER["variables"]) && (count($SERVER["variables"]) !== 0)){

					foreach($SERVER["variables"] as $k=>$v){

						if(strtolower($k) === "names"){

							$this->set_charset($v);
						}
						else{

							if(is_int($v)){

								$this->request("SET ".$k." = ".$v.";", "" , [], __LINE__, false);
							}
							else{

								$this->request("SET ".$k." = '".$v."';", "" , [], __LINE__, false);
							}
						}
					}
				}

				$this->character_name = $this->dbc->character_set_name();

				$sql_mode = $this->request("SELECT @@session.sql_mode","" , [], __LINE__, false);
				if($sql_mode[0]){

					$this->sql_mode = $this->fetch_row($sql_mode[1])[0];
				}

				$this->client_info = $this->dbc->client_info;
				$this->server_info = $this->dbc->server_info;

				$this->current_user	= $SERVER["user"]."@".$SERVER["host"].":".$SERVER["port"];

				return true;
			}
		}
		catch (Exception $e) {

			$this->_RS["MESSAGE"][] = "Errno: [".$this->dbc->errno."]. '".$e->getMessage()."'}";
		}

		return false;
	}


	public function connect($SERVER)
	{
		if(!extension_loaded("mysqli"))
		{
			$this->connect = true;

			$this->_RS["MESSAGE"]["connect"] = _MESSAGE_PL_MYSQLI;

			return;
		}

		if(!$this->init_connect($SERVER))
		{
			$this->connect = true;

			$this->_RS["MESSAGE"]["connect"] =_MESSAGE_CONNECTION;
		}
	}


	private function fetch_assoc($result)
	{
		try {

			return $result->fetch_assoc();
		}
		catch (Exception $e) {

			return false;
		}
	}


	private function fetch_row($result)
	{
		try {

			return $result->fetch_row();
		}
		catch (Exception $e) {

			return false;
		}
	}


	private function set_charset($charset)
	{
		$this->dbc->set_charset($charset);
	}


	private function request($sql, $type, $value, $line, $log = true)
	{
		try {

			if($type !== "")
			{
				$stm = $this->dbc->prepare($sql);

				$stm->bind_param($type, ...$value);
				$stm->execute();
				$result = $stm->get_result();
			}
			else
			{
				$this->dbc->real_query($sql);
				$result = $this->dbc->store_result();
			}

			if($this->dbc->error){

				$this->_RS["MESSAGE"][] = "Error ".$this->dbc->errno.": ".$this->dbc->error;

				return [false, $this->dbc->errno];
			}
		}
		catch (Exception $e) {

				$this->_RS["MESSAGE"][] = "Errno: [".$this->dbc->errno."]. '".$e->getMessage()."'}";

				return [false, $this->dbc->errno];
		}

		return [true, $result];
	}

	private function multi_request($script)
	{
		try
		{
			$i = 0;

			if( $this->dbc->multi_query( $script ) )
			{
				do{

					$ST = "";

					if ($result = $this->dbc->store_result()) {

						while($row = $result->fetch_assoc())
						{
							foreach($row as $k=>$v){

								$ST .= (string)$k.": ".(string)$v."\n";
							}

							$ST .= "\n";
						}
					}

					if ($ST !== ""){

						$this->_RS["RESULT"][] = $ST;
					}

					$i++;
				}
				while ((mysqli_more_results($this->dbc)) ? $this->dbc->next_result() : false);
			}

			if( $this->dbc->errno )
			{
				$this->_RS["MESSAGE"][] = "Query: [".($i + 1)."]. Errno: [".$this->dbc->errno."]. '".$this->dbc->error."'}";
			}
		}
		catch (Exception $e) {

			$this->_RS["MESSAGE"][] = "Query: [".($i + 1)."]. Errno: [".$this->dbc->errno."]. '".$e->getMessage()."'}";
		}
	}

	private function escape($v)
	{
		return "'".$this->dbc->real_escape_string($v)."'";
	}

	private function esN($v, $sql_mode="")
	{
		if($sql_mode === ""){$sql_mode = $this->sql_mode;}

		if($v !== ""){

			if(preg_match("/ANSI_QUOTES/", $sql_mode)){

				return '"'.str_replace('"','""',$v).'"';
			}
			else{

				return '`'.str_replace('`','``',$v).'`';
			}
		}
		else{return '';}
	}
}
