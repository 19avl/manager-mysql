<?php

/*
Copyright (c) 2018-2019 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();


Class View
{

	public function __construct(){}

	public function alt_message()
	{
		$this->div_open("id_alt_message", "altDialog", "display: none;", "");
		$this->div("id_alt_message_text", "altDialog_text", "", "", "");
		$this->btn("", "altDialog_btn", _MESSAGE_CONFIRM_YES, "onclick=\"ms.el_view('id_alt_message', 'none');\"");
		$this->div_close();
	}


	private function confirm($id)
	{
		$this->input("", $id."_request", "", "", "", "hidden", "");

		$this->div_open($id, "confirmDialog", "display: none;", "");
		$this->div_open("", "", "", "");
		$this->div("", "confirmDialog_title", "", _MESSAGE_CONFIRM, "");
		$this->div($id."_text", "confirmDialog_text", "", "", "");
		$this->btn("", "confirmDialog_btn", _MESSAGE_CONFIRM_YES,
			"onclick=\"ms.RF('', '', '', this.form, 0); ms.el_view('".$id."', 'none');\"");
		$this->btn("", "confirmDialog_btn", _MESSAGE_CONFIRM_NO, "onclick=\"ms.el_view('".$id."', 'none'); \"");
		$this->div_close();
		$this->div_close();
	}


	public function message($log)
	{
		if(isset($log["MESSAGE"]))
		{
			$this->div_open("div_message", "res", "", "");

			foreach($log["MESSAGE"] as $value){

				$this->div("", "message", "", $value, "");
			}

			$this->div_close();

			$this->div("", "separator11", "", "", "");
		}

		if(isset($log["RESULT"]))
		{
			$this->div_open("div_result", "res", "", "");

			foreach($log["RESULT"] as $value){

				$this->div("", "result", "", $value, "");
			}

			$this->div_close();

			$this->div("", "separator11", "", "", "");
		}
	}


	public function main($_DB, $_TB, $nv, $_sql)
	{
		$this->div("", "nav_main_back", "", "", "");

		$this->div("", "separator22", "", "", "");

		$this->div_open("", "nav_main", "", "");

		$this->form_open( $_DB, "",
			"", "", "", "",
			"", "", "", "",
			"", "", "", "", "nav_main_form");

		$this->btn("", "btn", _NOTE_DATABASE, "onclick=\"ms.view_wr('id_wr_db', 'id_wr_script');\"");

		if($_DB === ""){$vdb = "...";}
		else{$vdb = $this->html(pack('H*', $_DB));}

		$this->input("", "", "int_db", $vdb, "", "readonly", "");

		$this->btn("", "btn", _NOTE_SQL, "onclick=\"ms.view_wr('id_wr_script', 'id_wr_db');\"");

		$this->btn("", "btn", _ACTION_RELOAD, "onclick=\"ms.RF('', '', '".$_TB."', this.form, 0);\"");

		$this->form_close();

		if(($_DB !== "") && ($_TB !== "")){

			$this->form_open("", "",
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				"", "", "", "", "nav_main_form");

			$this->btn("", "btn", _ACTION_BACK, "onclick=\"ms.RF('', '".$_DB."', '', this.form, 0);\"");

			$this->form_close();
		}
		elseif(($_DB !== "") && ($_TB === "")){

			$this->form_open("", "",
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				"", "", "", "",
				"", "", "", "", "nav_main_form");

			$this->btn("", "btn", _ACTION_BACK, "onclick=\"ms.RF('', '', '', this.form, 0);\"");

			$this->form_close();
		}

		$this->div_close();
	}


	public function mk($_DB, $_TB, $LIST_SQL, $nv, $display)
	{
		if($display === "sql"){

			$this->div_open("id_wr_script", "wr_main_nav", "", "");
		}
		else{

			$this->div_open("id_wr_script", "wr_main_nav", "display: none;", "");
		}

			$this->form_open( $_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->input("display", "", "", "sql", "", "hidden", "");

			$this->div_open("", "ct_row", "", "");

				$this->select($LIST_SQL["LIST"], "", "script_id", "", "slc", _NOTE_SCRIPT,
					"onchange=\"ms.RF('', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->div_close();

			$this->form_close();

			$this->form_open( $_DB, "", "", "", "", "", "", "", "", "", "", "", "", "");

			$this->textarea("script", "script_text", "", $LIST_SQL["SCRIPT"], "");

			$this->btn("", "btn", _ACTION_RUN, "onclick=\"ms.RF('_RUN_SQL', '', '".$_TB."', this.form, 0);\"");

			$this->form_close();

		$this->div_close();

		$this->div("", "separator11", "", "", "");
	}


	public function db($RT, $_DB, $_TB, $nv, $display)
	{
		if($display === "db"){

			$this->div_open("id_wr_db", "wr_main_nav", "", "");
		}
		else{

			$this->div_open("id_wr_db", "wr_main_nav", "display: none;", "");
		}

			$this->form_open($_DB, $_TB,
				"", "", "", "",
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->input("display", "", "", "db", "", "hidden", "");

			$this->nav($RT, $nv, "db");

			$this->form_close();

			if( count($RT["DB"]) !== 0 )
			{

			$this->div("", "separator11", "", "", "");

			$this->form_open($_DB, "",
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				"", "", "", "", "", "", "", "");

			$this->input("tb_name", "", "", "", "", "hidden", "");
			
			$this->div_open("", "ct_row", "", "");

			$this->input("", "", "ct_pre", "&nbsp;", "", "disabled", "");
			$this->input("", "", "ct_name_title", _NOTE_DATABASE, "", "disabled", "");

			$this->input("", "", "ct_info_A", "ROWS", "", "readonly", "");

			$this->input("", "", "ct_info_D", $RT["FIELD_SE"][$nv["field_db"]], "", "readonly", "");

			$this->div_close();

			foreach($RT["DB"] as $key=>$value)
			{
				$uk = unpack('H*', "$key")[1];

				$this->div_open("", "ct_row", "", "");

				$this->checkbox("list_db[]", "", "ct_check", $uk, "onclick=\"ms.el_view('id_alt_message', 'none');\"", "");

				$this->input("", "", "ct_name", $this->html($key),
					"onclick=\"ms.RF('VIEW', '".$uk."', '', this.form, 0);\"", "", "");

				$this->input("", "", "ct_info_A", $value["COUNT"], "", "readonly", "");

				$this->input("", "", "ct_info_D", $value[$RT["FIELD_SE"][$nv["field_db"]]], "", "readonly", "");

				$this->div_close();
			}

			$this->div_open("", "ct_row", "", "");

			$this->checkbox("total", "", "ct_check", "checkbox",
				"onclick=\"ms.check_sl(this.form,'list_db[]',this.checked); ms.el_view('id_alt_message', 'none');\"", "");

			$this->confirm("id_cn_db");

			$this->select(
				[
				'_DELETE_DB'=>_ACTION_DELETE,
				'_CLEAR_DB'=>_ACTION_CLEAR
				],
				false, "list_A_sl", "", "st_select_value", _NOTE_SELECT,
					"onchange=\"ms.AL(this.value, 'id_cn_db', 'id_cn_db_request', '', this, this.form, 'list_db[]',
					'"._NOTE_DATABASE." / "._NOTE_SELECT." / ', ['_DELETE_DB','_CLEAR_DB'], [], '"._MESSAGE_DB_CHECK."' ); \"",
				function($k, $v){return $v;},
				function($k, $v){return $k;},
				function($k, $v){return $v;});

			$this->div_close();

			$this->div("", "separator11", "", "", "");

			$this->form_close();

			}

		$this->div_close();
	}


	public function info($info)
	{
		foreach($info as $value){

			$this->div("", "", "", $value, "");
		}

		$this->div("", "separator11", "", "", "");
	}


	public function stat($dbc)
	{
		$this->div("", "", "", $dbc->stat, "");
		$this->div("", "separator11", "", "", "");
	}


	public function tb($_DB, $RT, $action, $nv)
	{
		if($RT["CREATE"] == ""){return;}

		$this->div("", "separator11", "", "", "");

		$this->form_open("", "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			"", "", "", "", "", "", "", "");

		$this->input("list_db[]", "", "", $_DB, "", "hidden", "");

		$this->div_open("", "pl_el", "", "");

		$this->input("cl_in", "search_db", "st_value_table", "",
			"onclick=\"ms.el_view('id_alt_message', 'none');\"", "", "");

		$this->btn("", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_DB', '".$_DB."', '', this.form, 0, 'search_db', '"._MESSAGE_NOT_VALUE."');\"");

		$this->div_close();

		$this->form_close();

		$this->div("", "separator11", "", "", "");

		$this->div_open("", "res", "", "");

		$this->div("", "", "", $this->html($RT["CREATE"]), "");

		if(count($RT["TRIGGERS"]) !== 0){

			$this->div("", "separator2", "", "", "");

			foreach($RT["TRIGGERS"] as $v){

				$this->div("", "separator2", "", "", "");

				$this->div("", "run_tr", "", $this->html($v), "");
			}
		}

		if(count($RT["PROCEDURE"]) !== 0){

			$this->div("", "separator2", "", "", "");

			foreach($RT["PROCEDURE"] as $v){

				$this->div("", "separator2", "", "", "");

				$this->div("", "run_tr", "", $this->html($v),"");
			}
		}

		if(count($RT["FUNCTION"]) !== 0){

			$this->div("", "separator2", "", "", "");

			foreach($RT["FUNCTION"] as $v){

				$this->div("", "separator2", "", "", "");

				$this->div("", "run_tr", "", $this->html($v), "");
			}
		}

		if(count($RT["EVENTS"]) !== 0){

			$this->div("", "separator2", "", "", "");

			foreach($RT["EVENTS"] as $v){

				$this->div("", "separator2", "", "", "");

				$this->div("", "run_tr", "", $this->html($v), "");
			}
		}

		$this->div_close();

		$this->div("", "separator11", "", "", "");

		$this->form_open($_DB, "", "", "", "", "", "", "", "", "", "", "", "", "");

		$this->nav($RT, $nv, "tb");

		$this->form_close();

		$this->div("", "separator11", "", "", "");

		if( count($RT["TABLES"]) === 0 ){return;}

		$this->form_open($_DB, "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", "");

		$this->div_open("", "ct_row", "", "");

		$this->input("", "", "ct_pre", "&nbsp;", "", "disabled", "");
		$this->input("", "", "ct_name_title", _NOTE_TABLE, "", "disabled", "");

		$this->input("", "", "ct_info_A", "ROWS", "", "readonly", "");

		$this->input("", "", "ct_info_D", $RT["FIELD_SE"][$nv["field_tb"]], "", "readonly", "");

		$this->div_close();

		foreach($RT["TABLES"] as $key=>$value)
		{
			$uk = unpack('H*', "$key")[1];

			$this->div_open("", "ct_row", "", "");

			$this->checkbox("list_tb[]", "", "ct_check", $uk, "onclick=\"ms.el_view('id_alt_message', 'none');\"", "");

			$this->input("", "", "ct_name", $this->html($key),
				"onclick=\"ms.RF('VIEW', '', '".$uk."', this.form, 0);\"", "", "");

			$this->input("", "", "ct_info_A", $value["COUNT"], "", "readonly", "");

			$this->input("", "", "ct_info_D", $value[$RT["FIELD_SE"][$nv["field_tb"]]], "", "readonly", "");

			$this->div_close();
		}

		$this->div_open("", "ct_row", "", "");

		$this->checkbox("total", "", "ct_check", "checkbox",
			"onclick=\"ms.check_sl(this.form,'list_tb[]',this.checked); ms.el_view('id_alt_message', 'none');\"", "");

		$this->confirm("id_cn_tbr");

		$this->select(
			[
				'_DELETE_TB'=>_ACTION_DELETE,
				'_CLEAR_TB'=>_ACTION_CLEAR,
				'_EXPORT_TB'=>_ACTION_EXPORT
			],
			false, "list_A_sl", "", "st_select_value", _NOTE_SELECT,
			"onchange=\"ms.AL(this.value, 'id_cn_tbr', 'id_cn_tbr_request', '', this, this.form, 'list_tb[]',
			'"._NOTE_TABLE." / "._NOTE_SELECT." / ', ['_DELETE_TB','_CLEAR_TB'], ['_EXPORT_TB'], '"._MESSAGE_TB_CHECK."' ); \"",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->div_close();

		$this->form_close();

		$this->div("", "separator11", "", "", "");
	}


	public function rc($_DB, $_TB, $RT, $nv, $exceptions)
	{
		if($RT["CREATE"] == ""){return;}

		$this->div("", "separator11", "", "", "");

		$this->form_open($_DB, "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", "");

		$this->input("list_tb[]", "", "", $_TB, "", "hidden", "");

		$this->div_open("", "pl_el", "", "");

		$this->input("cl_in", "search_tb", "st_value_table", "",
			"onclick=\"ms.el_view('id_alt_message', 'none');\"", "", "");

		$this->btn("", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_TB', '', '".$_TB."', this.form, 0, 'search_tb', '"._MESSAGE_NOT_VALUE."');\"");

		$this->div_close();

		$this->form_close();

		$this->div("", "separator11", "", "", "");

		if($RT["VIEW"]){	
		

			$this->div("", "res", "", $this->html($RT["CREATE"], "`,`", "`,<br>`")."<br><br>", "");	
		}
		else
		{
			$this->div("", "res", "", $this->html($RT["CREATE"], "\n", "<br>")."<br><br>", "");	
			
			$this->div("", "separator11", "", "", "");

			$this->form_open($_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->confirm("id_cn_st");

			$this->div_open("", "pl_el", "", "");			
			
				$this->input("name_new", "", "st_fn_value", $this->html(pack('H*', $_TB)), "", "", "");

				$this->btn("", "std_btn", _ACTION_RENAME,
				"onclick=\"ms.AT('_RENAME_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
					'"._NOTE_TABLE." / "._ACTION_RENAME."', ['_RENAME_TB'], []); \"");

				$this->btn("", "std_btn", _ACTION_COPY,
				"onclick=\"ms.AT('_COPY_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
					'"._NOTE_TABLE." / "._ACTION_COPY."', [], []); \"");				
				
				$this->select($RT["FIELD_ST"], false, "cl_del_sl", "", "st_select_value", _ACTION_COLUMN_DELETE,
					"onchange=\"ms.AT('_DELETE_FL', 'id_cn_st', 'id_cn_st_request', 'cl_del', this, this.form,
					' / "._ACTION_COLUMN_DELETE."', ['_DELETE_FL','_UPDATE_FL'], []); \"",
				function($k, $v){return $v;},
				function($k, $v){return unpack('H*', "$v")[1];},
				function($k, $v){return $this->html($v);});

				$this->input("cl_def", "cl_def_id", "st_value_field", "",
					"onclick=\"ms.el_view('id_alt_message', 'none');\"", "", _TITLE_DEF_COLUMN);

				$this->select($RT["FIELD_ST"], false, "cl_change_sl", "", "st_select_value", _ACTION_COLUMN_CHANGE,
					"onchange=\"ms.AT('_UPDATE_FL', 'id_cn_st', 'id_cn_st_request', 'cl_change', this, this.form,
					' / "._ACTION_COLUMN_CHANGE."', ['_DELETE_FL','_UPDATE_FL'], [], 'cl_def_id', '"._MESSAGE_NOT_VALUE."'); \"",
				function($k, $v){return $v;},
				function($k, $v){return unpack('H*', "$v")[1];},
				function($k, $v){return $this->html($v);});

				$this->select(array_merge([""], $RT["FIELD_ST"]), false, "cl_in_sl", "", "st_select_value", _ACTION_COLUMN_INSERT,
					"onchange=\"ms.AT('_INSERT_FL', 'id_cn_st', 'id_cn_st_request', 'cl_in', this, this.form,
					' / "._ACTION_COLUMN_INSERT."', ['_DELETE_FL','_UPDATE_FL'], [], 'cl_def_id', '"._MESSAGE_NOT_VALUE."'); \"",
				function($k, $v){return $v;},
				function($k, $v){return unpack('H*', "$v")[1];},
				function($k, $v){return  ($v === "") ? "first ".$this->html($v) : "after ".$this->html($v);});

				$this->input("tb_def", "tb_def_id", "st_value_table", "",
					"onclick=\"ms.el_view('id_alt_message', 'none');\"", "", _TITLE_DEF_TABLE);

				$this->btn("", "std_btn", _ACTION_TABLE_CHANGE,
				"onclick=\"ms.AT('_UPDATE_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
					'"._ACTION_TABLE_CHANGE."', ['_UPDATE_TB'], [], 'tb_def_id', '"._MESSAGE_NOT_VALUE."'); \"");
					
			$this->div_close();

			$this->form_close();					

			$this->div("", "separator11", "", "", "");
		
			$this->form_open($_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				"", "", "", "");

			$this->nav($RT, $nv, "rc");

			$this->form_close();

			$this->div("", "separator11", "", "", "");
		}

		$this->rc_data($_DB, $_TB, $RT, $nv, $exceptions);
	}


	private function rc_data($_DB, $_TB, $RT, $nv, $exceptions)
	{
		$count = "0";

		foreach($RT["RECORDS"] as $value)
		{
			$count += 1;

			$this->form_open($_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$count_fl = 0;
			$count_fl_display = 0;

			foreach($value as $k=>$v)
			{
				$count_fl += 1;

				if(($nv["field_rc"] == 0) || ($nv["field_rc"] > count($RT["FIELD_SE"])) ||
					($RT["FIELD_SE"][$nv["field_rc"]] == "field: ".$RT["FIELDS"][$k]["COLUMN_NAME"]) ||
					($RT["FIELD_SE"][$nv["field_rc"]] == "key: ".$RT["FIELDS"][$k]["COLUMN_KEY"]) ||
					($RT["FIELD_SE"][$nv["field_rc"]] == "type: ".$RT["FIELDS"][$k]["DATA_TYPE"])){

					$this->div_open("", "pl_el", "", "");
					$count_fl_display += 1;
				}
				else{ $this->div_open("", "", "display: none;", ""); }

				$uk = unpack('H*', "$k")[1];

				if($RT["FIELDS"][$k]["COLUMN_KEY"] == "PRI"){

					$this->input("key[".$uk."]", "", "", unpack('H*', "$v")[1], "", "hidden", "");
				}

				$this->input("", "", "rt_label_key", $this->html($RT["FIELDS"][$k]["COLUMN_KEY"]), "", "disabled", "");

				$this->input("", "", "rt_label_name", $this->html("$k"), "", "disabled", "");

				$this->div_open("", "", "display:inline-block;", "");

				if(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
					(($RT["FIELDS"][$k]["COLUMN_KEY"] === "MUL") && $RT["FIELDS"][$k]["FOREIGN"]))
				{

					$this->div_open("com".$count.$count_fl.$uk, "", "display: none;", "");

					$this->div_open("", "type_value", "", "");

					$this->div_open("com_sl".$count.$count_fl.$uk, "type_value_sl", "", "");

					foreach($RT["FIELDS"][$k]["COLUMN_VALUE"] as $vst){

						$this->div_open("", "type_value_sl_k", "", "");

						if(($RT["FIELDS"][$k]["COLUMN_KEY"] == "MUL")){

							$checked = ($v === $vst) ? "checked" : "";

							$this->radio("tv".$count.$count_fl.$uk, "", "", $this->html($vst), "", $checked);
						}
						if(($RT["FIELDS"][$k]["DATA_TYPE"] == "enum")){

							$checked = ($v === $vst) ? "checked" : "";

							$this->radio("tv".$count.$count_fl.$uk, "", "", $this->html($vst), "", $checked);
						}
						if($RT["FIELDS"][$k]["DATA_TYPE"] == "set"){

							$checked = (strpos($v, $vst) !== false) ? "checked" : "";

							$this->checkbox("", "", "", $this->html($vst), "", $checked);
						}

						print $this->html($vst);

						$this->div_close();

						$this->div("", "separator2", "", "", "");
					}

					$this->div_close();

					$this->btn("", "btn", _MESSAGE_CONFIRM_YES,
						"onclick=\"ms.get_stp('".$count.$uk."', 'com_sl".$count.$count_fl.$uk."', 'com".$count.$count_fl.$uk."');\"");

					$this->btn("", "btn", _MESSAGE_CONFIRM_NO,
						"onclick=\"ms.el_view('com".$count.$count_fl.$uk."', 'none');\"");

					$this->div_close();
					$this->div_close();

					$this->input("", "", "rt_select_type", $RT["FIELDS"][$k]["DATA_TYPE"]."...",
						"onclick=\"ms.el_open_com('com".$count.$count_fl.$uk."');\"", "readonly", "");
				}
				else{

					$this->input("", "", "rt_label_type",
						$this->html($RT["FIELDS"][$k]["COLUMN_TYPE"]), "", "disabled", "");
				}

				$this->div_close();

				if($RT["EXCEPT"])
				{
					$this->input("", "", "rt_value_input_disabled", $this->html($v), "", "disabled", "");
				}
				elseif(preg_match("/text$/", $RT["FIELDS"][$k]["DATA_TYPE"]) ||
					preg_match("/blob$/", $RT["FIELDS"][$k]["DATA_TYPE"])){

					$this->btn("", "btn_text", "&#8597&nbsp;", 
						"onclick=\"ms.view_text('text".$count.$count_fl.$uk."');\"");	

					$this->textarea("field[".$uk."]", "text".$count.$count_fl.$uk, "rt_value_text", $this->html($v),
						"onchange=\"ms.check_change(this);\"");
				}
				else{

					$flag = "";
					$class = "rt_value_input";

					if(($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
						($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP")){

						$flag = "disabled";
						$class = "rt_value_input_disabled";
					}
					elseif(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") || ($RT["FIELDS"][$k]["DATA_TYPE"] === "set"))
					{
						$flag = "readonly";
					}

					$this->input("field[".$uk."]", $count.$uk, $class, $this->html($v),
						"onchange=\"ms.check_change(this);\"", $flag, "");
				}

				$this->div_close();
			}

			$this->confirm("id_cn_rc".$count.$uk);

			$this->div("", "separator2", "", "", "");

			if(!$RT["EXCEPT"] && $RT["PRI"] && ($count_fl > 0) && ($count_fl_display > 0))
			{
				if($RT["COUNT"] !== 0)
				{
					$this->btn("", "btn", _ACTION_DELETE,
						"onclick=\"ms.AT('_DELETE_RC', 'id_cn_rc".$count.$uk."', 'id_cn_rc".$count.$uk."_request', '', this, this.form,
						'"._NOTE_RECORD." / "._ACTION_DELETE."', ['_DELETE_RC'], []); \"");

					$this->btn("", "btn", _ACTION_UPDATE, "onclick=\"ms.RF('_UPDATE_RC', '', '', this.form, 0);\"");
				}

				$this->btn("", "btn", _ACTION_INSERT, "onclick=\"ms.RF('_INSERT_RC', '', '',this.form, 0);\"");
			}

			$this->form_close();

			$this->div("", "separator11", "", "", "");
		}
	}


	private function form_open($_DB, $_TB,
		$page_db, $from_db, $order_db, $field_db,
		$page_tb, $from_tb, $order_tb, $field_tb,
		$page_rc, $from_rc, $order_rc, $field_rc, $class="")
	{
		print "<form name='' method='post' action='' class='".$class."' enctype='multipart/form-data' onSubmit='return false;'>";

		print "<input name='action' type='hidden' value=''>";
		print "<input name='bd' type='hidden' value='".$_DB."'>";
		print "<input name='tb' type='hidden' value='".$_TB."'>";

		if($page_db != ""){print "<input name='page_db' type='hidden' value='".$page_db."'>";}
		if($from_db != ""){print "<input name='from_db' type='hidden' value='".$from_db."'>";}
		if($order_db != ""){print "<input name='order_db' type='hidden' value='".$order_db."'>";}
		if($field_db != ""){print "<input name='field_db' type='hidden' value='".$field_db."'>";}

		if($page_tb != ""){print "<input name='page_tb' type='hidden' value='".$page_tb."'>";}
		if($from_tb != ""){print "<input name='from_tb' type='hidden' value='".$from_tb."'>";}
		if($order_tb != ""){print "<input name='order_tb' type='hidden' value='".$order_tb."'>";}
		if($field_tb != ""){print "<input name='field_tb' type='hidden' value='".$field_tb."'>";}

		if($page_rc != ""){print "<input name='page_rc' type='hidden' value='".$page_rc."'>";}
		if($from_rc != ""){print "<input name='from_rc' type='hidden' value='".$from_rc."'>";}
		if($order_rc != ""){print "<input name='order_rc' type='hidden' value='".$order_rc."'>";}
		if($field_rc != ""){print "<input name='field_rc' type='hidden' value='".$field_rc."'>";}

		print "<input name='session' type='hidden' value='1'>";

		print "<input name='request' type='hidden' value=''>";
	}


	private function nav($RT, $nv, $pre)
	{
		$this->div_open("", "nav", "", "");

			$this->div_open("", "nav_wrap_filter", "", "");

				$this->div("", "nav_label", "", _NOTE_FILTER, "");

				$this->div("", "separator0", "", "", "");

				$this->select($RT["FIELD_ST"], $nv["fl_field_".$pre], "fl_field_".$pre, "", "slc", "",
					"onchange=\"ms.RF('', '', '', this.form, 0);\"",
					function($k, $v){return unpack('H*', "$v")[1];},
					function($k, $v){return unpack('H*', "$v")[1];},
					function($k, $v){return $this->html($v);});

				$this->input("fl_value_".$pre, "", "nav_value", $this->html($nv["fl_value_".$pre]), "", "", "value");

				$this->select($RT["FILTER_EX"], $nv["fl_operator_".$pre], "fl_operator_".$pre, "ex_".$pre, "slc", "",
					"onchange=\"ms.RF('', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->div_close();

			$this->div_open("", "nav_wrap", "", "");

				$this->div("", "nav_label", "", _NOTE_LIMIT, "");

				$this->select($RT["ON_PAGE"], $nv["page_".$pre], "page_".$pre, "", "slc", "",
					"onchange=\"ms.set_vl('from_".$pre."', '0'); ms.RF('', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->div_close();

			$this->div_open("", "nav_wrap", "", "");

				$this->div("", "nav_label", "", _NOTE_FROM, "");

				$this->select($RT["FROM"], $nv["from_".$pre], "from_".$pre, "from_".$pre, "slc", "",
					"onchange=\"ms.RF('', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->div_close();

			$this->div_open("", "nav_wrap", "", "");

				$this->div("", "nav_label", "", _NOTE_ORDER_BY, "");

				$this->select($RT["FIELD_ST"], $nv["order_".$pre], "order_".$pre, "", "slc", "",
					"onchange=\"ms.RF('', '', '', this.form, 0);\"",
					function($k, $v){return $k;},
					function($k, $v){return $k;},
					function($k, $v){return $this->html($v);});

			$this->div_close();

			$this->div_open("", "nav_wrap", "", "");

				$this->div("", "nav_label", "", _NOTE_FIELD, "");

				$this->select($RT["FIELD_SE"], $nv["field_".$pre], "field_".$pre, "", "slc", "",
					"onchange=\"ms.RF('', '', '', this.form, 0);\"",
					function($k, $v){return $k;},
					function($k, $v){return $k;},
					function($k, $v){return $this->html($v);});

			$this->div_close();

		$this->div_close();

		$this->div("", "separator11", "", _NOTE_TOTAL." [ ".$RT["COUNT"]." ] ", "");
	}


	private function form_close()
	{
		print "</form>";
	}


	private function div($id, $class, $style, $value, $event)
	{
		print "<div id='".$id."' class='".$class."' style='".$style."' ".$event.">".$value."</div>";
	}


	private function div_open($id, $class, $style, $event)
	{
		print "<div id='".$id."' class='".$class."' style='".$style."' ".$event.">";
	}


	private function div_close()
	{
		print "</div>";
	}


	private function btn($name, $class, $value, $event)
	{
		print "<input type='button' name='".$name."' class='".$class."' value='".$value."' ".$event.">";
	}


	private function input($name, $id, $class, $value, $event, $flag, $placeholder)
	{
		$type = "text";
		if($flag === "hidden"){

			$type = "hidden";
			$flag = "";
		}

		print "<input id='".$id."' name='".$name."' type='".$type."' class='".$class."' value='".$value."' ".
			$event." ".$flag." placeholder='".$placeholder."'>";
	}


	private function checkbox($name, $id, $class, $value, $event, $checked)
	{
		print "<input type='checkbox' name='".$name."' class='".$class."' value='".$value."' ".$event." ".$checked.">";
	}


	private function radio($name, $id, $class, $value, $event, $checked)
	{
		print "<input type='radio' name='".$name."' value='".$value."' ".$event." ".$checked.">";
	}


	private function textarea($name, $id, $class, $value, $event)
	{
		print "<textarea id='".$id."' name='".$name."' class='".$class."' ".$event.">".$value."</textarea>";
	}


	private function select($foreach, $selected, $name, $id, $class, $title, $event, $ch, $fk, $fv)
	{
		print "<select id='".$id."' name='".$name."' class='".$class."' size='1' ".$event.">";

		if($title !== ""){print "<OPTION SELECTED value='' disabled> ".$title." </OPTION>";}

		foreach($foreach as $k=>$v){

			if($selected === (string)call_user_func($ch, $k, $v)){

				print "<OPTION SELECTED value='".call_user_func($fk, $k, $v)."' > ".call_user_func($fv, $k, $v)." </OPTION>";
			}
			else{

				print "<OPTION value='".call_user_func($fk, $k, $v)."' > ".call_user_func($fv, $k, $v)." </OPTION>";
			}
		}

		print "</select>";
	}


	private function html($input, $EL="", $DL="")
	{
		if($EL !==""){
			
			return preg_replace("/".$EL."/", $DL, htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE));
		}
		else{
			
			return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE);
		}
	}


}