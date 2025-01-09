<?php

/*
Copyright (c) 2018-2025 Andrey Lyskov
This project is licensed under the MIT License - see the LICENSE.md file
*/

defined("_EXEC") or die();

Class View
{
	private $GT;
	private $priv;
	private $mod_nav;

	private $bookmark;
	private $bookmark_view;
	private $profile;
	private $server_view;
	private $ac_con;
	private $ac_ex;

	public function __construct($user, $GT)
	{
		$this->GT = $GT;

		$this->ac_con = "['_DELETE_SH_FILTER','_CLEAR_SH_FILTER','_DELETE_SH','_CLEAR_SH',
			'_DELETE_TB_FILTER','_CLEAR_TB_FILTER','_DELETE_TB','_CLEAR_TB',
			'_DELETE_RC_FILTER','_DELETE_RC']";

		$this->ac_ex = "[
			'_SAVE_SQL_SH_FILTER','_SAVE_SQL_SH',
			'_SAVE_SQL_TB_FILTER','_SAVE_SQL_TB',
			'_SAVE_SQL_RC_FILTER']";
	}


	public function dl_ms()
	{
		$this->tg_open("div", "id_alt_message", "altDl", "display: none;", "");

		$this->tgFr_input("button", "", "", "altDl_btn", _NOTE_CONFIRM_YES, "onclick=\"ms.view_va('id_alt_message', 'none');\"");

		$this->tg("div", "id_alt_message_text", "altDl_text", "", "", "");

		$this->tg_close("div");
	}


	private function dl_confirm($id)
	{
		$this->tg_open("div", $id, "confirmDl", "display: none;", "");

		$this->tg_open("div", "", "", "", "");

		$this->tg("div", "", "confirmDl_title", "", _NOTE_CONFIRM, "");

		$this->tg("div", $id."_text", "confirmDl_text", "", "", "");

		$this->tgFr_input("button", "", "", "confirmDl_btn", _NOTE_CONFIRM_YES,
			"onclick=\"ms.RF('', '', '', this.form, 0); ms.view_va('".$id."', 'none');\"");

		$this->tgFr_input("button", "", "", "confirmDl_btn", _NOTE_CONFIRM_NO, "onclick=\"ms.view_va('".$id."', 'none'); \"");

		$this->tg_close("div");

		$this->tg_close("div");
	}


	private function rcdl($FUNCTION)
	{
		$this->tgFr_input("hidden", "", "rcDl_buf_id", "", "", "", "");

		$this->tg("div", "rcDl_ground", "confirmDl", "display: none;", "", "");

		$this->tg_open("div", "rcDl_window", "rcDl_window", "display: none;", "");

			$this->tg_open("div", "rcDl_nav", "rcDl_nav", "", "");

			$this->tg_open("div", "function_dv", "", "display: none;", "");

				$this->tgFr_input("hidden", "", "text_id", "", "", "", "");

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
						"onclick=\"ms.set_rcdl_function('rcDl_buf_id', 'text_id', '".$k."', '".
						addslashes($vs)."', '".addslashes($vs_add)."', '".$count."');\"");
				}

				$this->tg_close("div");

				$this->tg("div", "", "separator3", "", "", "");

				$this->tgFr_input("button", "", "", "confirmDl_btn", _NOTE_CONFIRM_UNSET,
					"onclick=\"ms.unset_rcdl_function('rcDl_buf_id', 'text_id');\"");

				$this->tgFr_input("button", "", "", "confirmDl_btn", _NOTE_CONFIRM_YES, "onclick=\"ms.close_rcdl();\"");

			$this->tg_close("div");

			$this->tg_close("div");

		$this->tg_close("div");
	}


	public function ms($rs)
	{
		if(isset($rs["RESULT"]) && (count($rs["RESULT"]) !== 0))
		{
			$this->tg_open("div", "div_result", "nav_wr_result", "", "");

			foreach($rs["RESULT"] as $value){

				$this->tg("div", "", "nav_result", "", $this->html($value, "\n", "<br>"), "");
			}

			$this->tg_close("div");

			$this->tg("div", "", "separator11", "", "", "");
		}

		if(isset($rs["MESSAGE"]) && (count($rs["MESSAGE"]) !== 0))
		{
			$this->tg_open("div", "div_message", "nav_wr_message", "", "");

			$this->tg("div", "", "nav_wr_message_close", "", "&#10006;", "onclick=\"ms.view_va('div_message', 'none');\"");

			foreach($rs["MESSAGE"] as $value){

				$this->tg("div", "", "nav_message", "", $this->html($value, "\n", "<br>"), "");
			}

			$this->tg_close("div");
		}
	}


	public function main($user, $nv, $display)
	{
		$this->tg("div", "", "nav_main_back_un", "", "", "");
		$this->tg("div", "", "nav_main_back", "", "", "");

		$this->tg_open("div", "", "nav_main", "", "");

		$this->tgFr_open("nav_main_form", "reload");

		$this->form_set($nv["_US"], $nv["_SV"], $nv["_SH"], $nv["_TB"],
			$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

		$this->filter_set($nv);

		$this->tg("div", "", "btn_nav_first_im", "",
			"<svg width='47' height='47'>".
			"<path d='M27 22 L33 22 L33 16 Z' fill='white' />".
			"<path d='M32,24 A8,8 0 1,1 30.92,20.00'></svg>",
			"onclick=\"ms.RF('VIEW', '', '".$nv["_TB"]."', document.getElementById('reload'), 0);\" ".
			"title='"._ACTION_RELOAD."'");

		$this->tgFr_close();

		$this->tgFr_open("nav_main_form");

		$style_wr_script = "btn_nav";
		if($display == "sql"){
	
			$style_wr_script = "btn_nav_focus";	
		}
	
		$this->tgFr_input("button", "", "nav_id_wr_script", $style_wr_script, _NOTE_SQL,
			"onclick=\"ms.view_wr('id_wr_script',[''], this);\"");			

		$this->tgFr_input("hidden", "session", "", "", "", "", "");

		$this->tgFr_input("hidden", "action", "", "", "", "", "");

		$this->tgFr_input("button", "", "", "btn_nav", _ACTION_EXIT,
			"onclick=\"as.clr_ps(); ms.RF('VIEW', '', '', this.form, 0);\"");

		$this->tgFr_close();

		$this->tg("div", "", "separator0", "", "", "");

		$this->tgFr_open();

		$this->form_set($nv["_US"], $nv["_SV"], "", "", $nv["page_rc"], "", "", []);

		$this->tg_open("div", "", "nav_main_nv", "", "");

		$this->tgFr_input("button", "", "", "nav_label_nv", "[ ".$user." ]",
			"onclick=\"window.scrollTo(0,0); ms.RF('VIEW', '', '', this.form, 0);\"");

		if($nv["_SH"] !== "")
		{
			$this->tgFr_input("button", "", "", "nav_label_nv", "[ "._NOTE_SCHEMA.": ".$this->html(hex2bin($nv["_SH"]))." ]",
				"onclick=\"window.scrollTo(0,0); ms.RF('VIEW', '".$nv["_SH"]."', '', this.form, 0);\"");
		}
		if($nv["_TB"] !== "")
		{
			$this->tgFr_input("button", "", "", "nav_label_nv", "[ "._NOTE_TABLE.": ".$this->html(hex2bin($nv["_TB"]))." ]",
				"onclick=\"window.scrollTo(0,0); ms.RF('VIEW', '".$nv["_SH"]."', '".$nv["_TB"]."', this.form, 0);\"");


			$this->tgFr_input("button", "", "", "nav_label_nv", "[ "._ACTION_INSERT." ]",
				"onclick=\"window.scrollTo(0,0); ms.view_vb('tb_id');\"");
		}

		$this->tg_close("div");

		$this->tgFr_close();

		$this->tg_close("div");
	}


	public function sql($SCRIPT, $SQL_SL, $nv, $display)
	{
		if($display === "sql"){

			$this->tg_open("div", "id_wr_script", "nav_wr_main", "", "");
		}
		else{

			$this->tg_open("div", "id_wr_script", "nav_wr_main", "display: none;", "");
		}

		$this->tgFr_open();

		$this->form_set($nv["_US"], $nv["_SV"], $nv["_SH"], $nv["_TB"], $nv["page_rc"], $nv["from_rc"], $nv["order_rc"], []);

		$this->tgFr_input("hidden", "display", "", "", "sql", "", "");


		$this->tg_open("div", "", "nav_wr_script", "", "");

			if(isset($SCRIPT["userscripts"])){

				$this->select(array_keys($SCRIPT["userscripts"]), "", "--", "script_id", "", "sc_slc1", "userscripts",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $this->html($v);},
					function($k, $v){return $this->html($v);});
			}

			if(isset($SCRIPT["objects"])){

				foreach($SCRIPT["objects"] as $k=>$v)
				{
					$this->select($v, "", "--", $k, $k."_id", "sc_slc1", $k,
						"onchange=\"ms.edit_text('".$k."_id', 'script_text_sql', this);\"",
						function($k, $v){return $k;},
						function($k, $v){return $this->html($v);},
						function($k, $v){return $this->html($k);});
				}
			}

		$this->tg_close("div");

		$this->tgFr_close();

		$this->tgFr_open();

		$this->form_set($nv["_US"], $nv["_SV"], $nv["_SH"], $nv["_TB"], $nv["page_rc"], $nv["from_rc"], $nv["order_rc"], []);

		$this->textarea("script", "script_text_sql", "", $SQL_SL,
			"onclick=\"ms.view_va('id_alt_message', 'none');\"", "title='"._NOTE_SCRIPT_DROP."'", "");

		$this->tg("div", "", "separator0", "", "", "");

		$this->tgFr_input("button", "", "", "sc_btn", _ACTION_RUN,
			"onclick=\"ms.AV('_RUN_SQL', '', '".$nv["_TB"]."', this.form, 0, 'script_text_sql', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tgFr_close();

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_close("div");
	}


	public function rc($RT, $nv, $FUNCTION)
	{
		if($RT["CREATE"] == ""){return;}

		$this->tg_open("div", "tb_id", "dt_wr", "display: none;", "");

		$count = 0;

		foreach($RT["DATA_NEW"] as $value)
		{
			$count += 1;

			$this->rc_data($RT, $value, $count, $nv, "insert");
		}

		$this->tg_close("div");

		$this->tgFr_open();

		$this->form_set($nv["_US"], $nv["_SV"], $nv["_SH"], $nv["_TB"], "", "", "", [], $nv["view_rc"]);

		$this->tg_open("div", "", "dt_wr", "", "");

		$this->select(
			[_NOTE_SEARCH_M0, _NOTE_SEARCH_M1],
			"", "", "cl_df", "", "dt_slc", "", "",
			function($k, $v){return $v;},
			function($k, $v){return $k;},
			function($k, $v){return $v;});

		$this->tgFr_input("text", "cl_in", "search_tb", "dt_in_search", "",
			"onclick=\"ms.view_va('id_alt_message', 'none');\"", "");

		$this->tgFr_input("button", "", "", "dt_btn_search", _ACTION_FIND,
			"onclick=\"ms.AV('_FIND_TB', '', '".$nv["_TB"]."', this.form, 0, 'search_tb', '"._MESSAGE_NOT_VALUE."');\"");

		$this->tg_close("div");

		$this->tg("div", "", "separator3", "", "", "");

		$this->filter($RT, $nv);

		$this->tgFr_close();

		$this->tg("div", "", "separator3", "", "", "");

		if($RT["COUNT"] !== 0)
		{
			if($nv["view_rc"] === "table")
			{
				$this->tgFr_open();

				$this->form_set($nv["_US"], $nv["_SV"], $nv["_SH"], $nv["_TB"],
					$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"]);

				$this->filter_set($nv);

				$this->tgFr_input("button", "", "", "dt_btn_list", "...",
					"onclick=\"ms.RF('VIEW', '".$nv["_SH"]."', '".$nv["_TB"]."', this.form, 0);\"", "title='"._NOTE_CLOSE."'");

				$this->tgFr_close();

				$this->tg("div", "", "separator11", "", "", "");

				$count = 0;

				foreach($RT["DATA"] as $value)
				{
					$count += 1;

					$this->rc_data($RT, $value, $count, $nv, "edit");
				}
			}
			else
			{
				if(($nv["_SH"] === "") || ($nv["_TB"] === ""))
				{
					$this->tgFr_open();

					$this->form_set($nv["_US"], $nv["_SV"], $nv["_SH"], $nv["_TB"],
						$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], []);

					$this->rc_data_list($RT, $nv, "edit");

					$this->tgFr_close();

					$this->tg("div", "", "separator11", "", "", "");
				}
				else
				{
					$this->rc_data_list($RT, $nv, "edit");

					$this->tg("div", "", "separator11", "", "", "");
				}
			}
		}

		$this->rcdl($FUNCTION);
	}


	private function rc_data_list($RT, $nv, $mod)
	{
		if(count($RT["DATA"]) === 0){return;}

		$this->tg_open("table", "", "dt_list_table", "", "");

			$this->tg_open("tr", "", "", "", "");

				if(($nv["_SH"] === "") || ($nv["_TB"] === ""))
				{
					$this->tg_open("td", "", "", "", "");

						$this->tgFr_input("checkbox", "totalH", "", "dt_check", "checkbox",
							"onclick=\"ms.set_list_ch_fr(this.form,'list_rc[]',this.checked);
							ms.view_va('id_alt_message', 'none');\"", "");

					$this->tg_close("td");

					$this->tg_open("td", "", "", "", "");

						$this->tgFr_input("button", "", "", "dt_btn_list", "&#160;", "");

					$this->tg_close("td");
				}
				else
				{
					$this->tg_open("td", "", "", "", "");

						$this->tgFr_open();

						$this->form_set($nv["_US"], $nv["_SV"], "", "",
							$nv["page_rc"], $nv["from_rc"], $nv["order_rc"], $nv["field_rc"], "table");

						$this->filter_set($nv);

						$this->tgFr_input("button", "", "", "dt_btn_list", "...",
							"onclick=\"ms.RF('VIEW', '".$nv["_SH"]."', '".$nv["_TB"]."', this.form, 0);\"",
							"title='"._NOTE_OPEN."'");

						$this->tgFr_close();

					$this->tg_close("td");
				}

				foreach($RT["DATA"][0] as $k=>$value)
				{
					$is_extra = $RT["FIELDS"][$k]["EXTRA"];

					if($RT["FIELDS"][$k]["IS_NULLABLE"] === "NO"){

						$is_extra = "not null ".$is_extra;
					}

					if((count($nv["field_rc"]) === 0) || (in_array(bin2hex((string)$k), $nv["field_rc"])))
					{
						$this->tg_open("td", "", "", "", "");

						$constraint = "";
						foreach($RT["FIELDS"][$k]["CONSTRAINT"] as $vc){

							$constraint .= $vc[0];
						}

						$DATA_TYPE = "";
						if($RT["FIELDS"][$k]["DATA_TYPE"] !== ""){

							$DATA_TYPE = " [ ".$RT["FIELDS"][$k]["DATA_TYPE"]." ] ";
						}

						$this->tgFr_input("text", "", "", "dt_in_label_list", $constraint.$DATA_TYPE.$this->html("$k"),
							"", "title='".$this->html("$k")." ".$is_extra."' disabled");
							
						$this->tg_close("td");
					}
				}

			$this->tg_close("tr");

		$this->tg_close("table");

		$count = 0;

		foreach($RT["DATA"] as $value)
		{
			$count += 1;

			$this->tg_open("div", $nv["_SH"]." - ".$nv["_TB"]." - ".$count."_rc_list", "", "", "");

			$this->tg_open("table", "", "dt_list_table", "", "");

				$this->tg_open("tr", "", "", "", "");

					if($nv["_SH"] === "")
					{
						$this->tg_open("td", "", "", "", "");

						$name = bin2hex($value[$RT["SCHEMA_NAME_SL"]]);

						$this->tgFr_input("checkbox", "list_rc[]", "", "dt_check", $name, "onclick=\"ms.view_va('id_alt_message', 'none');\"", "");

						$this->tg_close("td");
					}
					elseif(($nv["_SH"] !== "") && ($nv["_TB"] === ""))
					{
						$this->tg_open("td", "", "", "", "");

						$name = bin2hex($value[$RT["TABLE_NAME"]]);

						$this->tgFr_input("checkbox", "list_rc[]", "", "dt_check", $name, "onclick=\"ms.view_va('id_alt_message', 'none');\"", "");

						$this->tg_close("td");
					}

					if($nv["_SH"] === "")
					{
						$this->tg_open("td", "", "", "", "");

						$name = bin2hex($value[$RT["SCHEMA_NAME"]]);

						$this->tgFr_input("button", "", "", "dt_btn_list", "...", 
							"onclick=\"ms.RF('VIEW', '".$name."', '', this.form, 0);\"",
							"title='"._NOTE_OPEN."'");

						$this->tg_close("td");
					}
					elseif(($nv["_SH"] !== "") && ($nv["_TB"] === ""))
					{
						$this->tg_open("td", "", "", "", "");

						$name = bin2hex($value[$RT["TABLE_NAME"]]);

						$this->tgFr_input("button", "", "", "dt_btn_list", "...",
							"onclick=\"ms.RF('_RESET_FILTER_rc', '".$nv["_SH"]."', '".$name."', this.form, 0);\"",
							"title='"._NOTE_OPEN."'");

						$this->tg_close("td");
					}
					else
					{
						$this->tg_open("td", "", "", "", "");

						$this->tgFr_input("button", "", "", "dt_btn_list", "...",
							"onclick=\"ms.view_vb('".$nv["_SH"]." - ".$nv["_TB"]." - ".$count."_cn');
							ms.view_vb('".$nv["_SH"]." - ".$nv["_TB"]." - ".$count."_rc_list');\"",
							"title='"._NOTE_OPEN."'");

						$this->tg_close("td");
					}

					foreach($value as $k=>$v)
					{
						$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);

						$class = "dt_in_value_list";

						if($flag === "disabled"){

							$class = "dt_in_label_list";
						}

						$uk = bin2hex((string)$k);

						if($RT["FIELDS"][$k]["COLUMN_KEY"] === "PRI")
						{
							$this->tgFr_input("hidden", "key[".$uk."]", "", "", bin2hex((string)$v), "", "");
						}

						if((count($nv["field_rc"]) === 0) || (in_array(bin2hex((string)$k), $nv["field_rc"])))
						{
							$this->tg_open("td", "", "", "", "");

							if(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["blob"])){

								$this->tgFr_input("text", "", "", $class, $this->get_size(hex2bin((string)$v)), "", "readonly");
							}
							elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["text"])){

								$this->tgFr_input("text", "", "", $class, $this->get_size($v), "", "readonly");
							}
							else{

								$this->tgFr_input("text", "", "", $class, $this->html($v), "", 
									"title='".$this->html($v)."' readonly");							
							}

							$this->tg_close("td");
						}
					}

				$this->tg_close("tr");

			$this->tg_close("table");

			$this->tg_close("div");

			if(($nv["_SH"] !== "") && ($nv["_TB"] !== ""))
			{
				$this->tg_open("div", $nv["_SH"]." - ".$nv["_TB"]." - ".$count."_cn", "", "display: none;", "");

				$this->tg("div", "", "separator11", "", "", "");

				$this->tgFr_input("button", "", "", "dt_btn_list", "...",
					"onclick=\"ms.view_vb('".$nv["_SH"]." - ".$nv["_TB"]." - ".$count."_cn');
					ms.view_vb('".$nv["_SH"]." - ".$nv["_TB"]." - ".$count."_rc_list');\"", "title='"._NOTE_CLOSE."'");

				$this->tg("div", "", "separator11", "", "", "");

				$this->tg("div", $nv["_SH"]." - ".$nv["_TB"]." - ".$count."_rc", "", "", "", "");

				$this->rc_data($RT, $value, $count, $nv, "edit");

				$this->tg("div", "", "separator11", "", "", "");

				$this->tg_close("div");
			}
		}

		$this->dl_confirm("id_cn_db");

		$this->tg("div", "", "separator3", "", "", "");

		if(($nv["_SH"] === "") || ($nv["_TB"] === ""))
		{
			$this->select($RT["ACS"],
				false, "", "", "", "dt_slc", _NOTE_SELECT,
					"onchange=\"ms.AL(this.value, 'id_cn_db', this, this.form, 'list_rc[]',
					'"._NOTE_SCHEMAS." / "._NOTE_SELECT." / ',
					".$this->ac_con.",".$this->ac_ex.",'"._MESSAGE_SH_CHECK."' );\"",
				function($k, $v){return $v;},
				function($k, $v){return $k;},
				function($k, $v){return $v;});
		}
	}


	public function rc_data($RT, $value, $count, $nv, $mod)
	{
		$this->tgFr_open();

		$this->form_set($nv["_US"], $nv["_SV"], $nv["_SH"], $nv["_TB"], "", "", $nv["order_rc"], $nv["field_rc"]);

		$this->filter_set($nv);

		$count_fl = 0;
		$count_fl_display = 0;

		$pac = false;

		foreach($value as $k=>$v)
		{
			$count_fl += 1;

			if((count($nv["field_rc"]) === 0) || (in_array(bin2hex((string)$k), $nv["field_rc"])) || ($mod === "insert")){

				$this->tg_open("div", "", "dt_wr", "", "");
				$count_fl_display += 1;
			}
			else{

				$this->tg_open("div", "", "", "display: none;", "");
			}

			$uk = bin2hex((string)$k);

			if($RT["FIELDS"][$k]["COLUMN_KEY"] === "PRI"){

				$this->tgFr_input("hidden", "key[".$uk."]", "", "", bin2hex((string)$v), "", "");
			}

			$constraint = "";
			foreach($RT["FIELDS"][$k]["CONSTRAINT"] as $vc){

				$constraint .= $vc[0];
			}

			$flag = $this->privileges_rc($k, $RT["PRIVILEGES"], $mod);

			$this->tg_open("table", "", "", "", "");
			$this->tg_open("tr", "", "", "", "");
			$this->tg_open("td", "", "", "", "");

			if($flag !== "disabled"){ $pac = true; }

			if(((string)$RT["FIELDS"][$k]["COLUMN_DEFAULT"] !== "") || ($RT["FIELDS"][$k]["IS_NULLABLE"] === "YES"))
			{
				$this->tgFr_input("checkbox", "list_rw[]", "", "dt_check", $uk, "", "checked");
			}
			else
			{
				$this->tgFr_input("checkbox", "", "", "dt_check", "", "", "disabled checked");
				$this->tgFr_input("hidden", "list_rw[]", "", "dt_in_key", $uk, "", "");
			}

			$this->tg_close("td");
			$this->tg_open("td", "", "", "", "");


			$this->tgFr_input("text", "", "", "dt_in_key", $constraint, "", "disabled");

			$this->tgFr_input("text", "", "", "dt_in_name", $this->html("$k"), "", "disabled");

			$not_null = "";

			if($RT["FIELDS"][$k]["IS_NULLABLE"] === "NO"){

				$not_null = " not null";
			}

			$this->tg_open("div", "", "", "display:inline-block;", "");

			if(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
				($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
				($RT["FIELDS"][$k]["FOREIGN"]))
			{
				$this->tg_open("div", "com".$count.$count_fl.$uk.$mod, "", "display: none;", "");

				$this->tg_open("div", "", "slcus", "", "");

				$this->tg_open("div", "com_sl".$count.$count_fl.$uk.$mod, "slcus_sl", "", "onclick=\"ms.el_stop_com();\"");

				foreach($RT["FIELDS"][$k]["COLUMN_VALUE"] as $vst)
				{
					$this->tg_open("div", "", "slcus_sl_k", "", "");

					if(in_array("FOREIGN KEY", $RT["FIELDS"][$k]["CONSTRAINT"])){

						$checked = ($v === $vst) ? "checked" : "";

						$this->tgFr_input("radio", "tv".$count.$count_fl.$uk, "", "", $this->html($vst), "", $checked);
					}
					if(($RT["FIELDS"][$k]["DATA_TYPE"] == "enum")){

						$checked = ($v === $vst) ? "checked" : "";

						$this->tgFr_input("radio", "tv".$count.$count_fl.$uk, "", "", $this->html($vst), "", $checked);
					}
					if($RT["FIELDS"][$k]["DATA_TYPE"] == "set"){

						$checked = (in_array($vst, explode(",",(string)$v))) ? "checked" : "";

						$this->tgFr_input("checkbox", "", "", "", $this->html($vst), "", $checked);
					}

					$this->tg("label", "", "", "", $this->html($vst), "");

					$this->tg_close("div");

					$this->tg("div", "", "separator3", "", "", "");
				}

				$this->tg_close("div");

				$this->tgFr_input("button", "", "", "dt_btn", _NOTE_CONFIRM_YES,
					"onclick=\"ms.set_vl_sl('text".$count.$count_fl.$uk.$mod."', 'com_sl".
					$count.$count_fl.$uk.$mod."', 'com".$count.$count_fl.$uk.$mod."');\"");

				$this->tgFr_input("button", "", "", "dt_btn", _NOTE_CONFIRM_NO,
					"onclick=\"ms.view_va('com".$count.$count_fl.$uk.$mod."', 'none');\"");

				$this->tg_close("div");
				$this->tg_close("div");

				if($flag === "disabled"){

					$type_onclick = "";
				}
				else{

					$type_onclick = "onclick=\"ms.el_open_com('com".$count.$count_fl.$uk.$mod."');\"";
				}

				$this->tgFr_input("text", "", "", "dt_in_type_sl", $RT["FIELDS"][$k]["DATA_TYPE"].$not_null." ...",
					$type_onclick, "readonly");
			}
			else{

				$this->tgFr_input("text", "", "", "dt_in_type", $this->html($RT["FIELDS"][$k]["COLUMN_TYPE"]).$not_null,
					"", "disabled");
			}

			$this->tg_close("div");

			$function_class = "dt_in_function";

			$function_onclick ="onclick=\"ms.creat_rcdl(this.id, 'text".$count.$count_fl.$uk.$mod."',
				'function_dv', '".$this->html(addslashes("$k"))."');\"";

			$function_flag = "autocomplete='off'";

			$function_placeholder = "placeholder='function...'";

			if(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["blob"]) ||
				in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["geo"]) ||
				($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
				($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
				($RT["FIELDS"][$k]["DATA_TYPE"] === "bit") ||
				($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
				($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP") ||
				in_array("FOREIGN KEY", $RT["FIELDS"][$k]["CONSTRAINT"]))
			{
				$this->tgFr_input("text", "function[".$uk."]", "function_".$count.$count_fl.$uk.$mod,
					"dt_in_function_disabled", "", "", "disabled");
			}
			else
			{
				if($flag === "disabled"){

					$function_class = "dt_in_function_disabled";
					$function_onclick = "";
					$function_flag = "disabled";
					$function_placeholder = "placeholder=''";
				}

				$this->tgFr_input("text", "function[".$uk."]", "function_".$count.$count_fl.$uk.$mod,
					$function_class, "", $function_onclick, $function_flag." ".$function_placeholder);
			}

			$data_flag = $flag;

			$type_placeholder = $this->html(trim((string)$RT["FIELDS"][$k]["COLUMN_DEFAULT"]));

			if(gettype ( $v ) === "NULL"){

				$type_placeholder = "placeholder='NULL'";
			}

			if(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["blob"]))
			{
				$v = hex2bin((string)$v);

				if($flag === "disabled")
				{
					$this->tgFr_input("button", "", "", "dt_btn_text", "&nbsp;", "");
				}
				else
				{
					$this->tg_open("div", "", "", "display:inline-block;", "");

						$this->tg_open("div", "", "file_dl", "", "");

						$this->tg("a", "", "file_dl_a", "", "text",
							"href=\"javascript:ms.reset_rcul_file('".$count.$count_fl.$uk.$mod."');\"");

						$this->tg_close("div");

						$this->tg_open("div", "", "file_dl", "", "");

							$this->tg("a", "", "file_dl_a", "", _NOTE_UL,
								"href=\"javascript:ms.reset_rcul_file_click('".$count.$count_fl.$uk.$mod."');\"");

						$this->tg_close("div");

						if(($mod === "edit") && (trim((string)$v) !== "")){

							$this->tg_open("div", "", "file_dl", "", "");

							$this->tg("a", "", "file_dl_a", "", _NOTE_DL." (".$this->get_size($v).")",
								"download='".$k.".bin' href='data:multipart/form-data;base64,".base64_encode((string)$v)."'");

							$this->tg_close("div");
						}

					$this->tg_close("div");

					$this->tg_open("div", "file_name_sl".$count.$count_fl.$uk.$mod, "file_ul", "display: none;", "");
					$this->tg_open("label", "", "", "", "");

					$this->tgFr_input("file", "", "", "", "",
						"onchange=\"ms.get_rcul_file(this.files,'".$count.$count_fl.$uk.$mod."','blob');\"", "multiple");

					$this->tg("span", "file_name".$count.$count_fl.$uk.$mod, "", "", _NOTE_FL." (max ".ini_get("post_max_size").") ", "");

					$this->tg_close("label");
					$this->tg_close("div");

					$this->textarea("file[".$uk."]", "file".$count.$count_fl.$uk.$mod, "dt_in_text",
						"", "", "disabled hidden", "");

					$this->textarea("text[".$uk."]", "text".$count.$count_fl.$uk.$mod, "dt_in_text",
						"", "", "disabled hidden", $type_placeholder);

					$this->textarea("field[".$uk."]", "field".$count.$count_fl.$uk.$mod, "", "", "", "hidden", "");
				}

				if(($mod === "edit") && (trim((string)$v) !== ""))
				{
					$this->textarea("", "prev".$count.$count_fl.$uk.$mod, "dt_in_text",
						$this->strTV($this->html($v)), "", "disabled", $type_placeholder);
				}
				else
				{
					$this->textarea("", "prev".$count.$count_fl.$uk.$mod, "dt_in_text",
						$this->strTV($this->html($v)), "", "disabled hidden", $type_placeholder);
				}
			}
			elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["text"]) || ($RT["FIELDS"][$k]["DATA_TYPE"] === "json"))
			{
				if($flag === "disabled")
				{
					$this->tgFr_input("button", "", "", "dt_btn_text", "&nbsp;", "");
				}
				else
				{
					$this->tg_open("div", "", "", "display:inline-block;", "");

						$this->tg_open("div", "", "file_dl", "", "");

						$this->tg("a", "", "file_dl_a", "", "text",
							"href=\"javascript:ms.reset_rcul_file('".$count.$count_fl.$uk.$mod."');\"");

						$this->tg_close("div");

						$this->tg_open("div", "", "file_dl", "", "");

							$this->tg("a", "", "file_dl_a", "", _NOTE_UL,
								"href=\"javascript:ms.reset_rcul_file_click('".$count.$count_fl.$uk.$mod."');\"");

						$this->tg_close("div");

						if(($mod === "edit") && (trim((string)$v) !== "")){

							$this->tg_open("div", "", "file_dl", "", "");

							$this->tg("a", "", "file_dl_a", "", _NOTE_DL." (".$this->get_size($v).")",
								"download='".$k.".txt' href='data:multipart/form-data;base64,".base64_encode((string)$v)."'");

							$this->tg_close("div");
						}

					$this->tg_close("div");

					$this->tg_open("div", "file_name_sl".$count.$count_fl.$uk.$mod, "file_ul", "display: none", "");
					$this->tg_open("label", "", "", "", "");

					$this->tgFr_input("file", "", "", "", "",
						"onchange=\"ms.get_rcul_file(this.files,'".$count.$count_fl.$uk.$mod."','text');\"", "multiple");

					$this->tg("span", "file_name".$count.$count_fl.$uk.$mod, "", "", _NOTE_FL." (max ".ini_get("post_max_size").") ", "");

					$this->tg_close("label");
					$this->tg_close("div");

					$this->textarea("file[".$uk."]", "file".$count.$count_fl.$uk.$mod, "dt_in_text",
						"", "", "disabled hidden", "");

					$this->textarea("field[".$uk."]", "field".$count.$count_fl.$uk.$mod, "", "", "", "hidden", "");
				}

				if(($mod === "edit") && (trim((string)$v) !== ""))
				{
					$this->textarea("text[".$uk."]", "text".$count.$count_fl.$uk.$mod, "dt_in_text", $this->html($v),
						"onchange=\"ms.check_change(this, 'dt_in_text_change');\"", $data_flag, $type_placeholder);
				}
				else
				{
					$this->textarea("text[".$uk."]", "text".$count.$count_fl.$uk.$mod, "dt_in_text", $this->html($v),
						"onchange=\"ms.check_change(this, 'dt_in_text_change');\"", $data_flag." hidden", $type_placeholder);
				}

				$this->textarea("", "prev".$count.$count_fl.$uk.$mod, "", "", "", "hidden", "");
			}
			elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["binary"]))
			{
				$class = "dt_in_value";

				if($flag === "disabled"){

					$class = "dt_in_value_disabled";
					$data_flag = "disabled";
				}

				$this->tgFr_input("hidden", "file[".$uk."]", "file".$count.$count_fl.$uk.$mod, "", $v, "", "");

				$this->tgFr_input("text", "field[".$uk."]", "text".$count.$count_fl.$uk.$mod, $class, $v, "",
					$data_flag." ".$type_placeholder);
			}
			elseif(in_array($RT["FIELDS"][$k]["DATA_TYPE"], $this->GT["geo"]))
			{
				$class = "dt_in_value";

				if($flag === "disabled"){

					$class = "dt_in_value_disabled";
					$data_flag = "disabled";
				}

				$this->tgFr_input("text", "field[".$uk."]", "", $class, $this->html($v), "", $data_flag);
			}
			else
			{
				$class = "dt_in_value";

				if(($RT["FIELDS"][$k]["EXTRA"] === "auto_increment") ||
					($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL") ||
					($RT["FIELDS"][$k]["EXTRA"] === "VIRTUAL GENERATED") ||
					($RT["FIELDS"][$k]["EXTRA"] === "STORED GENERATED") ||
					($RT["FIELDS"][$k]["COLUMN_DEFAULT"] === "CURRENT_TIMESTAMP")){

					$class = "dt_in_value_disabled";
					$data_flag = "disabled";
				}
				elseif(($RT["FIELDS"][$k]["DATA_TYPE"] === "enum") ||
					($RT["FIELDS"][$k]["DATA_TYPE"] === "set") ||
					in_array("FOREIGN KEY", $RT["FIELDS"][$k]["CONSTRAINT"])){

					$data_flag = "readonly";
				}

				if($flag === "disabled"){

					$class = "dt_in_value_disabled";
					$data_flag = "disabled";
				}

				$this->tgFr_input("text", "field[".$uk."]", "text".$count.$count_fl.$uk.$mod, $class, $this->html($v),
					"onchange=\"ms.check_change(this, 'dt_in_value_change');\"", $data_flag." ".$type_placeholder);
			}

			$this->tg_close("td");
			$this->tg_close("tr");
			$this->tg_close("table");

			$this->tg_close("div");
		}

		$this->dl_confirm("id_cn_rc".$count.$mod);

		$this->tg("div", "", "separator3", "", "", "");

		if($pac && ($count_fl > 0) && ($count_fl_display > 0))
		{
			if($mod === "edit")
			{
				if($RT["PRI"] || (strtoupper($RT["TABLE_TYPE"]) === "VIEW"))
				{
					$this->tgFr_input("button", "", "", "dt_btn", _ACTION_DELETE,
						"onclick=\"ms.AT('_DELETE_RC', 'id_cn_rc".$count.$mod."', this, this.form,
						'"._NOTE_ROW." / "._ACTION_DELETE."', ['_DELETE_RC'], []); \"");

					$this->tgFr_input("button", "", "", "dt_btn", _ACTION_UPDATE,
						"onclick=\"ms.RF('_UPDATE_RC', '', '', this.form, 0);\"");
				}

				if($this->prci($RT["PRIVILEGES"])){

					$this->tgFr_input("button", "", "", "dt_btn", _ACTION_INSERT, "onclick=\"ms.RF('_COPY_RC', '', '',this.form, 0);\"");
				}
			}
			elseif($mod === "insert")
			{
				$this->tgFr_input("button", "", "", "dt_btn", _ACTION_INSERT,
					"onclick=\"ms.RF('_INSERT_RC', '', '',this.form, 0);\"");
			}
		}

		$this->tgFr_close();

		$this->tg("div", "", "separator11", "", "", "");
	}


	private function filter_set($nv)
	{
		foreach($nv["fl_and_rc"] as $v){

			$this->tgFr_input("hidden", "fl_and_rc[]", "", "", $v, "", "");
		}
		foreach($nv["fl_field_rc"] as $v){

			$this->tgFr_input("hidden", "fl_field_rc[]", "", "", $this->html($v), "", "");
		}
		foreach($nv["fl_operator_rc"] as $v){

			$this->tgFr_input("hidden", "fl_operator_rc[]", "", "", $v, "", "");
		}
		foreach($nv["fl_value_rc"] as $v){

			$this->tgFr_input("hidden", "fl_value_rc[]", "", "", $this->html($v), "", "");
		}

		$this->tgFr_input("hidden", "fl_count_rc", "", "", $nv["fl_count_rc"], "", "");
	}


	private function filter($RT, $nv)
	{
		$this->tg_open("table", "", "fl_wrap_main_table", "", "");

			$this->tg_open("tr", "", "", "", "");

			$this->tg_open("td", "", "fl_wrap_main_tb", "", "");

				$this->tgFr_input("button", "", "", "fl_btn", "select ", "onclick=\"ms.el_open_com('com_field_rc');\"");

				$this->tg_open("div", "com_field_rc", "slcus", "display: none;",
					"onclick=\"ms.el_stop_com();\"");

				$this->tg_open("div", "com_sl_field_rc", "slcus_sl", "", "");

				$this->tg_open("div", "", "slcus_sl_k", "", "");

					$this->tgFr_input("checkbox", "totalH", "", "", "",
						"onclick=\"ms.set_list_ch_fr(this.form,'field_rc[]',this.checked);\"", "checked");

				$this->tg_close("div");

				$this->tg("div", "", "separator3", "", "", "");

				foreach($RT["FIELD_SE_VIEW"] as $vst)
				{
						$this->tg_open("div", "", "slcus_sl_k", "", "");

						if(count($nv["field_rc"]) === 0){

							$checked = "checked";
						}
						else{

							$checked = (in_array(bin2hex((string)$vst), $nv["field_rc"])) ? "checked" : "";
						}

						$this->tgFr_input("checkbox", "field_rc[]", "", "", bin2hex((string)$vst), "", $checked);

						$v = ($RT["FIELDS"][$vst]["DATA_TYPE"] !== "") ? "(".$RT["FIELDS"][$vst]["DATA_TYPE"].") " : "";

						$this->tg("label", "", "", "", "&nbsp;".$this->html($v.$vst), "");

						$this->tg_close("div");

						$this->tg("div", "", "separator3", "", "", "");
				}

				$this->tg_open("div", "", "slcus_sl_k", "", "");

					$this->tgFr_input("checkbox", "totalF", "", "", "",
						"onclick=\"ms.set_list_ch_fr(this.form,'field_rc[]',this.checked);\"", "checked");

				$this->tg_close("div");

				$this->tg("div", "", "separator3", "", "", "");

				$this->tg_close("div");

				$this->tgFr_input("button", "", "", "dt_btn", _NOTE_CONFIRM_YES, "onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

				$this->tgFr_input("button", "", "", "dt_btn", _NOTE_CONFIRM_NO, "onclick=\"ms.view_va('com_field_rc', 'none');\"");

			$this->tg_close("div");

			$this->tg_close("td");

			$this->tg_open("td", "", "fl_wrap_main_tb", "", "");

				$this->tgFr_input("button", "", "", "fl_btn", "where", "onclick=\"ms.view_vb('flc_rc');\"");

			$this->tg_close("td");

			$this->tg_open("td", "", "fl_wrap_main_tb", "", "");

				$this->select($RT["ON_PAGE"], $nv["page_rc"], "", "page_rc", "", "fl_slc", "",
					"onchange=\"ms.set_vl('from_rc', '0'); ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return "limit ".$v;});

			$this->tg_close("td");

			$this->tg_open("td", "", "fl_wrap_main_tb", "", "");

				$this->select($RT["FROM"], $nv["from_rc"], "", "from_rc", "from_rc", "fl_slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return "from ".$v;});

			$this->tg_close("td");

			$this->tg_open("td", "", "", "", "");

				$this->select($RT["FIELD_SE_ORDER"], $nv["order_rc"], "", "order_rc", "", "fl_slc2", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $k;},
					function($k, $v){return $k;},
					function($k, $v){return "order by ".$this->html($v);});

				if($nv["order_desc_rc"] === "DESC"){

					$this->tgFr_input("checkbox", "order_desc_rc", "", "dt_check", "",
						"onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"", "checked");
				}
				else{

					$this->tgFr_input("checkbox", "order_desc_rc", "", "dt_check", "desc",
						"onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"", "");
				}

				$this->tg("label", "", "", "", "DESC", "");

			$this->tg_close("td");

			$this->tg_close("tr");

		$this->tg_close("table");

		$fl_display = "display: none;";

		if((isset($nv["fl_field_rc"][0]) && ($RT["FIELD_SE_FILTER"][0] !== $nv["fl_field_rc"][0])) ||
			(isset($nv["fl_field_rc"][1]) && ($RT["FIELD_SE_FILTER"][1] !== $nv["fl_field_rc"][1]))	||
			(implode("", $nv["fl_value_rc"]) !== "") || ($nv["fl_count_rc"] > 2)){

			$fl_display = "";
		}

		$this->tg_open("div", "flc_rc", "", $fl_display, "");

		$this->tg("div", "", "separator3", "", "", "");

		$this->tg_open("table", "", "fl_wrap_table", "", "");

		for($k=0;$k<$nv["fl_count_rc"];$k++)
		{
			$add_fl_operator = "";
			$add_fl_value = "";
			$add_fl_operator_and = "";

			if(isset($nv["fl_field_rc"][$k]))
			{
				$add_fl_field_and = $nv["fl_field_rc"][$k];
				$add_fl_operator = $nv["fl_operator_rc"][$k];
				$add_fl_value = $this->html($nv["fl_value_rc"][$k]);
				$add_fl_operator_and = $nv["fl_and_rc"][$k];
			}
			else{

				if(isset($RT["FIELD_SE_FILTER"][$k])){

					$add_fl_field_and = $RT["FIELD_SE_FILTER"][$k];
				}
				else{

					$add_fl_field_and = $RT["FIELD_SE_FILTER"][0];
				}
			}

			$this->tg_open("tr", "", "", "", "");

			$this->tg_open("td", "", "fl_wrap_td", "", "");

			if($k === 0){

				$this->tgFr_input("hidden", "fl_and_rc[]", "", "fl_value", "", "", "");
			}
			else{

				$this->select(["AND", "OR"], $add_fl_operator_and, "",
					"fl_and_rc[]", "", "fl_slc", "", "",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});
			}

			$this->tg_close("td");

			$this->tg_open("td", "", "fl_wrap_td", "", "");

				$this->select($RT["FIELD_SE_FILTER"], $add_fl_field_and, "",
					"fl_field_rc[]", "", "fl_slc", "",
					"onchange=\"ms.RF('VIEW', '', '', this.form, 0);\"",
					function($k, $v){return $v;},
					function($k, $v){return $this->html($v);},
					function($k, $v){return $this->html($v);});

			$this->tg_close("td");
			$this->tg_open("td", "", "fl_wrap_td", "", "");

				$this->select($RT["FILTER_EX"][$add_fl_field_and], $add_fl_operator, "",
					"fl_operator_rc[]", "", "fl_slc", "", "",
					function($k, $v){return $v;},
					function($k, $v){return $v;},
					function($k, $v){return $v;});

			$this->tg_close("td");
			$this->tg_open("td", "", "", "", "");

				$this->tgFr_input("text", "fl_value_rc[]", "", "fl_value", $add_fl_value, "", _NOTE_FILTER_VALUE);

			$this->tg_close("td");

			$this->tg_close("tr");
		}

		$this->tg_close("table");

		$this->tgFr_input("hidden", "fl_count_rc", "", "", $nv["fl_count_rc"], "", "");

		$this->tgFr_input("button", "", "", "fl_btn", _NOTE_FILTER_ADD, "onclick=\"ms.RF('_ADD_FILTER_rc', '', '', this.form, 0);\"");

		$this->tg("div", "", "separator3", "", "", "");

		$this->tgFr_input("button", "", "", "dt_btn", _NOTE_FILTER_RESET, "onclick=\"ms.RF('_RESET_FILTER_rc', '', '', this.form, 0);\"");

		$this->tgFr_input("button", "", "", "dt_btn", _NOTE_CONFIRM_YES, "onclick=\"ms.RF('VIEW', '', '', this.form, 0);\"");

		$this->tg("div", "", "separator11", "", "", "");

		$this->tg_close("div");

		if(((int)$RT["COUNT"] === 0))
		{
			$this->tg("div", "", "separator3", "", "", "");
			$this->tgFr_input("button", "", "", "dt_slc",  _NOTE_TOTAL." [ ".$RT["COUNT"]." ] ", "");
		}
		else
		{
			$this->tg("div", "", "separator3", "", "", "");

			$this->dl_confirm("id_cn_nv_TARGET");

			$this->select($RT["ACF"],
				false, "", "list_FT_sl", "", "dt_slc", _NOTE_TOTAL." [ ".$RT["COUNT"]." ] ",
					"onchange=\"ms.AL(this.value, 'id_cn_nv_TARGET', this, this.form, '',
					'"._NOTE_ROWS." / "._NOTE_TOTAL." [ ".$RT["COUNT"]." ] / ',
					".$this->ac_con.",".$this->ac_ex.", '' ); \"",
				function($k, $v){return $v;},
				function($k, $v){return $k;},
				function($k, $v){return $v;});
		}
	}

	private function get_size($v)
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
					$size = round($size, 2)." gb";
				}
				else
				{
					$size = round($size, 2)." mb";
				}
			}
			else
			{
				$size = round($size, 2)." kb";
			}
		}
		else
		{
			$size = round($size, 2)." b";
		}

		return $size;
	}

	protected function strTV($str)
	{
		$hex = "";

		$l = strlen((string)$str);

		for ($i=0; $i<$l; $i++){

			if((ord($str[$i]) > 31) && (ord($str[$i]) < 127)){

				$hex = $hex.$str[$i];
			}
			else{

				$hex = $hex.'.';
			}
		}

		return $hex;
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
			)
			{
				$RT = "";
			}
			else{$RT = "disabled";}
		}

		return $RT;
	}

	private function prci($PR)
	{
		if(!isset($PR["TABLE_PRIVILEGES"]) && !isset($PR["COLUMN_PRIVILEGES"]))
		{
			return true;
		}
		elseif((in_array("INSERT", $PR["TABLE_PRIVILEGES"])) ||
			(isset($PR["COLUMN_PRIVILEGES"]["UPDATE"]) && isset($PR["COLUMN_PRIVILEGES"]["INSERT"]) &&
			($PR["COLUMN_PRIVILEGES"]["UPDATE"] === $PR["COLUMN_PRIVILEGES"]["INSERT"])) ||
			(isset($PR["COLUMN_PRIVILEGES"]["UPDATE"]) && !isset($PR["COLUMN_PRIVILEGES"]["INSERT"])))
		{
			return true;
		}

		return false;
	}

	private function form_set($_US, $_SV, $_SH, $_TB, $page_rc, $from_rc, $order_rc, $field_rc, $view_rc="")
	{
		$this->tgFr_input("hidden", "action", "", "", "", "", "");

		$this->tgFr_input("hidden", "sh", "", "", $_SH, "", "");
		$this->tgFr_input("hidden", "tb", "", "", $_TB, "", "");

		if($page_rc != ""){$this->tgFr_input("hidden", "page_rc", "", "", $page_rc, "", "");}
		if($from_rc != ""){$this->tgFr_input("hidden", "from_rc", "", "", $from_rc, "", "");}
		if($order_rc != ""){$this->tgFr_input("hidden", "order_rc", "", "", $order_rc, "", "");}

		if(count($field_rc) !== 0)
		{
			foreach($field_rc as $value){

				$this->tgFr_input("hidden", "field_rc[]", "", "", $this->html($value), "", "");
			}
		}

		if($view_rc != ""){$this->tgFr_input("hidden", "view_rc", "", "", $view_rc, "", "");}

		$this->tgFr_input("hidden", "session", "", "", "", "", "");
		$this->tgFr_input("hidden", "request", "", "", "", "", "");

		$this->tgFr_input("hidden", "usr", "", "", $_US, "", "");
	}

	private function tg($name, $id, $class, $style, $value, $event)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$style = ($style !== "") ? "style='".$style."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<".$name." ".$id." ".$class." ".$style." ".$event.">".$value."</".$name.">";
	}

	private function tg_open($name, $id, $class, $style, $event)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$style = ($style !== "") ? "style='".$style."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<".$name." ".$id." ".$class." ".$style." ".$event.">";
	}

	private function tg_close($name)
	{
		print "</".$name.">";
	}

	private function tgFr_open($class="", $id="")
	{
		print "<form id='".$id."' name='' method='post' action='' class='".$class."' enctype='' onSubmit='return false;'>";
	}

	private function tgFr_close()
	{
		print "</form>";
	}

	private function tgFr_input($type, $name, $id, $class, $value, $event, $atr="")
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<input type='".$type."' ".$id." name='".$name."' ".$class." value='".$value."' ".$event." ".$atr.">";
	}

	private function textarea($name, $id, $class, $value, $event, $flag, $placeholder)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<textarea ".$id." name='".$name."' ".$class." ".$event." ".$flag." ".$placeholder.">".
			$value."</textarea>";
	}

	private function select($foreach, $selected, $disabled, $name, $id, $class, $title, $event, $ch, $fk, $fv)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<select ".$id." name='".$name."' ".$class." size='1' ".$event.">";

		if($title !== ""){print "<OPTION SELECTED value='' disabled> ".$title." </OPTION>";}

		foreach($foreach as $k=>$v){

			if(($disabled !== "") && preg_match("/".$disabled."/", (string)call_user_func($ch, $k, $v))){

				print "<OPTION value='".call_user_func($fk, $k, $v)."' disabled> ".
					call_user_func($fv, $k, $v)." </OPTION>";
			}
			elseif($selected === (string)call_user_func($ch, $k, $v)){

				print "<OPTION SELECTED value='".call_user_func($fk, $k, $v)."' > ".
					call_user_func($fv, $k, $v)." </OPTION>";
			}
			else{

				print "<OPTION value='".call_user_func($fk, $k, $v)."' > ".
					call_user_func($fv, $k, $v)." </OPTION>";
			}
		}

		print "</select>";
	}

	private function html($input, $EL="", $DL="")
	{
		if($EL !==""){

			return preg_replace("/".$EL."/", $DL, htmlspecialchars((string)$input, ENT_QUOTES | ENT_SUBSTITUTE));
		}
		else{

			return htmlspecialchars((string)$input, ENT_QUOTES | ENT_SUBSTITUTE);
		}
	}

}