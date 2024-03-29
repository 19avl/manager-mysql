<?php

/*
Copyright (c) 2018-2024 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();


Class View
{
	use Convert;
	use Wr_html;

private $ac_con;
private $ac_ex;

	public function __construct(){

		$this->ac_con = "['_DELETE_SH','_CLEAR_SH','_DELETE_TB','_CLEAR_TB']";

		$this->ac_ex = "['_EXPORT_SQL_SH','_EXPORT_CSV_SH','_EXPORT_XML_SH',
			'_EXPORT_SQL_TB','_EXPORT_CSV_TB','_EXPORT_XML_TB',]";
	}

	public function dl_message()
	{
		$this->tg_open("div", "id_alt_message", "altDl", "display: none;", "");

		$this->tg("div", "id_alt_message_text", "altDl_text", "", "", "");

		$this->btn("", "", "altDl_btn", _NOTE_CONFIRM_YES, "onclick=\"ms.el_va('id_alt_message', 'none');\"");

		$this->tg_close("div");
	}

	private function dl_confirm($id)
	{
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


	public function message($log)
	{
		if(isset($log["MESSAGE"]) && (count($log["MESSAGE"]) !== 0))
		{
			$this->tg_open("div", "div_message", "res_message", "", "");

			$this->tg("div", "", "res_message_close", "", "&#10006;", "onclick=\"ms.el_va('div_message', 'none');\"");

			foreach($log["MESSAGE"] as $value){

				$this->tg("div", "", "message", "", $value, "");
			}

			$this->tg_close("div");
		}

		if(isset($log["RESULT"]) && (count($log["RESULT"]) !== 0))
		{
			$this->tg_open("div", "div_result", "res", "", "");

			foreach($log["RESULT"] as $value){

				$this->tg("div", "", "result", "", $value, "");
			}

			$this->tg_close("div");

			$this->tg("div", "", "separator11", "", "", "");
		}
	}


	public function main($user, $_SH, $_TB, $nv)
	{
		$this->tg("div", "", "nav_main_back_un", "", "", "");

		$this->tg("div", "", "nav_main_back", "", "", "");

		$this->tg_open("div", "", "nav_main", "", "");

		$this->form_open("nav_main_form", "reload");

		$this->form_set($_SH, $_TB,
			$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"], $nv["view_rc"]);

		$this->filter_set($nv, "sh");

		$this->filter_set($nv, "tb");

		$this->filter_set($nv, "rc");

		$this->tg("div", "", "btn_nav_first_im", "",
"<svg baseProfile='full' width='47' height='47'>
<path d='M 33 21 L 33 15 L 15 15 L 15 30 L 35 30' fill='transparent' stroke='white' stroke-width='2'/>
<path d='M28 20 L 33 26 L 38 20 Z' fill='white'/></svg>",
			"onclick=\"ms.RF('VIEW', '', '".$_TB."', document.getElementById('reload'), 0);\"");

		$this->form_close();

		$this->btn("", "", "btn_nav", _NOTE_SQL, "onclick=\"ms.view_wr('id_wr_script', '');\"");

		$this->tg("div", "", "separator0", "", "", "");

		$this->form_open();

		$this->form_set("", "",
			$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"], $nv["view_rc"]);

		$this->filter_set($nv, "sh");

		$this->filter_set($nv, "tb");

		$this->filter_set($nv, "rc");

		$this->tg_open("div", "", "nav_main_nv", "", "");

		$this->tg("div", "", "separator3", "", "", "");

		$this->btn("", "", "rt_label_nv", "[ ".$user." ]",
			"onclick=\"window.scrollTo(0,0); ms.RF('VIEW', '', '', this.form, 0);\"");


		if($_SH !== "")
		{
			$this->btn("", "", "rt_label_nv", "[ ".$this->html($this->h2s($_SH))." ]",
				"onclick=\"window.scrollTo(0,0); ms.RF('VIEW', '".$_SH."', '', this.form, 0);\"");
		}
		if($_TB !== "")
		{
			$this->btn("", "", "rt_label_nv", "[ ".$this->html($this->h2s($_TB))." ]",
				"onclick=\"window.scrollTo(0,0); ms.RF('VIEW', '".$_SH."', '".$_TB."', this.form, 0);\"");
		}

		$this->tg_close("div");

		$this->form_close();

		$this->tg_close("div");
	}


	public function sql($_SH, $_TB, $SCRIPT, $SQL_SL, $nv, $display)	
	{
		if($display === "sql"){

			$this->tg_open("div", "id_wr_script", "wr_main_nav", "", "");
		}
		else{

			$this->tg_open("div", "id_wr_script", "wr_main_nav", "display: none;", "");
		}

		$this->form_open();

		$this->form_set($_SH, $_TB,
			$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->filter_set($nv, "sh");

		$this->filter_set($nv, "tb");

		$this->input("display", "", "", "sql", "", "hidden", "");

		$this->tg_open("div", "", "ct_row", "", "");

		if(isset($SCRIPT["userscripts"])){
		
			$this->select(array_keys($SCRIPT["userscripts"]), "", "--", "script_id", "", "slc", "userscripts",
				"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
				function($k, $v){return $v;},
				function($k, $v){return $this->html($v);},
				function($k, $v){return $this->html($v);});	
		}				

		if(isset($SCRIPT["objects"])){

			foreach($SCRIPT["objects"] as $k=>$v)
			{

				$this->select($v, "", "--", $k, $k."_id", "slc", $k,
					"onchange=\"ms.IRText('".$k."_id', 'script_text_sql', this);\"",
					function($k, $v){return $k;},
					function($k, $v){return $this->html($v);},
					function($k, $v){return $this->html($k);});	
			}		
		}

		$this->tg_close("div");

		$this->form_close();

		$this->form_open();

		$this->form_set($_SH, $_TB,
			$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->filter_set($nv, "sh");

		$this->filter_set($nv, "tb");

		$this->textarea("script", "script_text_sql", "", $SQL_SL, "", "title='"._NOTE_SCRIPT_DROP."'");

		$this->btn("", "", "btn", _ACTION_RUN, "onclick=\"ms.RF('_RUN_SQL', '', '".$_TB."', this.form, 0);\"");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_close("div");
	}


	public function sh($user, $_SH, $_TB, $RT, $nv)
	{
		$this->tg("div", "", "rt_label_tl1", "", _NOTE_SERVER.": ".$user, "");

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_open("div", "id_wr_db", "wr_main_nav", "", "");

		$this->form_open();

		$this->form_set($_SH, $_TB,
			"", "", "", "",
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->tg_open("div", "", "pl_el", "", "");

		$this->select(
			[_NOTE_SEARCH_M0, _NOTE_SEARCH_M1],
			"", "", "cl_df", "", "st_select_value", "", "",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->input("cl_in", "search_db", "st_value_D", "",
			"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

		$this->btn("", "", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_TL', '', '', this.form, 0, 'search_db', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tg_close("div");

		$this->tg("div", "", "separator3", "", "", "");

		$this->nav($RT, $nv, "sh");

		$this->form_close();

		if( count($RT["SH"]) !== 0 )
		{
			$this->form_open();

			$this->form_set($_SH, "",
				$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
				"", "", "", "", "", "", "", []);

			$this->filter_set($nv, "sh");

			$this->tg_open("div", "", "ct_row", "", "");

			$this->tg_open("div", "", "pl_el", "", "");

			$this->checkbox("totalH", "", "ct_check", "checkbox",
				"onclick=\"ms.check_sl(this.form,'list_sh[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

			$this->input("", "", "ct_name_title", _NOTE_SCHEMAS, "", "disabled", "");

			$this->input("", "", "ct_info_A", "COUNT", "", "readonly", "");

			$this->input("", "", "ct_info_D", $RT["FIELD_SE"][$nv["field_sh"]], "", "readonly", "");

			$this->tg_close("div");

			foreach($RT["SH"] as $key=>$value)
			{
				$uk = $this->s2h($key);

				$this->tg_open("div", "", "pl_el", "", "");

				$this->checkbox("list_sh[]", "", "ct_check", $uk, "onclick=\"ms.el_va('id_alt_message', 'none');\"", "");

				$this->input("", "", "ct_name", $this->html($key),
					"onclick=\"ms.RF('VIEW', '".$uk."', '', this.form, 0);\"", "", "");

				$this->input("", "", "ct_info_A", $value["count"], "", "readonly", "");

				$this->input("", "", "ct_info_D", $value[$RT["FIELD_SE"][$nv["field_sh"]]], "", "readonly", "");

				$this->tg_close("div");
			}

			$this->checkbox("totalF", "", "ct_check", "checkbox",
				"onclick=\"ms.check_sl(this.form,'list_sh[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

			$this->dl_confirm("id_cn_db");

			$this->select($RT["ACS"],
				false, "", "", "", "st_select_value", _NOTE_SELECT,
					"onchange=\"ms.AL(this.value, 'id_cn_db', this, this.form, 'list_sh[]',
					'"._NOTE_SCHEMAS." / "._NOTE_SELECT." / ',
					".$this->ac_con.",".$this->ac_ex.",'"._MESSAGE_SH_CHECK."' ); \"",
				function($k, $v){return $v;},
				function($k, $v){return $k;},
				function($k, $v){return $v;});

			$this->tg_close("div");

			$this->tg("div", "", "separator11", "", "", "");

			$this->form_close();
		}

		$this->tg_close("div");
	}


	public function tb($_SH, $RT, $action, $nv)
	{
		if($RT["CREATE"] == ""){return;}

		$_SHS = $this->html($this->h2s($_SH));

		$this->tg("div", "", "rt_label_tl1", "", _NOTE_SCHEMA.": ".$_SHS, "");

		$this->tg("div", "", "separator11", "", "", "");

		$this->form_open();

		$this->form_set($_SH, "", "", "", "", "", "", "", "", "", "", "", "", []);

		$this->tg_open("div", "", "pl_el", "", "");

		$this->select(
			[_NOTE_SEARCH_M0, _NOTE_SEARCH_M1],
			"", "", "cl_df", "", "st_select_value", "", "",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->input("cl_in", "search_db", "st_value_D", "",
			"onclick=\"ms.el_va('id_alt_message', 'none');\"", "", "");

		$this->btn("", "", "st_btn", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_SH', '".$_SH."', '', this.form, 0, 'search_db', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tg_close("div");

		$this->tg("div", "", "separator3", "", "", "");

		$this->nav($RT, $nv, "tb");

		$this->form_close();

		if( count($RT["TABLES"]) === 0 ){return;}

		$this->form_open();

		$this->form_set($_SH, "",
			$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", []);

		$this->filter_set($nv, "sh");

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
			if($value["count"] === "IN USE"){

				$flag = "disabled";
			}

			$this->input("", "", "ct_name", $this->html($key),
				"onclick=\"ms.RF('VIEW', '', '".$uk."', this.form, 0);\"", $flag, "");

			$this->input("", "", "ct_info_A", $value["count"], "", "readonly", "");

			$this->input("", "", "ct_info_D", $value[$RT["FIELD_SE"][$nv["field_tb"]]], "", "readonly", "");

			$this->tg_close("div");
		}

		$this->checkbox("totalF", "", "ct_check", "checkbox",
			"onclick=\"ms.check_sl(this.form,'list_tb[]',this.checked); ms.el_va('id_alt_message', 'none');\"", "");

		$this->dl_confirm("id_cn_tbr");

		$this->select($RT["ACS"],
			false, "", "list_LT_sl", "", "st_select_value", _NOTE_SELECT,
			"onchange=\"ms.AL(this.value, 'id_cn_tbr', this, this.form, 'list_tb[]',
			'"._NOTE_TABLES." / "._NOTE_SELECT." / ',
			".$this->ac_con.",".$this->ac_ex.",'"._MESSAGE_TB_CHECK."' ); \"",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->tg_close("div");

		$this->form_close();

		$this->tg("div", "", "separator11", "", "", "");
	}


	public function rc($_SH, $_TB, $RT, $nv, $FUNCTION, $ext)
	{
		$_SHS = $this->html($this->h2s($_SH));
		$_TBS = $this->html($this->h2s($_TB));

		if($RT["CREATE"] == ""){return;}

		$this->tg("div", "", "rt_label_tl2", "", _NOTE_TABLE.": ".$_TBS,
			"onclick=\"window.scrollTo(0,0); ms.el_vb('tb_id');\"");

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_open("div", "tb_id", "pl_el", "display: none;", "");

			if($RT["TABLE_TYPE"] === "BASE TABLE")
			{
				$this->rc_data($_SH, $_TB, $RT, $nv, $FUNCTION, $ext, "insert");
			}

		$this->tg_close("div");

		$this->form_open();

		$this->form_set($_SH, $_TB,
			$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
			$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
			"", "", "", [], $nv["view_rc"]);

		$this->tg_open("div", "", "pl_el", "", "");

		$this->select(
			[_NOTE_SEARCH_M0, _NOTE_SEARCH_M1],
			"", "", "cl_df", "", "st_select_value", "", "",
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

				$this->rc_data($_SH, $_TB, $RT, $nv, $FUNCTION, $ext, "edit");
			}
			else{

				$this->rc_data_list($_SH, $_TB, $RT, $nv, $FUNCTION, $ext, "edit");
			}
		}

		$this->rcdl($FUNCTION);
	}


	private function rc_data_list($_SH, $_TB, $RT, $nv, $FUNCTION, $ext, $mod)
	{
		$this->tg_open("table", "", "rt_list_tb", "", "");

			$this->tg_open("tr", "", "", "", "");

				$this->tg_open("td", "", "", "", "");

					$this->form_open();

					$this->form_set($_SH, $_TB,
						$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
						$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
						$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

					$this->filter_set($nv, "sh");

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

		$count = 0;

		foreach($RT["RECORDS"] as $value)
		{
			$count += 1;

			$this->form_open();

			$this->form_set($_SH, $_TB,
				$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->filter_set($nv, "sh");

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
						$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);

						$class = "rt_value_list";

						if($flag === "disabled"){

							$class = "rt_label_list";
						}

						$uk = $this->s2h($k);

						if($RT["FIELDS"][$k]["COLUMN_KEY"] === "PRI")
						{
							$this->input("key[".$uk."]", "", "", $this->s2h($v), "", "hidden", "");
						}

						if((count($nv["field_rc"]) === 0) || (in_array($this->s2h($k), $nv["field_rc"])))
						{
							$this->tg_open("td", "", "", "", "");

							if(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["geo"]))
							{
								$this->input("", "", $class, $this->html($v), "", $flag, "");
							}
							elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["binary"]))
							{
								$this->input("", "", $class, $v, "", $flag, "");
							}
							elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["blob"]))
							{
								$this->input("", "", $class, $this->get_seze($this->h2s($v)), "", "disabled", "");
							}
							elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["text"]))
							{
								$this->input("", "", $class, $this->get_seze($v), "", "disabled", "");
							}
							else
							{
								if(($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
									($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL") ||
									($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL GENERATED") ||
									($RT["FIELDS"][$k]["EXTRA"] === "STORED GENERATED") ||
									($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP")){

										$class = "rt_label_list";
										$flag = "disabled";
								}

								$v = preg_replace("/\\n/", " ", (string)$v);

								$this->input("", "", $class, $this->html($v), "", $flag, "");
							}

							$this->tg_close("td");
						}
					}

				$this->tg_close("tr");

			$this->tg_close("table");

			$this->form_close();
		}

		$this->tg("div", "", "separator11", "", "", "");
	}


	private function rc_data($_SH, $_TB, $RT, $nv, $FUNCTION, $ext, $mod)
	{
		if($mod === "edit")
		{
			$this->form_open();

			$this->form_set($_SH, $_TB,
				$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

			$this->filter_set($nv, "sh");

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

			$this->form_set($_SH, $_TB,
				$nv["page_sh"], $nv["from_sh"], $nv["order_sh"], $nv["field_sh"],
				$nv["page_tb"], $nv["from_tb"], $nv["order_tb"], $nv["field_tb"],
				"", "", $nv["order_rc"], $nv["field_rc"]);

			$this->filter_set($nv, "sh");

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

						$this->tg("label", "", "", "", $this->html($vst), "");

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
					($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "bit") ||
					($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
					($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP") ||
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
						$v, "", "hidden", "");

					$this->input("field[".$uk."]", "text".$count.$count_fl.$uk.$mod, $class,
						$v, "", $data_flag, "");
				}
				elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $ext["blob"]))
				{
					$v = $this->h2s($v);

					if(($mod === "edit") && (trim((string)$v) !== "")){

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

						if(($mod === "edit") && (trim((string)$v) !== ""))
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

						if(($mod === "edit") && ($RT["PRI"] === true) )
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

					if(($mod === "edit") && (trim((string)$v) !== ""))
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

			$this->dl_confirm("id_cn_rc".$count.$mod);

			$this->tg("div", "", "separator3", "", "", "");

			if($this->h2s($_SH) !== "information_schema")
			{
				if(($count_fl > 0) && ($count_fl_display > 0))
				{
					if($RT["PRI"])
					{
						if($mod === "edit")
						{
							$this->btn("", "", "btn", _ACTION_DELETE,
								"onclick=\"ms.AT('_DELETE_RC', 'id_cn_rc".$count.$mod."', this, this.form,
								'"._NOTE_ROW." / "._ACTION_DELETE."', ['_DELETE_RC'], []); \"");

							$this->btn("", "", "btn", _ACTION_UPDATE,
								"onclick=\"ms.RF('_UPDATE_RC', '', '', this.form, 0);\"");
						}
					}

					$this->btn("", "", "btn", _ACTION_INSERT,
						"onclick=\"ms.RF('_INSERT_RC', '', '',this.form, 0);\"");
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
		$size = strlen((string)$v);
		
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


	private function form_set($_SH, $_TB,
		$page_sh, $from_sh, $order_sh, $field_sh,
		$page_tb, $from_tb, $order_tb, $field_tb,
		$page_rc, $from_rc, $order_rc, $field_rc, $view_rc="")
	{
		$this->input("action", "", "", "", "", "hidden", "");

		$this->input("sh", "", "", $_SH, "", "hidden", "");
		$this->input("tb", "", "", $_TB, "", "hidden", "");

		if($page_sh != ""){$this->input("page_sh", "", "", $page_sh, "", "hidden", "");}
		if($from_sh != ""){$this->input("from_sh", "", "", $from_sh, "", "hidden", "");}
		if($order_sh != ""){$this->input("order_sh", "", "", $order_sh, "", "hidden", "");;}
		if($field_sh != ""){$this->input("field_sh", "", "", $field_sh, "", "hidden", "");}

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
					$this->select($RT["FIELD_SE"], $nv["field_".$pre], "", "field_".$pre, "", "slc", "",
						"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
						function($k, $v){return $k;},
						function($k, $v){return $k;},
						function($k, $v){return $this->html($v);});
				}

			$this->tg_close("td");

			$this->tg_open("td", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_LIMIT, "");

				$this->select($RT["ON_PAGE"], $nv["page_".$pre], "", "page_".$pre, "", "slc", "",
					"onchange=\"ms.set_vl('from_".$pre."', '0'); ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("td");

			$this->tg_open("td", "", "nav_wrap", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_FROM, "");

				$this->select($RT["FROM"], $nv["from_".$pre], "", "from_".$pre, "from_".$pre, "slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("td");

			$this->tg_open("td", "", "", "", "");

				$this->tg("div", "", "nav_label", "", _NOTE_ORDER_BY, "");

				$this->select($RT["FIELD_ST"], $nv["order_".$pre], "", "order_".$pre, "", "slc", "",
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

			$this->filter_set($nv, "sh");
		}
		if($pre === "rc"){

			$this->filter_set($nv, "sh");

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

				$this->select(["AND", "OR"], $add_fl_operator_and, "",
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

			$this->select($RT["FILTER_EX"][$v], $add_fl_operator, "",
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


	private function privileges_rc($k, $PRIVILEGES, $mod)
	{
		$RT = "";

		if($mod === "edit"){$action = "UPDATE";}
		else{$action = "INSERT";}

		if(isset($PRIVILEGES["TABLE_PRIVILEGES"]) || isset($PRIVILEGES["COLUMN_PRIVILEGES"]))
		{
			if(in_array($action, $PRIVILEGES["TABLE_PRIVILEGES"]) ||
				(isset($PRIVILEGES["COLUMN_PRIVILEGES"][$action]) &&
				in_array($k, $PRIVILEGES["COLUMN_PRIVILEGES"][$action]))
			){
				$RT = "";
			}
			else{$RT = "disabled";}
		}

		return $RT;
	}

}