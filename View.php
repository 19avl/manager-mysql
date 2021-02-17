<?php

/*
Copyright (c) 2018-2021 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();

Class View
{
	use Convert;
	use Wr_html;

	public function __construct(){}


	public function dl_message()
	{
		$this->tg_open("div", "id_alt_message", "altDialog", "display: none;", "");
		$this->tg("div", "id_alt_message_text", "altDialog_text", "", "", "");
		$this->btn("", "altDialog_btn", _MESSAGE_CONFIRM_YES, "onclick=\"ms.el_va('id_alt_message', 'none');\"");
		$this->tg_close("div");
	}


	private function dl_confirm($id)
	{
		$this->input("", $id."_request", "", "", "", "hidden", "");

		$this->tg_open("div", $id, "confirmDialog", "display: none;", "");
		$this->tg_open("div", "", "", "", "");
		$this->tg("div", "", "confirmDialog_title", "", _MESSAGE_CONFIRM, "");
		$this->tg("div", $id."_text", "confirmDialog_text", "", "", "");
		$this->btn("", "confirmDialog_btn", _MESSAGE_CONFIRM_YES,
			"onclick=\"ms.RF('', '', '', this.form, 0); ms.el_va('".$id."', 'none');\"");
		$this->btn("", "confirmDialog_btn", _MESSAGE_CONFIRM_NO, "onclick=\"ms.el_va('".$id."', 'none'); \"");
		$this->tg_close("div");
		$this->tg_close("div");
	}


	public function message($log)
	{
		if(isset($log["MESSAGE"]))
		{
			$this->tg_open("div", "div_message", "res", "", "");

			foreach($log["MESSAGE"] as $value){

				$this->tg("div", "", "message", "", $value, "");
			}

			$this->tg_close("div");

			$this->tg("div", "", "separator11", "", "", "");
		}

		if(isset($log["RESULT"]))
		{
			$this->tg_open("div", "div_result", "res", "", "");

			foreach($log["RESULT"] as $value){

				$this->tg("div", "", "result", "", $value, "");
			}

			$this->tg_close("div");

			$this->tg("div", "", "separator11", "", "", "");
		}
	}


	public function main($_DB, $_TB, $nv, $_sql)
	{
		$this->tg("div", "", "nav_main_back", "", "", "");

		$this->tg_open("div", "", "nav_main", "", "");

		$this->tg_open("ul", "", "nav_main_sub", "", "");

			$this->tg_open("li", "", "", "", "");

				$this->btn("", "btn_nav_first", "...", "");

				$this->tg_open("ul", "", "", "", "");

					if(($_DB !== "") && ($_TB !== "")){

						$this->form_open("nav_main_form");

						$this->form_set("", "",
							$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
							$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
							"", "", "", []);

						$this->btn("", "btn_nav_sub", _ACTION_BACK, "onclick=\"ms.RF('', '".$_DB."', '', this.form, 0);\"");

						$this->form_close();
					}
					elseif(($_DB !== "") && ($_TB === "")){

						$this->form_open("nav_main_form");

						$this->form_set("", "",
							$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
							"", "", "", "", "", "", "", []);

						$this->btn("", "btn_nav_sub", _ACTION_BACK, "onclick=\"ms.RF('', '', '', this.form, 0);\"");

						$this->form_close();
					}

					$this->form_open("nav_main_form");

					$this->form_set($_DB, "", "", "", "", "", "", "", "", "", "", "", "", []);
					$this->btn("", "btn_nav_sub", _ACTION_RELOAD, "onclick=\"ms.RF('', '', '".$_TB."', this.form, 0);\"");

					$this->form_close();

				$this->tg_close("ul");

			$this->tg_close("li");

			$this->tg_open("li", "", "", "", "");
				$this->btn("", "btn_nav", _NOTE_DATABASE, "onclick=\"ms.view_wr('id_wr_db', 'id_wr_script');\"");
			$this->tg_close("li");

			$this->tg_open("li", "", "", "", "");
				$this->btn("", "btn_nav", _NOTE_SQL, "onclick=\"ms.view_wr('id_wr_script', 'id_wr_db');\"");
			$this->tg_close("li");

		$this->tg_close("ul");

		$this->tg_close("div");
	}


	public function db($RT, $_DB, $_TB, $nv, $display)
	{
		if($display === "db"){

			$this->tg_open("div", "id_wr_db", "wr_main_nav", "", "");
		}
		else{

			$this->tg_open("div", "id_wr_db", "wr_main_nav", "display: none;", "");
		}

		$this->form_open();

		$this->form_set($_DB, $_TB,
			"", "", "", "",
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->input("display", "", "", "db", "", "hidden", "");

		$this->nav($RT, $nv, "db");

		$this->form_close();

		if( count($RT["DB"]) !== 0 )
		{
			$this->tg("div", "", "separator11", "", "", "");

			$this->form_open();

			$this->form_set($_DB, "",
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				"", "", "", "", "", "", "", []);

			$this->tg_open("div", "", "ct_row", "", "");

			$this->tg_open("div", "", "pl_el", "", "");

			$this->checkbox("totalH", "", "ct_check", "checkbox",
				"onclick=\"ms.check_sl(this.form,'list_db[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

			$this->input("", "", "ct_name_title", _NOTE_DATABASE, "", "disabled", "");

			$this->input("", "", "ct_info_A", "ROWS", "", "readonly", "");

			$this->input("", "", "ct_info_D", $RT["FIELD_SE"][$nv["field_db"]], "", "readonly", "");

			$this->tg_close("div");

			foreach($RT["DB"] as $key=>$value)
			{
				$uk = $this->s2h($key);

				$this->tg_open("div", "", "pl_el", "", "");

				$this->checkbox("list_db[]", "", "ct_check", $uk, "onclick=\"ms.el_va('id_alt_message', 'none');\"", "");

				$this->input("", "", "ct_name", $this->html($key),
					"onclick=\"ms.RF('VIEW', '".$uk."', '', this.form, 0);\"", "", "");

				$this->input("", "", "ct_info_A", $value["COUNT"], "", "readonly", "");

				$this->input("", "", "ct_info_D", $value[$RT["FIELD_SE"][$nv["field_db"]]], "", "readonly", "");

				$this->tg_close("div");
			}

			$this->checkbox("totalF", "", "ct_check", "checkbox",
				"onclick=\"ms.check_sl(this.form,'list_db[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

			$this->dl_confirm("id_cn_db");

			$this->select(
				[
				'_DELETE_DB'=>_ACTION_DELETE,
				'_CLEAR_DB'=>_ACTION_CLEAR,
				'_EXPORT_DB'=>_ACTION_EXPORT,
				],
				false, "list_LD_sl", "", "st_select_value", _NOTE_SELECT,
					"onchange=\"ms.AL(this.value, 'id_cn_db', 'id_cn_db_request', '', this, this.form, 'list_db[]',
					'"._NOTE_DATABASE." / "._NOTE_SELECT." / ', ['_DELETE_DB','_CLEAR_DB'],
					['_EXPORT_DB'],
					'"._MESSAGE_DB_CHECK."' ); \"",
				function($k, $v){return $v;},
				function($k, $v){return $k;},
				function($k, $v){return $v;});

			$this->tg_close("div");

			$this->tg("div", "", "separator11", "", "", "");

			$this->form_close();
		}

		$this->tg_close("div");
	}


	public function mk($_DB, $_TB, $LIST_SQL, $nv, $display)
	{
		if($display === "sql"){

			$this->tg_open("div", "id_wr_script", "wr_main_nav", "", "");
		}
		else{

			$this->tg_open("div", "id_wr_script", "wr_main_nav", "display: none;", "");
		}

		$this->form_open();

		$this->form_set($_DB, $_TB,
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->input("display", "", "", "sql", "", "hidden", "");

		$this->tg_open("div", "", "ct_row", "", "");

			$this->select($LIST_SQL["LIST"], "", "script_id", "", "slc", _NOTE_SCRIPT,
				"onchange=\"ms.RF('', '', '', this.form, 0);\"",
				function($k, $v){return $v;},
				function($k, $v){return $v;},
				function($k, $v){return $v;});

		$this->tg_close("div");

		$this->form_close();

		$this->form_open();

		$this->form_set($_DB, "", "", "", "", "", "", "", "", "", "", "", "", []);

		$this->textarea("script", "script_text", "", $LIST_SQL["SCRIPT"], "", "");

		$this->btn("", "btn", _ACTION_RUN, "onclick=\"ms.RF('_RUN_SQL', '', '".$_TB."', this.form, 0);\"");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_close("div");
	}


	public function stat($stat, $SERVER)
	{
		$this->tg("div", "", "", "", "&nbsp;", "");
		$this->tg("div", "", "", "", $stat, "");
		$this->tg("div", "", "", "", "&nbsp;", "");
	}


	public function info($info)
	{
		foreach($info as $value){

			$this->tg("div", "", "", "", $value, "");
		}

		$this->tg("div", "", "separator11", "", "", "");
	}


	public function tb($_DB, $RT, $action, $nv, $display)
	{
		if($RT["CREATE"] == ""){return;}

		$_DBS = $this->html($this->h2s($_DB));

		$this->input("", "", "rt_label_sv", $_DBS, "onclick=\"ms.el_vb('db_id');\"", "readonly", "");

		if($display === "tb_sub"){

			$this->tg_open("div", "db_id", "pl_el", "", "");
		}
		else{

			$this->tg_open("div", "db_id", "pl_el", "display: none;", "");
		}

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg("div", "", "", "", $this->html(substr($RT["CREATE"]["DB"], 7)), "");

		$this->tg("div", "", "separator11", "", "", "");

		$this->form_open();

		$this->form_set($_DB, "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", []);

		$this->input("display", "", "", "tb_sub", "", "hidden", "");

		$this->dl_confirm("id_cn_sub");

		if(count($RT["EVENTS"]) !== 0)
		{
			$this->select(array_keys($RT["EVENTS"]),
				"", "cl_sl[events]", "", "slc", "events",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});
		}

		if(count($RT["TRIGGERS"]) !== 0)
		{
			$this->select(array_keys($RT["TRIGGERS"]),
				"", "cl_sl[triggers]", "", "slc", "triggers",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});
		}

		if(count($RT["PROCEDURE"]) !== 0)
		{
			$this->select(array_keys($RT["PROCEDURE"]),
				"", "cl_sl[procedure]", "", "slc", "procedure",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});
		}

		if(count($RT["FUNCTION"]) !== 0)
		{
			$this->select(array_keys($RT["FUNCTION"]),
				"", "cl_sl[function]", "", "slc", "function",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);;},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});
		}

		$this->input("cl_in", "", "", $RT["SUB"]["NM"], "", "hidden", "");
		$this->input("cl_tr", "sub_name_id", "", $RT["SUB"]["ID"], "", "hidden", "");
		$this->input("cl_dl", "", "", $this->s2h(substr($RT["SUB"]["SL"], 7)), "", "hidden", "");

		if($RT["SUB"]["SL"] !== "")
		{
			$this->tg_open("div", "target_id_edit", "", "", "");

				$this->textarea("cl_df", "target_id", "rt_value_text", substr($RT["SUB"]["SL"], 7),
					"onclick=\"ms.el_va('id_alt_message', 'none');\"", "");

				$this->tg("div", "", "separator3", "", "", "");

				$this->btn("", "st_btn", _ACTION_DELETE,
					"onclick=\"ms.AT('_DELETE_SUB', 'id_cn_sub', 'id_cn_sub_request', '', this, this.form,
					ms.get_sub('sub_name_id','')+' / "._ACTION_DELETE."',
					['_DELETE_SUB'], [], 'target_id', '"._MESSAGE_NOT_VALUE."'); \"");

				$this->btn("", "st_btn", _ACTION_UPDATE,
					"onclick=\"ms.AT('_UPDATE_SUB', 'id_cn_sub', 'id_cn_sub_request', '', this, this.form,
					ms.get_sub('sub_name_id','')+' / "._ACTION_UPDATE."',
					['_UPDATE_SUB'], [], 'target_id', '"._MESSAGE_NOT_VALUE."'); \"");

				$this->btn("", "st_btn", _ACTION_CREATE,
					"onclick=\"ms.AT('_CREATE_SUB', 'id_cn_sub', 'id_cn_sub_request', '', this, this.form,
					ms.get_sub('sub_name_id','')+' / "._ACTION_CREATE."',
					[], [], 'target_id', '"._MESSAGE_NOT_VALUE."'); \"");

			$this->tg_close("div");

			$this->tg_close("div");
		}

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_close("div");

		$this->tg("div", "", "separator11", "", "", "");

		$this->form_open();

		$this->form_set($_DB, "", "", "", "", "", "", "", "", "", "", "", "", []);

		$this->tg_open("div", "", "pl_el", "", "");

		$this->select(
			[_NOTE_SEARCH_M0, _NOTE_SEARCH_M1],
			"", "cl_df", "", "st_select_value", "", "",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->input("cl_in", "search_db", "st_value_D", "",
			"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

		$this->btn("", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_DB', '".$_DB."', '', this.form, 0, 'search_db', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tg_close("div");

		$this->tg("div", "", "separator3", "", "", "");

		$this->nav($RT, $nv, "tb");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");

		if( count($RT["TABLES"]) === 0 ){return;}

		$this->form_open();

		$this->form_set($_DB, "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", []);

		$this->tg_open("div", "", "ct_row", "", "");

		$this->tg_open("div", "", "pl_el", "", "");

		$this->checkbox("totalH", "", "ct_check", "checkbox",
			"onclick=\"ms.check_sl(this.form,'list_tb[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

		$this->input("", "", "ct_name_title", _NOTE_TABLE, "", "disabled", "");

		$this->input("", "", "ct_info_A", "ROWS", "", "readonly", "");

		$this->input("", "", "ct_info_D", $RT["FIELD_SE"][$nv["field_tb"]], "", "readonly", "");

		$this->tg_close("div");

		foreach($RT["TABLES"] as $key=>$value)
		{
			$uk = $this->s2h($key);

			$this->tg_open("div", "", "pl_el", "", "");

			$this->checkbox("list_tb[]", "", "ct_check", $uk, "onclick=\"ms.el_va('id_alt_message', 'none');\"", "");

			$this->input("", "", "ct_name", $this->html($key),
				"onclick=\"ms.RF('VIEW', '', '".$uk."', this.form, 0);\"", "", "");

			$this->input("", "", "ct_info_A", $value["COUNT"], "", "readonly", "");

			$this->input("", "", "ct_info_D", $value[$RT["FIELD_SE"][$nv["field_tb"]]], "", "readonly", "");

			$this->tg_close("div");
		}

		$this->checkbox("totalF", "", "ct_check", "checkbox",
			"onclick=\"ms.check_sl(this.form,'list_tb[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

		$this->dl_confirm("id_cn_tbr");

		$this->select(
			[
				'_DELETE_TB'=>_ACTION_DELETE,
				'_CLEAR_TB'=>_ACTION_CLEAR,
				'_EXPORT_TB'=>_ACTION_EXPORT
			],
			false, "list_LT_sl", "", "st_select_value", _NOTE_SELECT,
			"onchange=\"ms.AL(this.value, 'id_cn_tbr', 'id_cn_tbr_request', '', this, this.form, 'list_tb[]',
			'"._NOTE_TABLE." / "._NOTE_SELECT." / ', ['_DELETE_TB','_CLEAR_TB'],
			['_EXPORT_TB'],
			'"._MESSAGE_TB_CHECK."' ); \"",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->tg_close("div");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");
	}


	public function rc($_DB, $_TB, $RT, $nv, $exceptions, $display)
	{
		$_DBS = $this->html($this->h2s($_DB));
		$_TBS = $this->html($this->h2s($_TB));

		if($RT["CREATE"] == ""){return;}

		$this->input("", "", "rt_label_sv", $_DBS.".".$_TBS, "onclick=\"ms.el_vb('tb_id');\"", "readonly", "");

		$this->tg_open("div", "", "res", "", "");

		$this->tg_close("div");

		$this->tg_open("div", "", "pl_el", "", "");

		if($display === "rc_sub"){

			$this->tg_open("div", "tb_id", "", "", "");
		}
		else{

			$this->tg_open("div", "tb_id", "", "display: none;", "");
		}

		$this->form_open();

		$this->form_set($_DB, $_TB,
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->input("display", "", "", "rc_sub", "", "hidden", "");

		$this->dl_confirm("id_cn_st");

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg("div", "", "", "", $this->html(substr($RT["CREATE"]["DB"], 7), "`,`", "`,<br>`"), "");

		$this->tg("div", "", "separator3", "", "", "");
		$this->tg("div", "", "separator3", "", "", "");

		if($RT["VIEW"]){

			$this->tg("div", "", "res", "", $this->html(substr($RT["CREATE"]["TB"], 7), "`,`", "`,<br>`"), "");
		}
		else{

			$this->tg("div", "", "res", "", $this->html(substr($RT["CREATE"]["TB"], 7), "\n", "<br>"), "");
		}

		$this->tg("div", "", "separator11", "", "", "");

		if(!$RT["VIEW"])
		{
			$this->select($RT["ALTER_TABLE"]["ADD"], false, "", "", "slc", "add",
				"onchange=\"ms.el_va('st_id','');ms.el_va('id_alt_message', 'none');ms.sub_tb('tb_def_id', this); \"",
				function($k, $v){return $v;},
				function($k, $v){return $this->html($v);},
				function($k, $v){return $this->html($v);});


			$this->select($RT["ALTER_TABLE"]["CHANGE"], false, "", "", "slc", "change",
				"onchange=\"ms.el_va('st_id','');ms.el_va('id_alt_message', 'none');ms.sub_tb('tb_def_id', this); \"",
				function($k, $v){return $v;},
				function($k, $v){return $this->html($v);},
				function($k, $v){return $this->html($v);});


			$this->select($RT["ALTER_TABLE"]["DROP"], false, "", "", "slc", "drop",
				"onchange=\"ms.el_va('st_id','');ms.el_va('id_alt_message', 'none');ms.sub_tb('tb_def_id', this); \"",
				function($k, $v){return $v;},
				function($k, $v){return $this->html($v);},
				function($k, $v){return $this->html($v);});

			$this->tg_open("div", "st_id", "", "display: none;", "");

			$this->textarea("cl_df", "tb_def_id", "rt_value_text", "",
				"onclick=\"ms.el_va('id_alt_message', 'none');\"", "");

			$this->btn("", "st_btn", _ACTION_ALTER,
				"onclick=\"ms.AT('_UPDATE_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
				ms.get_sub('','')+'"._NOTE_TABLE." / "._ACTION_ALTER."',
				['_UPDATE_TB'], [], 'tb_def_id', '"._MESSAGE_NOT_VALUE."'); \"");

			$this->tg_close("div");
		}

		$this->tg("div", "", "separator11", "", "", "");

		if($RT["VIEW"])
		{
			$this->input("cl_in", "", "st_value_B", $_TBS, "", "", "");

			$this->btn("", "st_btn", _ACTION_RENAME,
				"onclick=\"ms.AT('_RENAME_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
				'"._NOTE_TABLE." / "._ACTION_RENAME."', ['_RENAME_TB'], []); \"");
		}
		else
		{
			$this->input("cl_in", "name_new_id", "st_value_D", $_TBS,
				"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

			$this->btn("", "st_btn", _ACTION_RENAME,
				"onclick=\"ms.AT('_RENAME_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
				'"._NOTE_TABLE." / "._ACTION_RENAME."',
				['_RENAME_TB'], [], 'name_new_id', '"._MESSAGE_NOT_VALUE."'); \"");

			$this->btn("", "st_btn", _ACTION_COPY,
				"onclick=\"ms.AT('_COPY_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
				'"._NOTE_TABLE." / "._ACTION_COPY."',
				[], [], 'name_new_id', '"._MESSAGE_NOT_VALUE."'); \"");

		}

		$this->tg("div", "", "separator11", "", "", "");

		$this->form_close();

		if(!$RT["VIEW"]){

			$this->rc_data($_DB, $_TB, $RT, $nv, $exceptions, "insert");
		}

		$this->tg_close("div");

		$this->tg_close("div");

		$this->tg("div", "", "separator11", "", "", "");

		$this->form_open();

		$this->form_set($_DB, $_TB,
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", []);

		$this->tg_open("div", "", "pl_el", "", "");

		$this->select(
			[_NOTE_SEARCH_M0, _NOTE_SEARCH_M1],
			"", "cl_df", "", "st_select_value", "", "",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->input("cl_in", "search_tb", "st_value_D", "",
			"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

		$this->btn("", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_TB', '', '".$_TB."', this.form, 0, 'search_tb', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tg_close("div");

		$this->tg("div", "", "separator3", "", "", "");

		$this->nav($RT, $nv, "rc");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");

		if($RT["COUNT"] !== 0){

				$this->rc_data($_DB, $_TB, $RT, $nv, $exceptions, "edit");
		}
	}


	private function rc_data($_DB, $_TB, $RT, $nv, $exceptions, $mod)
	{
		$count = "0";

		if($mod === "insert"){

			$RECORDS = $RT["RECORD_NEW"];
		}
		else{

			$RECORDS = $RT["RECORDS"];
		}

		foreach($RECORDS as $value)
		{
			$count += 1;

			$this->form_open();

			$this->form_set($_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$count_fl = 0;
			$count_fl_display = 0;

			foreach($value as $k=>$v)
			{
				$count_fl += 1;

				if((count($nv["field_rc"]) === 0) || (in_array($this->s2h($k), $nv["field_rc"]))){

						$this->tg_open("div", "", "pl_el", "", "");
						$count_fl_display += 1;
				}
				else{

					$this->tg_open("div", "", "", "display: none;", "");
				}

				$uk = $this->s2h($k);

				if($RT["FIELDS"][$k]["COLUMN_KEY"] === "PRI"){

					$this->input("key[".$uk."]", "", "", $this->s2h($v), "", "hidden", "");
				}

				$constraint = "";
				foreach($RT["FIELDS"][$k]["CONSTRAINT"] as $vc){

					$constraint .= $vc[0];
				}

				$this->input("", "", "rt_label_key", $constraint, "", "disabled", "");

				$this->input("", "", "rt_label_name", $this->html("$k"), "", "disabled", "");

				$this->tg_open("div", "", "", "display:inline-block;", "");

				if(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
					($RT["FIELDS"][$k]["FOREIGN"]))
				{
					$this->tg_open("div", "com".$count.$count_fl.$uk.$mod, "", "display: none;", "");

					$this->tg_open("div", "", "type_value", "", "");

					$this->tg_open("div", "com_sl".$count.$count_fl.$uk.$mod, "type_value_sl", "", "");

					foreach($RT["FIELDS"][$k]["COLUMN_VALUE"] as $vst)
					{
						$this->tg_open("div", "", "type_value_sl_k", "", "");

						if(in_array("FOREIGN KEY", $RT["FIELDS"][$k]["CONSTRAINT"])){

							$checked = ($v === $vst) ? "checked" : "";

							$this->radio("tv".$count.$count_fl.$uk, "", "", $this->html($vst), "", $checked);
						}
						if(($RT["FIELDS"][$k]["DATA_TYPE"] == "enum")){

							$checked = ($v === $vst) ? "checked" : "";

							$this->radio("tv".$count.$count_fl.$uk, "", "", $this->html($vst), "", $checked);
						}
						if($RT["FIELDS"][$k]["DATA_TYPE"] == "set"){

							$checked = (in_array($vst, explode(",",$v))) ? "checked" : "";

							$this->checkbox("", "", "", $this->html($vst), "", $checked);
						}

						print $this->html($vst);

						$this->tg_close("div");

						$this->tg("div", "", "separator3", "", "", "");
					}

					$this->tg_close("div");

					$this->btn("", "btn", _MESSAGE_CONFIRM_YES,
						"onclick=\"ms.get_stp('".$count.$uk.$mod."', 'com_sl".$count.$count_fl.$uk.$mod."', 'com".$count.$count_fl.$uk.$mod."');\"");

					$this->btn("", "btn", _MESSAGE_CONFIRM_NO,
						"onclick=\"ms.el_va('com".$count.$count_fl.$uk.$mod."', 'none');\"");

					$this->tg_close("div");
					$this->tg_close("div");

					$this->input("", "", "rt_select_type", $RT["FIELDS"][$k]["DATA_TYPE"]."...",
						"onclick=\"ms.el_open_com('com".$count.$count_fl.$uk.$mod."');\"", "readonly", "");
				}
				else{

					$this->input("", "", "rt_label_type",
						$this->html($RT["FIELDS"][$k]["COLUMN_TYPE"]), "", "disabled", "");
				}

				$this->tg_close("div");

				if(in_array($RT["FIELDS"][$k]["COLUMN_TYPE"], $exceptions["geo"]))
				{
					$flag = "";
					$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);

					$this->input("field[".$uk."]", "", "rt_value_input", $this->html($v), "", $flag, "");
				}
				elseif($RT["FIELDS"][$k]["DATA_TYPE"] === "varbinary")
				{
					$flag = "";
					$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);
					if($mod === "edit"){$flag = "disabled";}

					$this->input("field[".$uk."]", "", "rt_value_input_disabled", $this->s2h($v), "", $flag, "");
				}
				elseif(preg_match("/blob$/", $RT["FIELDS"][$k]["DATA_TYPE"]))
				{
					$this->btn("", "btn_text", "&#8597&nbsp;",
						"onclick=\"ms.view_text('text".$count.$count_fl.$uk.$mod."');\"");

					$flag = "";
					$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);
					if($mod === "edit"){$flag = "disabled";}

					$this->textarea("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, "rt_value_text", $this->s2h($v),
						"onchange=\"ms.check_change(this);\"", $flag);
				}
				elseif(preg_match("/text$/", $RT["FIELDS"][$k]["DATA_TYPE"]))
				{
					$this->btn("", "btn_text", "&#8597&nbsp;",
						"onclick=\"ms.view_text('text".$count.$count_fl.$uk.$mod."');\"");

					$flag = "";
					$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);

					$this->textarea("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, "rt_value_text", $this->html($v),
						"onchange=\"ms.check_change(this);\"", $flag);
				}
				else
				{
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
						$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);
					}
					else
					{
						$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);
					}

					$this->input("field[".$uk."]", $count.$uk.$mod, $class, $this->html($v),
						"onchange=\"ms.check_change(this);\"", $flag, "");
				}

				$this->tg_close("div");
			}

			$this->dl_confirm("id_cn_rc".$count.$uk.$mod);

			$this->tg("div", "", "separator3", "", "", "");

			if(($count_fl > 0) && ($count_fl_display > 0))
			{
				if(!$RT["VIEW"])
				{
					if($RT["PRI"])
					{
						if($mod === "edit")
						{
							$this->btn("", "btn", _ACTION_DELETE,
								"onclick=\"ms.AT('_DELETE_RC', 'id_cn_rc".$count.$uk.$mod."', 'id_cn_rc".$count.$uk.$mod."_request', '', this, this.form,
								'"._NOTE_RECORD." / "._ACTION_DELETE."', ['_DELETE_RC'], []); \"");

							$this->btn("", "btn", _ACTION_UPDATE, "onclick=\"ms.RF('_UPDATE_RC', '', '', this.form, 0);\"");
						}
					}

					if($mod === "insert"){

						$this->btn("", "btn", _ACTION_INSERT, "onclick=\"ms.RF('_INSERT_RC', '', '',this.form, 0);\"");
					}

					if($mod === "edit"){

						$this->btn("", "btn", _ACTION_COPY, "onclick=\"ms.RF('_INSERT_RC', '', '',this.form, 0);\"");
					}
				}
			}

			$this->form_close();

			$this->tg("div", "", "separator11", "", "", "");
		}
	}


	private function privileges_rc($k, $PRIVILEGES, $mod)
	{
		if(!isset($PRIVILEGES["COLUMN_PRIVILEGES"])){}
		else
		{
			if($mod === "edit")
			{
				if(!in_array("UPDATE-".$k, $PRIVILEGES["COLUMN_PRIVILEGES"])){

					return "disabled";
				}
			}
			elseif($mod === "insert")
			{
				if(!in_array("INSERT-".$k, $PRIVILEGES["COLUMN_PRIVILEGES"])){

					return "disabled";
				}
			}
		}
	}


	private function form_set($_DB, $_TB,
		$page_db, $from_db, $order_db, $field_db,
		$page_tb, $from_tb, $order_tb, $field_tb,
		$page_rc, $from_rc, $order_rc, $field_rc)
	{
		$this->input("action", "", "", "", "", "hidden", "");		

		$this->input("db", "", "", $_DB, "", "hidden", "");	
		$this->input("tb", "", "", $_TB, "", "hidden", "");	

		if($page_db != ""){$this->input("page_db", "", "", $page_db, "", "hidden", "");}
		if($from_db != ""){$this->input("from_db", "", "", $from_db, "", "hidden", "");}
		if($order_db != ""){$this->input("order_db", "", "", $order_db, "", "hidden", "");;}
		if($field_db != ""){$this->input("field_db", "", "", $field_db, "", "hidden", "");}

		if($page_tb != ""){$this->input("page_tb", "", "", $page_tb, "", "hidden", "");}
		if($from_tb != ""){$this->input("from_tb", "", "", $from_tb, "", "hidden", "");}
		if($order_tb != ""){$this->input("order_tb", "", "", $order_tb, "", "hidden", "");}
		if($field_tb != ""){$this->input("field_tb", "", "", $field_tb, "", "hidden", "");}

		if($page_rc != ""){$this->input("page_rc", "", "", $page_rc, "", "hidden", "");}
		if($from_rc != ""){$this->input("from_rc", "", "", $from_rc, "", "hidden", "");}
		if($order_rc != ""){$this->input("order_rc", "", "", $order_rc, "", "hidden", "");}

		if(count($field_rc) !== 0){

			foreach($field_rc as $value){

				$this->input("field_rc[]", "", "", $this->html($value), "", "hidden", "");
			}
		}

		$this->input("session", "", "", "", "", "hidden", "");	
		$this->input("request", "", "", "", "", "hidden", "");			
	}


	private function nav($RT, $nv, $pre)
	{
		$this->tg_open("div", "", "nav", "", "");

			$this->tg_open("div", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_FIELD, "");


				if($pre === "rc"){

					$this->btn("", "blc", "...", "onclick=\"ms.el_open_com('com_field_rc');\"");

					$this->tg_open("div", "com_field_rc", "type_value", "display: none;", "");
					$this->tg_open("div", "com_sl_field_rc", "type_value_sl", "", "");

					$fl_fl = true;

					foreach($RT["FIELD_ST"] as $vst)
					{
						$this->tg_open("div", "", "type_value_sl_k", "", "");

						if(count($nv["field_rc"]) === 0){

							$checked = "checked";
						}
						else{

							$checked = (in_array($this->s2h($vst), $nv["field_rc"])) ? "checked" : "";
						}

						if($checked === ""){$fl_fl = false;}

						$this->checkbox("field_rc[]", "", "", $this->s2h($vst), "", $checked);

						print $this->html("(".$RT["FIELDS"][$vst]["DATA_TYPE"].") ".$vst);

						$this->tg_close("div");

						$this->tg("div", "", "separator3", "", "", "");
					}

					$checked = $fl_fl ? "checked" : "";

					$this->tg_open("div", "", "type_value_sl_k", "", "");

						$this->checkbox("total", "", "", "",
							"onclick=\"ms.check_sl(this.form,'field_rc[]',this.checked);\"", $checked);

					$this->tg_close("div");
					$this->tg("div", "", "separator3", "", "", "");

					$this->tg_close("div");

					$this->btn("", "btn", _MESSAGE_CONFIRM_YES, "onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

					$this->btn("", "btn", _MESSAGE_CONFIRM_NO, "onclick=\"ms.el_va('com_field_rc', 'none');\"");

					$this->tg_close("div");

				}
				else
				{
					$this->select($RT["FIELD_SE"], $nv["field_".$pre], "field_".$pre, "", "slc", "",
						"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
						function($k, $v){return $k;},
						function($k, $v){return $k;},
						function($k, $v){return $this->html($v);});
				}

			$this->tg_close("div");

			$this->tg_open("div", "", "nav_wrap_filter", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_FILTER, "");

				$this->tg("div", "", "separator0", "", "", "");

				$this->select($RT["FIELD_ST"], $nv["fl_field_".$pre], "fl_field_".$pre, "", "slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $this->s2h($v);},
					function($k, $v){return $this->s2h($v);},
					function($k, $v){return $this->html($v);});

				$this->input("fl_value_".$pre, "", "nav_value", $this->html($nv["fl_value_".$pre]),
					"onchange=\"ms.check_change(this);\"", "", "value");

				$this->select($RT["FILTER_EX"], $nv["fl_operator_".$pre], "fl_operator_".$pre, "ex_".$pre, "slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("div");

			$this->tg_open("div", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_LIMIT, "");

				$this->select($RT["ON_PAGE"], $nv["page_".$pre], "page_".$pre, "", "slc", "",
					"onchange=\"ms.set_vl('from_".$pre."', '0'); ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("div");

			$this->tg_open("div", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_FROM, "");

				$this->select($RT["FROM"], $nv["from_".$pre], "from_".$pre, "from_".$pre, "slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("div");

			$this->tg_open("div", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_ORDER_BY, "");


				$this->select($RT["FIELD_ST"], $nv["order_".$pre], "order_".$pre, "", "slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $k;},
					function($k, $v){return $k;},
					function($k, $v){return $this->html($v);});

			$this->tg_close("div");

		$this->tg_close("div");

		$this->tg("div", "", "separator11", "", _NOTE_TOTAL." [ ".$RT["COUNT"]." ] ", "");
	}

}