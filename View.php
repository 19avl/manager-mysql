<?php

/*
Copyright (c) 2018-2022 Andrey Lyskov
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
		$this->tg_open("div", "id_alt_message", "altDl", "display: none;", "");

		$this->tg("div", "id_alt_message_text", "altDl_text", "", "", "");

		$this->btn("", "", "altDl_btn", _NOTE_CONFIRM_YES, "onclick=\"ms.el_va('id_alt_message', 'none');\"");

		$this->tg_close("div");
	}


	private function dl_confirm($id)
	{
		$this->input("", $id."_request", "", "", "", "hidden", "");

		$this->tg_open("div", $id, "confirmDl", "display: none;", "");

		$this->tg_open("div", "", "", "", "");

		$this->tg("div", "", "confirmDl_title", "", _NOTE_CONFIRM, "");

		$this->tg("div", $id."_text", "confirmDl_text", "", "", "");

		$this->btn("", "", "confirmDl_btn", _NOTE_CONFIRM_YES,
			"onclick=\"ms.RF('', '', '', this.form, 0); ms.el_va('".$id."', 'none');\"");

		$this->btn("", "", "confirmDl_btn", _NOTE_CONFIRM_NO, "onclick=\"ms.el_va('".$id."', 'none'); \"");

		$this->tg_close("div");

		$this->tg_close("div");
	}


	private function rcdl($FUNCTION)
	{
		$this->input("", "rcDl_buf_id", "", "", "", "hidden", "");

		$this->tg("div", "rcDl_ground", "confirmDl", "display: none;", "", "");

		$this->tg_open("div", "rcDl_window", "rcDl_window", "display: none;", "");

			$this->tg_open("div", "rcDl_nav", "rcDl_nav", "", "");

			$this->tg_open("div", "function_dv", "", "display: none;", "");

				$this->input("", "text_id", "", "", "", "hidden", "");

				$this->tg("div", "note_function", "confirmDl_title", "", "", "");

				$this->tg("div", "", "separator3", "", "", "");

				$this->tg_open("div", "", "rcDl", "", "");

				foreach($FUNCTION as $k=>$v){

					$count = count($v);

					if($count === 0){

						$vs = "";
					}
					else{

						$vs = "(".implode(",", $v).")";
					}

					unset($v[0]);
					$vs_add = implode(",", $v);

					$this->tg("div", "", "rcDl_str", "", $k."  ".$vs,
						"onclick=\"dl.set_rcdl_function('rcDl_buf_id', 'text_id', '".$k."', '".
						addslashes($vs)."', '".addslashes($vs_add)."', '".$count."');\"");
				}

				$this->tg_close("div");

				$this->tg("div", "", "separator3", "", "", "");

				$this->btn("", "", "confirmDl_btn", _NOTE_CONFIRM_UNSET,
					"onclick=\"dl.unset_rcdl_function('rcDl_buf_id', 'text_id');\"");

				$this->btn("", "", "confirmDl_btn", _NOTE_CONFIRM_YES, "onclick=\"dl.close_rcdl();\"");

			$this->tg_close("div");

			$this->tg_close("div");

		$this->tg_close("div");
	}


	public function dl_nav($_DB, $_TB, $nv)
	{
		$this->tg_open("div", "id_nav_message", "altDl", "display: none;", "");

		$this->btn("", "", "btn_nav_first", "...", "onclick=\"ms.el_va('id_nav_message', 'none');\"");

		if(($_DB !== "") && ($_TB !== ""))
		{
			$this->form_open("nav_main_form");

			$this->form_set("", "",
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				"", "", "", []);

			$this->filter_set($nv, "db");

			$this->filter_set($nv, "tb");

			$this->btn("", "", "btn_nav", _ACTION_BACK,
							"onclick=\"ms.RF('VIEW', '".$_DB."', '', this.form, 0);\"");

			$this->form_close();
		}
		elseif(($_DB !== "") && ($_TB === ""))
		{
			$this->form_open("nav_main_form");

			$this->form_set("", "",
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				"", "", "", "", "", "", "", []);

			$this->filter_set($nv, "db");

			$this->btn("", "", "btn_nav", _ACTION_BACK,
				"onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

			$this->form_close();
		}

		$this->form_open("nav_main_form");

		$this->form_set($_DB, $_TB,
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", [], $nv["view_rc"]);


		$this->filter_set($nv, "db");

		$this->filter_set($nv, "tb");

		$this->btn("", "", "btn_nav", _ACTION_RELOAD,
			"onclick=\"ms.RF('VIEW', '', '".$_TB."', this.form, 0);\"");

		$this->form_close();

		$this->tg_close("div");
	}


	public function message($log)
	{
		if(isset($log["MESSAGE"]))
		{
			$this->tg_open("div", "div_message", "res_message", "", "");

			$this->tg("div", "", "res_message_close", "", "&#10006;", "onclick=\"ms.el_va('div_message', 'none');\"");

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

			$this->btn("", "", "btn_nav_first", "...", "onclick=\"ms.el_va('id_nav_message', '');\"");

			$this->btn("", "", "btn_nav", _NOTE_DATABASES,
				"onclick=\"ms.view_wr('id_wr_db', 'id_wr_script');\"");

			$this->btn("", "", "btn_nav", _NOTE_SQL,
				"onclick=\"ms.view_wr('id_wr_script', 'id_wr_db');\"");

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

			$this->filter_set($nv, "db");

			$this->tg_open("div", "", "ct_row", "", "");

			$this->tg_open("div", "", "pl_el", "", "");

			$this->checkbox("totalH", "", "ct_check", "checkbox",
				"onclick=\"ms.check_sl(this.form,'list_db[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

			$this->input("", "", "ct_name_title", _NOTE_DATABASES, "", "disabled", "");

			$this->input("", "", "ct_info_A", "COUNT", "", "readonly", "");

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
				'_EXPORT_SQL_DB'=>_ACTION_EXPORT_SQL
				],
				false, "", "", "st_select_value", _NOTE_SELECT,
					"onchange=\"ms.AL(this.value, 'id_cn_db', 'id_cn_db_request', '', this, this.form, 'list_db[]',
					'"._NOTE_DATABASES." / "._NOTE_SELECT." / ', ['_DELETE_DB','_CLEAR_DB'],
					['_EXPORT_SQL_DB'],
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


	public function mk($_DB, $_TB, $LIST_SQL, $SQL_SL, $nv, $display)
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

		$this->filter_set($nv, "db");

		$this->filter_set($nv, "tb");

		$this->input("display", "", "", "sql", "", "hidden", "");

		$this->tg_open("div", "", "ct_row", "", "");

			$this->select(array_keys($LIST_SQL), "", "script_id", "", "slc", _NOTE_SCRIPT,
				"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
				function($k, $v){return $v;},
				function($k, $v){return $v;},
				function($k, $v){return $v;});

		$this->tg_close("div");

		$this->form_close();

		$this->form_open();

		$this->form_set($_DB, $_TB,
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->filter_set($nv, "db");

		$this->filter_set($nv, "tb");

		$this->textarea("script", "script_text", "", $SQL_SL, "", "title='"._NOTE_SCRIPT_DROP."'");

		$this->textarea("script_file", "script_text_file", "", "", "", "hidden");

		$this->btn("", "", "btn", _ACTION_RUN, "onclick=\"ms.RF('_RUN_SQL', '', '".$_TB."', this.form, 0);\"");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_close("div");
	}


	public function stat($stat)
	{
		$this->tg("div", "", "", "", "&nbsp;", "");
		$this->tg("div", "", "", "", $stat, "");
		$this->tg("div", "", "", "", "&nbsp;", "");
	}


	public function info($info)
	{
		foreach($info as $value){

			$this->tg("div", "", "", "rt_list", $value, "");
		}

		$this->tg("div", "", "separator11", "", "", "");
	}


	public function tb($_DB, $RT, $action, $nv, $display)
	{
		if($RT["CREATE"] == ""){return;}

		$_DBS = $this->html($this->h2s($_DB));

		$this->input("", "", "rt_label_db", $_DBS, "onclick=\"ms.el_vb('db_id');\"", "readonly", "");

		if($display === "tb_sub"){

			$this->tg_open("div", "db_id", "pl_el", "", "");
		}
		else{

			$this->tg_open("div", "db_id", "pl_el", "display: none;", "");
		}

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg("div", "", "", "", $this->html(substr($RT["CREATE"]["DB"], 7)), "");

		$this->form_open();

		$this->form_set($_DB, "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			"", "", "", "", "", "", "", []);

		$this->dl_confirm("id_cn_tbt");

		$this->tg("div", "", "separator11", "", "", "");

		$this->input("cl_in", "db_name_new_id", "st_value_B", $this->html($this->h2s($_DB)),
			"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

		$this->btn("", "", "st_btn", _ACTION_COPY,
			"onclick=\"ms.AT('_COPY_DB', 'id_cn_tbt', 'id_cn_stb_request', '', this, this.form, '', [''], [],
			'db_name_new_id', '"._MESSAGE_NOT_VALUE."'); \"");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");

		$this->form_open();

		$this->form_set($_DB, "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", []);

		$this->filter_set($nv, "db");

		$this->filter_set($nv, "tb");

		$this->input("display", "", "", "tb_sub", "", "hidden", "");

		$this->dl_confirm("id_cn_sub");

			$this->select(array_keys($RT["VIEWS"]),
				"", "cl_sl[views]", "", "slc", "views",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});

			$this->select(array_keys($RT["EVENTS"]),
				"", "cl_sl[events]", "", "slc", "events",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});

			$this->select(array_keys($RT["TRIGGERS"]),
				"", "cl_sl[triggers]", "", "slc", "triggers",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});

			$this->select(array_keys($RT["PROCEDURE"]),
				"", "cl_sl[procedure]", "", "slc", "procedure",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});

			$this->select(array_keys($RT["FUNCTION"]),
				"", "cl_sl[function]", "", "slc", "function",
				"onchange=\"ms.RF('_GET_SUB', '".$_DB."', '', this.form, 0); \"",
				function($k, $v){return $this->s2h($v);;},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});

		$this->input("cl_in", "", "", $RT["SUB"]["NM"], "", "hidden", "");
		$this->input("cl_tr", "sub_name_id", "", $RT["SUB"]["ID"], "", "hidden", "");
		$this->input("cl_dl", "", "", $this->s2h(substr($RT["SUB"]["SL"], 7)), "", "hidden", "");

		if($RT["SUB"]["SL"] !== "")
		{
			$this->tg_open("div", "target_id_nav", "", "", "");

				$this->textarea("cl_df", "", "rt_value_text",
					$this->html(substr($RT["SUB"]["SL"], 7), "`,`", "`,\n`"),
					"onclick=\"ms.el_va('id_alt_message', 'none');\"", "disabled");

				$this->tg("div", "", "separator3", "", "", "");

				$this->btn("", "", "st_btn", _ACTION_EDIT,
					"onclick=\"ms.el_va('target_id_edit', ''); ms.el_va('target_id_nav', 'none');\"");

				if(($RT["SUB"]["ID"] === "procedure") || ($RT["SUB"]["ID"] === "function")){

					$this->btn("", "", "st_btn", _ACTION_RUN,
						"onclick=\"ms.el_va('target_id_run', ''); ms.el_va('target_id_nav', 'none');\"");
				}

			$this->tg_close("div");

			$this->tg_open("div", "target_id_edit", "", "display: none;", "");

				$this->textarea("cl_df", "target_id", "rt_value_text",
					$this->html(substr($RT["SUB"]["SL"], 7), "`,`", "`,\n`"),
					"onclick=\"ms.el_va('id_alt_message', 'none');\"", "");

				$this->tg("div", "", "separator3", "", "", "");

				$this->btn("", "", "st_btn", _ACTION_DELETE,
					"onclick=\"ms.AT('_DELETE_SUB', 'id_cn_sub', 'id_cn_sub_request', '', this, this.form,
					ms.get_sub('sub_name_id','')+' / "._ACTION_DELETE."',
					['_DELETE_SUB'], [], 'target_id', '"._MESSAGE_NOT_VALUE."'); \"");

				$this->btn("", "", "st_btn", _ACTION_UPDATE,
					"onclick=\"ms.AT('_UPDATE_SUB', 'id_cn_sub', 'id_cn_sub_request', '', this, this.form,
					ms.get_sub('sub_name_id','')+' / "._ACTION_UPDATE."',
					['_UPDATE_SUB'], [], 'target_id', '"._MESSAGE_NOT_VALUE."'); \"");

				$this->btn("", "", "st_btn", _ACTION_CREATE,
					"onclick=\"ms.AT('_CREATE_SUB', 'id_cn_sub', 'id_cn_sub_request', '', this, this.form,
					ms.get_sub('sub_name_id','')+' / "._ACTION_CREATE."',
					[], [], 'target_id', '"._MESSAGE_NOT_VALUE."'); \"");

			$this->tg_close("div");

			$this->tg_open("div", "target_id_run", "", "display: none;", "");

				if(($RT["SUB"]["ID"] === "procedure") || ($RT["SUB"]["ID"] === "function")){

					$this->textarea("script", "", "rt_value_text", $RT["SUB"]["PR"],"", "");

					$this->tg("div", "", "separator3", "", "", "");

					$this->btn("", "", "st_btn", _ACTION_RUN, "onclick=\"ms.RF('_RUN_SQL', '', '', this.form, 0);\"");
				}

			$this->tg_close("div");
		}

		$this->form_close();

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

		$this->btn("", "", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_DB', '".$_DB."', '', this.form, 0, 'search_db', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tg_close("div");

		$this->tg("div", "", "separator3", "", "", "");

		$this->nav($RT, $nv, "tb");

		$this->form_close();

		if( count($RT["TABLES"]) === 0 ){return;}

		$this->form_open();

		$this->form_set($_DB, "",
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", []);

		$this->filter_set($nv, "db");

		$this->filter_set($nv, "tb");

		$this->tg_open("div", "", "ct_row", "", "");

		$this->tg_open("div", "", "pl_el", "", "");

		$this->checkbox("totalH", "", "ct_check", "checkbox",
			"onclick=\"ms.check_sl(this.form,'list_tb[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

		$this->input("", "", "ct_name_title", _NOTE_TABLES, "", "disabled", "");

		$this->input("", "", "ct_info_A", "COUNT", "", "readonly", "");

		$this->input("", "", "ct_info_D", $RT["FIELD_SE"][$nv["field_tb"]], "", "readonly", "");

		$this->tg_close("div");

		foreach($RT["TABLES"] as $key=>$value)
		{
			$uk = $this->s2h($key);

			$this->tg_open("div", "", "pl_el", "", "");

			$this->checkbox("list_tb[]", "", "ct_check", $uk, "onclick=\"ms.el_va('id_alt_message', 'none');\"", "");

			$flag = "";
			if($value["COUNT"] === "IN USE"){

				$flag = "disabled";
			}

			$this->input("", "", "ct_name", $this->html($key),
				"onclick=\"ms.RF('VIEW', '', '".$uk."', this.form, 0);\"", $flag, "");

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
				'_EXPORT_SQL_TB'=>_ACTION_EXPORT_SQL
			],
			false, "list_LT_sl", "", "st_select_value", _NOTE_SELECT,
			"onchange=\"ms.AL(this.value, 'id_cn_tbr', 'id_cn_tbr_request', '', this, this.form, 'list_tb[]',
			'"._NOTE_TABLES." / "._NOTE_SELECT." / ', ['_DELETE_TB','_CLEAR_TB'],
			['_EXPORT_SQL_TB'],
			'"._MESSAGE_TB_CHECK."' ); \"",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->tg_close("div");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");
	}


	public function rc($_DB, $_TB, $RT, $nv, $FUNCTION, $ext, $display)
	{
		$_DBS = $this->html($this->h2s($_DB));
		$_TBS = $this->html($this->h2s($_TB));

		if($RT["CREATE"] == ""){return;}

		$this->input("", "", "rt_label_db", $_DBS.".".$_TBS, "onclick=\"ms.el_vb('tb_id');\"", "readonly", "");

		$this->tg_open("div", "", "pl_el", "", "");

		if($display === "rc_sub"){

			$this->tg_open("div", "tb_id", "", "", "");
		}
		else{

			$this->tg_open("div", "tb_id", "", "display: none;", "");
		}

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg("div", "", "", "", $this->html(substr($RT["CREATE"]["DB"], 7), "`,`", "`,<br>`"), "");

		$this->tg("div", "", "separator3", "", "", "");
		$this->tg("div", "", "separator3", "", "", "");

		if($RT["TABLE_TYPE"] === "VIEW"){

			$this->tg("div", "", "res", "", $this->html(substr($RT["CREATE"]["TB"], 7), "`,`", "`,<br>`"), "");
		}
		else{

			$this->tg("div", "", "res", "", $this->html(substr($RT["CREATE"]["TB"], 7), "\n", "<br>"), "");
		}

		$this->tg("div", "", "separator11", "", "", "");

		if(($this->h2s($_DB) !== "information_schema") && ($this->h2s($_DB) !== "performance_schema") &&
			($RT["CREATE"]["TB"] !== ""))
		{
			$this->form_open();

			$this->form_set($_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->filter_set($nv, "db");

			$this->filter_set($nv, "tb");

			$this->input("display", "", "", "rc_sub", "", "hidden", "");

			$this->dl_confirm("id_cn_st");

			if(($RT["TABLE_TYPE"] !== "VIEW"))
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

				$this->btn("", "", "st_btn", _ACTION_ALTER,
					"onclick=\"ms.AT('_UPDATE_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
					ms.get_sub('','')+'"._NOTE_TABLE." / "._ACTION_ALTER."',
					['_UPDATE_TB'], [], 'tb_def_id', '"._MESSAGE_NOT_VALUE."'); \"");

				$this->tg_close("div");

				$this->tg("div", "", "separator11", "", "", "");
			}

			$this->select($RT["DB_LIST"],
				$_DB, "cl_tr", "", "st_select_db", "", "",
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->s2h($v);},
				function($k, $v){return $this->html($v);});

			$this->input("cl_in", "name_new_id", "st_value_C", $_TBS,
				"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

			$this->btn("", "", "st_btn", _ACTION_RENAME,
				"onclick=\"ms.AT('_RENAME_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form,
				'"._NOTE_TABLE." / "._ACTION_RENAME."',
				['_RENAME_TB'], [], 'name_new_id', '"._MESSAGE_NOT_VALUE."'); \"");

			$this->btn("", "", "st_btn", _ACTION_COPY,
				"onclick=\"ms.AT('_COPY_TB', 'id_cn_st', 'id_cn_st_request', '', this, this.form, '',
				[], [], 'name_new_id', '"._MESSAGE_NOT_VALUE."'); \"");

			$this->tg("div", "", "separator11", "", "", "");

			$this->form_close();

			if(($RT["TABLE_TYPE"] !== "VIEW")){

				$this->rc_data($_DB, $_TB, $RT, $nv, $FUNCTION, $ext, "insert");
			}
		}

		$this->tg_close("div");

		$this->tg_close("div");

		$this->tg("div", "", "separator11", "", "", "");

		$this->form_open();

		$this->form_set($_DB, $_TB,
			$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", [], $nv["view_rc"]);

		$this->tg_open("div", "", "pl_el", "", "");

		$this->select(
			[_NOTE_SEARCH_M0, _NOTE_SEARCH_M1],
			"", "cl_df", "", "st_select_value", "", "",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->input("cl_in", "search_tb", "st_value_D", "",
			"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

		$this->btn("", "", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_TB', '', '".$_TB."', this.form, 0, 'search_tb', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tg_close("div");

		$this->tg("div", "", "separator3", "", "", "");

		$this->nav($RT, $nv, "rc");

		$this->form_close();

		if($RT["COUNT"] !== 0){

			if($nv["view_rc"] === "tb"){

				$this->rc_data($_DB, $_TB, $RT, $nv, $FUNCTION, $ext, "edit");
			}
			else{

				$this->rc_data_list($_DB, $_TB, $RT, $nv, $FUNCTION, $ext, "edit");
			}
		}

		$this->rcdl($FUNCTION);
	}


	private function rc_data_list($_DB, $_TB, $RT, $nv, $FUNCTION, $ext, $mod)
	{
		$this->tg_open("div", "", "rt_list", "", "");

		$this->tg_open("table", "", "rt_list_tb", "", "");

			$this->tg_open("tr", "", "", "", "");

				$this->tg_open("td", "", "", "", "");

					$this->form_open();

					$this->form_set($_DB, $_TB,
						$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
						$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
						$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

					$this->filter_set($nv, "db");

					$this->filter_set($nv, "tb");

					$this->filter_set($nv, "rc");

					$this->input("view_rc", "", "", "tb", "", "hidden", "");

					$this->btn("", "", "rt_btn_list", "...", "onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

					$this->form_close();

				$this->tg_close("td");

				foreach($RT["RECORDS"][0] as $k=>$value)
				{
					$is_extra = $RT["FIELDS"][$k]["EXTRA"];

					if($RT["FIELDS"][$k]["IS_NULLABLE"] === "NO"){

						$is_extra = "not null ".$is_extra;
					}

					if((count($nv["field_rc"]) === 0) || (in_array($this->s2h($k), $nv["field_rc"])))
					{
						$this->tg_open("td", "", "", "", "");

						$constraint = "";
						foreach($RT["FIELDS"][$k]["CONSTRAINT"] as $vc){

							$constraint .= $vc[0];
						}

						if(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
							($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
							($RT["FIELDS"][$k]["FOREIGN"]))
						{
							$this->input("", "", "rt_label_list",
								"".$constraint." [ ".$RT["FIELDS"][$k]["DATA_TYPE"]." ] ".$this->html("$k"),
								"title='".$is_extra."'", "disabled", "");
						}
						else
						{
							$this->input("", "", "rt_label_list",
								"".$constraint." [ ".$RT["FIELDS"][$k]["COLUMN_TYPE"]." ] ".$this->html("$k"),
								"title='".$is_extra."'", "disabled", "");
						}

						$this->tg_close("td");
					}
				}

			$this->tg_close("tr");

		$this->tg_close("table");

		$from_rc = $nv["from_rc"];
		$count = "0";

		foreach($RT["RECORDS"] as $value)
		{
			$count += 1;

			$this->form_open();

			$this->form_set($_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->filter_set($nv, "db");

			$this->filter_set($nv, "tb");

			$this->filter_set($nv, "rc");

			$this->input("view_rc", "", "", "st", "", "hidden", "");

			$this->tg_open("table", "", "rt_list_tb", "", "");

				$this->tg_open("tr", "", "", "", "");

					$this->tg_open("td", "", "", "", "");

						$this->btn("", "", "rt_btn_list", "...",
							"onclick=\"this.form.page_rc.value='1'; this.form.from_rc.value='".$from_rc."'; this.form.view_rc.value='tb';
							ms.RF('VIEW', '', '', this.form, 0);\"");

						$from_rc = $from_rc+1;

					$this->tg_close("td");

					foreach($value as $k=>$v)
					{
						$data_flag = "disabled";
						$flag = "";
						$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);

						$class = "rt_value_list";

						if($flag === "disabled"){

							$class = "rt_label_list";
						}

						$uk = $this->s2h($k);

						if(($RT["FIELDS"][$k]["COLUMN_KEY"] === "PRI") ||
							($RT["FIELDS"][$k]["COLUMN_KEY"] === "UNI")){

							$this->input("key[".$uk."]", "", "", $this->s2h($v), "", "hidden", "");
						}

						if((count($nv["field_rc"]) === 0) || (in_array($this->s2h($k), $nv["field_rc"])))
						{
							$this->tg_open("td", "", "", "", "");

							if(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["geo"]))
							{
								$this->input("", "", $class, $this->html($v), "", $data_flag, "");
							}
							elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["binary"]))
							{
								$this->input("", "", $class, $this->s2h($v), "", $data_flag, "");
							}
							elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["blob"]))
							{
								$this->input("", "", $class, $this->get_seze($v), "", $data_flag, "");
							}
							elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["text"]))
							{
								$this->input("", "", $class, $this->get_seze($v), "", $data_flag, "");
							}
							else
							{
								if(($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
									($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL") ||
									($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL GENERATED") ||
									($RT["FIELDS"][$k]["EXTRA"] === "STORED GENERATED") ||
									($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP")){

										$class = "rt_label_list";
								}

								$v = preg_replace("/\\n/", " ", $v);

								$this->input("", "", $class, $this->html($v), "", $data_flag, "");
							}

							$this->tg_close("td");
						}
					}

				$this->tg_close("tr");

			$this->tg_close("table");

			$this->form_close();
		}

		$this->tg_close("div");

		$this->tg("div", "", "separator11", "", "", "");
	}


	private function rc_data($_DB, $_TB, $RT, $nv, $FUNCTION, $ext, $mod)
	{
		if($mod === "edit")
		{
			$this->form_open();

			$this->form_set($_DB, $_TB,
				$nv["page_db"], $nv["from_db"], $nv["order_db"], $nv["field_db"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->filter_set($nv, "db");

			$this->filter_set($nv, "tb");

			$this->filter_set($nv, "rc");

			$this->input("view_rc", "", "", "st", "", "hidden", "");

			$this->btn("", "", "rt_btn_list", "...", "onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

			$this->form_close();

			$this->tg("div", "", "separator11", "", "", "");
		}

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
				"", "", $nv["order_rc"], $nv["field_rc"]);

			$this->filter_set($nv, "db");

			$this->filter_set($nv, "tb");

			$this->filter_set($nv, "rc");

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

				if(($RT["FIELDS"][$k]["COLUMN_KEY"] === "PRI") ||
					($RT["FIELDS"][$k]["COLUMN_KEY"] === "UNI")){

					$this->input("key[".$uk."]", "", "", $this->s2h($v), "", "hidden", "");
				}

				$constraint = "";
				foreach($RT["FIELDS"][$k]["CONSTRAINT"] as $vc){

					$constraint .= $vc[0];
				}

				$this->input("", "", "rt_label_key", $constraint, "", "disabled", "");

				$this->input("", "", "rt_label_name", $this->html("$k"), "", "disabled", "");

				$this->tg_open("div", "", "", "display:inline-block;", "");

				$flag = "";
				$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);

				$is_extra = $RT["FIELDS"][$k]["EXTRA"];

				$not_null = "";

				if($RT["FIELDS"][$k]["IS_NULLABLE"] === "NO"){

					$not_null = " not null";
				}

				if(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
					($RT["FIELDS"][$k]["FOREIGN"]))
				{
					$this->tg_open("div", "com".$count.$count_fl.$uk.$mod, "", "display: none;", "");

					$this->tg_open("div", "", "type_value", "", "");

					$this->tg_open("div", "com_sl".$count.$count_fl.$uk.$mod, "type_value_sl", "",
						"onclick=\"ms.el_stop_com();\"");

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

					$this->btn("", "", "btn", _NOTE_CONFIRM_YES,
						"onclick=\"ms.get_stp('text".$count.$count_fl.$uk.$mod."', 'com_sl".
						$count.$count_fl.$uk.$mod."', 'com".$count.$count_fl.$uk.$mod."');\"");

					$this->btn("", "", "btn", _NOTE_CONFIRM_NO,
						"onclick=\"ms.el_va('com".$count.$count_fl.$uk.$mod."', 'none');\"");

					$this->tg_close("div");
					$this->tg_close("div");

					if($flag === "disabled"){

						$type_onclick = "";
					}
					else{

						$type_onclick = "onclick=\"ms.el_open_com('com".$count.$count_fl.$uk.$mod."');\"";
					}

					$this->input("", "", "rt_select_type",
						$RT["FIELDS"][$k]["DATA_TYPE"].$not_null." ...", $type_onclick, "readonly title='".$is_extra."'", "");
				}
				else{

					$this->input("", "", "rt_label_type",
						$this->html($RT["FIELDS"][$k]["COLUMN_TYPE"]).$not_null, "title='".$is_extra."'", "disabled", "");
				}

				$this->tg_close("div");

				$function_class = "rt_value_function";

				$function_onclick =
					"onclick=\"dl.creat_rcdl(this.id, 'text".$count.$count_fl.$uk.$mod."',
						'function_dv', '".$this->html(addslashes("$k"))."');\"";

				$function_flag = "autocomplete='off'";

				$function_placeholder = "function...";

				if(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["blob"]) ||
					in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["geo"]) ||
					($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
					($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP") ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") || ($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
					in_array("FOREIGN KEY", $RT["FIELDS"][$k]["CONSTRAINT"]))
				{
					$this->input("function[".$uk."]", "function_".$count.$count_fl.$uk.$mod, "rt_value_function_disabled", "",
						"", "disabled", "");
				}
				else
				{
					if($flag === "disabled"){

						$function_class = "rt_value_function_disabled";
						$function_onclick = "";
						$function_flag = "disabled";
						$function_placeholder = "";
					}

					$this->input("function[".$uk."]", "function_".$count.$count_fl.$uk.$mod, $function_class, "",
						$function_onclick, $function_flag, $function_placeholder);
				}

				$data_flag = $flag;

				if(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["geo"]))
				{
					$class = "rt_value_input";

					if($flag === "disabled"){

						$class = "rt_value_input_disabled";
					}

					$this->input("field[".$uk."]", "", $class, $this->html($v), "", $data_flag, "");
				}
				elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["binary"]))
				{
					$class = "rt_value_input";

					if($flag === "disabled"){

						$class = "rt_value_input_disabled";
						$data_flag = "disabled";
					}

					$this->input("file[".$uk."]", "file".$count.$count_fl.$uk.$mod, "",
						$this->s2h($v), "", "hidden", "");

					$this->input("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, $class,
						$this->s2h($v), "", $data_flag, "");
				}
				elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["blob"]))
				{
					if(($mod === "edit") && (trim($v) !== "")){

						$value = " ( ".$this->get_seze($v)." ) ";
					}
					else{

						$value = "";
						$data_flag = "hidden";
					}

					$this->input("", "file_name".$count.$count_fl.$uk.$mod,
						"rt_value_input_disabled", $value, "", "disabled", "");

					if($flag === "disabled")
					{
						$this->input("blob_ch[".$uk."]", "blob_ch_id".$count.$count_fl.$uk.$mod, "", "3", "", "hidden", "");
					}
					else
					{
						$this->tg_open("table", "", "", "", "");
						$this->tg_open("tr", "", "", "", "");

						$this->tg_open("td", "", "", "", "");

						if(($mod === "edit") && (trim($v) !== ""))
						{
							$this->tg_open("div", "", "file_dl", "", "");

							$this->tg("a", "", "file_dl_a", "", _NOTE_CONFIRM_UNSET, "
								href=\"javascript:ms.reset_rcul_file(
								'file".$count.$count_fl.$uk.$mod."',
								'file_name".$count.$count_fl.$uk.$mod."',
								'text".$count.$count_fl.$uk.$mod."',
								'blob_ch_id".$count.$count_fl.$uk.$mod."',
								'prev".$count.$count_fl.$uk.$mod."');\"");

							$this->tg_close("div");
						}

						$this->tg_close("td");

						$this->tg_open("td", "", "", "", "");

						$this->tg_open("div", "", "file_dl", "", "");

						$this->tg("a", "", "file_dl_a", "", "text", "
							href=\"javascript:ms.get_rcul_text(
							'file".$count.$count_fl.$uk.$mod."',
							'file_name".$count.$count_fl.$uk.$mod."',
							'text".$count.$count_fl.$uk.$mod."',
							'blob_ch_id".$count.$count_fl.$uk.$mod."',
							'prev".$count.$count_fl.$uk.$mod."'
							);\"");

						$this->tg_close("div");

						$this->tg_close("td");

						$this->tg_open("td", "", "", "", "");

						$this->tg_open("div", "", "file_ul", "", "");
						$this->tg_open("label", "", "", "", "");

						$this->file("", "", "", "",
							"onchange=\"ms.get_rcul_file(this.files,
							'file".$count.$count_fl.$uk.$mod."',
							'file_name".$count.$count_fl.$uk.$mod."',
							'text".$count.$count_fl.$uk.$mod."',
							'blob_ch_id".$count.$count_fl.$uk.$mod."',
							'prev".$count.$count_fl.$uk.$mod."'
							);\"", "multiple");

						$this->tg("span", "", "", "", _NOTE_UL." (max ".ini_get("post_max_size").")", "");

						$this->tg_close("label");
						$this->tg_close("div");

						$this->tg_close("td");

						$this->tg_close("tr");
						$this->tg_close("table");

						if($RT["PRI"] === true)
						{
							$this->input("blob_ch[".$uk."]", "blob_ch_id".$count.$count_fl.$uk.$mod, "", "1", "", "hidden", "");

							$this->textarea("file[".$uk."]", "file".$count.$count_fl.$uk.$mod, "", "", "hidden", "");

							$this->textarea("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, "rt_value_text", "", "hidden", "");
						}
						else
						{
							$this->input("blob_ch[".$uk."]", "blob_ch_id".$count.$count_fl.$uk.$mod, "", "2", "", "hidden", "");

							$this->textarea("file[".$uk."]", "file".$count.$count_fl.$uk.$mod, "", base64_encode($v), "hidden", "");

							$this->textarea("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, "rt_value_text", $v, "hidden", "");
						}
					}

					if(($mod === "edit") && (trim($v) !== ""))
					{
						$this->textarea("", "prev".$count.$count_fl.$uk.$mod, "rt_value_text",
							$this->strTV($this->html($v)), "", "disabled");
					}
					else
					{
						$this->textarea("", "prev".$count.$count_fl.$uk.$mod, "rt_value_text",
							$this->strTV($this->html($v)), "", "disabled hidden");
					}
				}
				elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["text"]) ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "json"))
				{
					$onclick = "onclick=\"ms.view_text('text".$count.$count_fl.$uk.$mod."');\"";

					if($flag === "disabled"){

						$onclick = "";
						$data_flag = "disabled";
					}

					$this->btn("", "", "btn_text", "&#8597;", $onclick);

					$this->textarea("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, "rt_value_text", $this->html($v),
						"onchange=\"ms.check_change(this);\"", $data_flag);
				}
				else
				{
					$class = "rt_value_input";

					if(($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
						($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL") ||
						($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL GENERATED") ||
						($RT["FIELDS"][$k]["EXTRA"] === "STORED GENERATED") ||
						($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP")){

						$class = "rt_value_input_disabled";
						$data_flag = "disabled";
					}
					elseif(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
						($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
						in_array("FOREIGN KEY", $RT["FIELDS"][$k]["CONSTRAINT"])){

						$data_flag = "readonly";
					}

					if($flag === "disabled"){

						$class = "rt_value_input_disabled";
						$data_flag = "disabled";
					}

					$this->input("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, $class, $this->html($v),
						"onchange=\"ms.check_change(this);\"", $data_flag, "");
				}

				$this->tg_close("div");
			}

			$this->dl_confirm("id_cn_rc".$count.$uk.$mod);

			$this->tg("div", "", "separator3", "", "", "");

			if($this->h2s($_DB) !== "information_schema")
			{
				if(($count_fl > 0) && ($count_fl_display > 0))
				{
					if(($RT["TABLE_TYPE"] !== "VIEW"))
					{
						if($RT["PRI"])
						{
							if($mod === "edit")
							{
								if(!isset($RT["PRIVILEGES"]["TABLE_PRIVILEGES"]) ||
									in_array("DELETE", $RT["PRIVILEGES"]["TABLE_PRIVILEGES"]))
								{
									$this->btn("", "", "btn", _ACTION_DELETE,
										"onclick=\"ms.AT('_DELETE_RC', 'id_cn_rc".$count.$uk.$mod."',
										'id_cn_rc".$count.$uk.$mod."_request', '', this, this.form,
										'"._NOTE_ROW." / "._ACTION_DELETE."', ['_DELETE_RC'], []); \"");
								}

								if(!isset($RT["PRIVILEGES"]["TABLE_PRIVILEGES"]) ||
									in_array("UPDATE", $RT["PRIVILEGES"]["TABLE_PRIVILEGES"]))
								{
									$this->btn("", "", "btn", _ACTION_UPDATE,
										"onclick=\"ms.RF('_UPDATE_RC', '', '', this.form, 0);\"");
								}
							}
						}

						if(!isset($RT["PRIVILEGES"]["TABLE_PRIVILEGES"]) ||
							in_array("INSERT", $RT["PRIVILEGES"]["TABLE_PRIVILEGES"]))
						{
							if($mod === "insert"){

								$this->btn("", "", "btn", _ACTION_INSERT,
									"onclick=\"ms.RF('_INSERT_RC', '', '',this.form, 0);\"");
							}

							if($mod === "edit"){

								$this->btn("", "", "btn", _ACTION_INSERT,
									"onclick=\"ms.RF('_COPY_RC', '', '',this.form, 0);\"");
							}
						}
					}
				}
			}
			else{

				$this->tg("div", "", "separator11", "", "", "");
				$this->tg("div", "", "separator11", "", "", "");
			}

			$this->form_close();

			$this->tg("div", "", "separator11", "", "", "");
		}
	}


	private function get_seze($v)
	{
		$size = strlen($v);
		if($size > 1024)
		{
			$size = ($size/1024);

			if($size > 1024)
			{
				$size = ($size/1024);

				if($size > 1024)
				{
					$size = ($size/1024);
					$size = round($size, 1)." gb";
				}
				else
				{
					$size = round($size, 1)." mb";
				}
			}
			else
			{
				$size = round($size, 1)." kb";
			}
		}
		else
		{
			$size = round($size, 1)." b";
		}

		return $size;
	}


	private function privileges_rc($k, $PRIVILEGES, $mod)
	{
		if(!isset($PRIVILEGES["COLUMN_PRIVILEGES"])){

			if(isset($PRIVILEGES["TABLE_PRIVILEGES"])){

				if($mod === "edit")
				{
					if(!in_array("UPDATE", $PRIVILEGES["TABLE_PRIVILEGES"])){

						return "disabled";
					}
				}
				elseif($mod === "insert")
				{
					if(!in_array("INSERT", $PRIVILEGES["TABLE_PRIVILEGES"])){

						return "disabled";
					}
				}
			}
		}
		else
		{
			if($mod === "edit")
			{
				if(!in_array($k, $PRIVILEGES["COLUMN_PRIVILEGES"]["UPDATE"])){

					return "disabled";
				}
			}
			elseif($mod === "insert")
			{
				if(!in_array($k, $PRIVILEGES["COLUMN_PRIVILEGES"]["INSERT"])){

					return "disabled";
				}
			}
		}
	}


	private function form_set($_DB, $_TB,
		$page_db, $from_db, $order_db, $field_db,
		$page_tb, $from_tb, $order_tb, $field_tb,
		$page_rc, $from_rc, $order_rc, $field_rc, $view_rc="")
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

		if($view_rc != ""){$this->input("view_rc", "", "", $view_rc, "", "hidden", "");}

		$this->input("session", "", "", "", "", "hidden", "");
		$this->input("request", "", "", "", "", "hidden", "");
	}



	private function filter_set($nv, $fl)
	{
		foreach($nv["fl_and_".$fl] as $v){

			$this->input("fl_and_".$fl."[]", "", "", $v, "", "hidden", "");
		}
		foreach($nv["fl_field_".$fl] as $v){

			$this->input("fl_field_".$fl."[]", "", "", $this->html($v), "", "hidden", "");
		}
		foreach($nv["fl_operator_".$fl] as $v){

			$this->input("fl_operator_".$fl."[]", "", "", $v, "", "hidden", "");
		}
		foreach($nv["fl_value_".$fl] as $v){

			$this->input("fl_value_".$fl."[]", "", "", $this->html($v), "", "hidden", "");
		}
	}


	private function nav($RT, $nv, $pre)
	{
		$this->tg_open("table", "", "nav_wrap_m", "", "");

			$this->tg_open("tr", "", "", "", "");

			$this->tg_open("td", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_FILTER, "");

				$this->btn("", "", "blc", "...", "onclick=\"ms.el_vb('flc_".$pre."');\"");

			$this->tg_close("td");

			$this->tg_open("td", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_FIELD, "");

				if($pre === "rc")
				{
					$this->btn("", "", "blc", "...", "onclick=\"ms.el_open_com('com_field_rc');\"");

					$this->tg_open("div", "com_field_rc", "type_value", "display: none;",
						"onclick=\"ms.el_stop_com();\"");

					$this->tg_open("div", "com_sl_field_rc", "type_value_sl", "", "");

					$this->tg_open("div", "", "type_value_sl_k", "", "");

						$this->checkbox("totalH", "", "", "",
							"onclick=\"ms.check_sl(this.form,'field_rc[]',this.checked);\"", "checked");

					$this->tg_close("div");

					$this->tg("div", "", "separator3", "", "", "");

					foreach($RT["FIELD_ST"] as $vst)
					{
						$this->tg_open("div", "", "type_value_sl_k", "", "");

						if(count($nv["field_rc"]) === 0){

							$checked = "checked";
						}
						else{

							$checked = (in_array($this->s2h($vst), $nv["field_rc"])) ? "checked" : "";
						}

						$this->checkbox("field_rc[]", "", "", $this->s2h($vst), "", $checked);

						$this->tg("label", "", "", "", $this->html("(".$RT["FIELDS"][$vst]["DATA_TYPE"].") ".$vst), "");

						$this->tg_close("div");

						$this->tg("div", "", "separator3", "", "", "");
					}

					$this->tg_open("div", "", "type_value_sl_k", "", "");

						$this->checkbox("totalF", "", "", "",
							"onclick=\"ms.check_sl(this.form,'field_rc[]',this.checked);\"", "checked");

					$this->tg_close("div");

					$this->tg("div", "", "separator3", "", "", "");

					$this->tg_close("div");

					$this->btn("", "", "btn", _NOTE_CONFIRM_YES, "onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

					$this->btn("", "", "btn", _NOTE_CONFIRM_NO, "onclick=\"ms.el_va('com_field_rc', 'none');\"");

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

			$this->tg_close("td");

			$this->tg_open("td", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_LIMIT, "");

				$this->select($RT["ON_PAGE"], $nv["page_".$pre], "page_".$pre, "", "slc", "",
					"onchange=\"ms.set_vl('from_".$pre."', '0'); ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("td");

			$this->tg_open("td", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_FROM, "");

				$this->select($RT["FROM"], $nv["from_".$pre], "from_".$pre, "from_".$pre, "slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("td");

			$this->tg_open("td", "", "", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_ORDER_BY, "");

				$this->select($RT["FIELD_ST"], $nv["order_".$pre], "order_".$pre, "", "slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $k;},
					function($k, $v){return $k;},
					function($k, $v){return $this->html($v);});

				if($nv["order_desc_".$pre] === "DESC"){

					$this->checkbox("order_desc_".$pre, "", "ct_check", "",
						"onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"", "checked");
				}
				else{

					$this->checkbox("order_desc_".$pre, "", "ct_check", "desc",
						"onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"", "");
				}


				$this->tg("label", "", "", "", "DESC", "");

			$this->tg_close("td");

			$this->tg_close("tr");

		$this->tg_close("table");

		if($pre === "tb"){

			$this->filter_set($nv, "db");
		}
		if($pre === "rc"){

			$this->filter_set($nv, "db");

			$this->filter_set($nv, "tb");
		}

		$fl_display = "display: none;";

		foreach($nv["fl_value_".$pre] as $k=>$v)
		{
			if((($nv["fl_operator_".$pre][$k] !== _NOTE_FILTER_OPERATOR) && ($v !== "")) ||
					($nv["fl_operator_".$pre][$k] === "IS NULL") ||
					($nv["fl_operator_".$pre][$k] === "IS NOT NULL")
			){
					$fl_display = "";
			}
		}

		$this->tg_open("div", "flc_".$pre."", "", $fl_display, "");

		$this->tg_open("div", "", "res", "", "");

		$this->tg("div", "", "separator3", "", "", "");

		$this->tg_open("table", "", "nav_wrap_f", "", "");

		foreach($RT["FIELD_ST_NAV"] as $k=>$v)
		{
			$add_fl_value = "";
			if(isset($nv["fl_value_".$pre][$k])){

				$add_fl_value = $nv["fl_value_".$pre][$k];
			}

			$add_fl_operator = "";
			if(isset($nv["fl_operator_".$pre][$k])){

				$add_fl_operator = $nv["fl_operator_".$pre][$k];
			}

			$add_fl_operator_and = "";
			if(isset($nv["fl_and_".$pre][$k])){

				$add_fl_operator_and = $nv["fl_and_".$pre][$k];
			}

			$this->tg_open("tr", "", "", "", "");

			$this->tg_open("td", "", "nav_wrap_filter", "", "");

			if($k === 0){

				$this->input("fl_and_".$pre."[]", "", "nav_value", "", "", "hidden", "");
			}
			else{

				$this->select(["AND", "OR"], $add_fl_operator_and,
					"fl_and_".$pre."[]", "", "slc", "", "",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});
			}

			$this->tg_close("td");
			$this->tg_open("td", "", "", "", "");

			$this->input("fl_field_".$pre."[]", "", "nav_value",
				$this->html($RT["FIELD_ST_NAV"][$k]), "", "readonly", "");

			$this->tg_close("td");
			$this->tg_open("td", "", "nav_wrap_filter", "", "");

			$this->select($RT["FILTER_EX"][$v], $add_fl_operator,
				"fl_operator_".$pre."[]", "", "slc", "", "",
				function($k, $v){return $v;},
				function($k, $v){return $v;},
				function($k, $v){return $v;});

			$this->tg_close("td");
			$this->tg_open("td", "", "", "", "");

			$this->input("fl_value_".$pre."[]", "", "nav_value",
				$this->html($add_fl_value), "", "", _NOTE_FILTER_VALUE);

			$this->tg_close("td");

			$this->tg_close("tr");
		}

		$this->tg_close("table");

		$this->tg("div", "", "separator3", "", "", "");

		$this->tg_close("div");

		$this->btn("", "", "btn", "OK", "onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

		$this->btn("", "", "btn", "RESET", "onclick=\"ms.RF('_RESET_FILTER_".$pre."', '', '', this.form, 0);\"");

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_close("div");

		$this->tg("div", "", "separator11", "", _NOTE_TOTAL." [ ".$RT["COUNT"]." ] ", "");
	}



}