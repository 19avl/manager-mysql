<?php

defined("_EXEC") or die();

Class Request
{
	protected $action = "";
	protected $display = "";
	protected $list_rc = [];
	protected $list_rw = [];
	protected $nv = [];

	protected $key = [];
	protected $field = [];
	protected $function = [];
	protected $text = [];
	protected $file = [];

	protected $cl_in = "";
	protected $cl_df = "";

	protected $script = "";
	protected $script_id = "";
	protected $script_id_add = "";
	protected $script_id_php = "";

	public function __construct(){}

	protected function request($LIMIT)
	{
		if(isset($_POST["action"])){ $this->action = $this->set_value($_POST["action"]); }

		if(isset($_POST["display"])){ $this->display = $this->set_value($_POST["display"]); }

		if(isset($_POST["list_rc"])){ $this->list_rc = $this->set_value_list($_POST["list_rc"]); }

		if(isset($_POST["list_rw"])){ $this->list_rw = $this->set_value_list($_POST["list_rw"]); }

		$this->nv["_SV"] = 0;

		if(isset($_POST["usr"])){ $this->nv["_US"] = $this->set_value($_POST["usr"]); }
		else{$this->nv["_US"] = "";}

		if(isset($_POST["sh"])){ $this->nv["_SH"] = $this->set_value($_POST["sh"]); }
		else{$this->nv["_SH"] = "";}

		if(isset($_POST["tb"])){ $this->nv["_TB"] = $this->set_value($_POST["tb"]); }
		else{$this->nv["_TB"] = "";}

		$this->nv["page_rc"] = (isset($_POST["page_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["page_rc"]))) ?
				$this->set_value($_POST["page_rc"]) : $LIMIT[0];

		$this->nv["from_rc"] = (isset($_POST["from_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["from_rc"]))) ?
				$this->set_value($_POST["from_rc"]) : "0";

		$this->nv["order_rc"] = (isset($_POST["order_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["order_rc"]))) ?
				$this->set_value($_POST["order_rc"]) : "0";

		$this->nv["order_desc_rc"] = (isset($_POST["order_desc_rc"]) && ($_POST["order_desc_rc"] === "DESC")) ? "DESC" : "";

		$this->nv["field_rc"] = (isset($_POST["field_rc"])) ? $this->set_value_list($_POST["field_rc"]) : [];

		$this->nv["fl_field_rc"] = [];
		if(isset($_POST["fl_field_rc"])){ $this->nv["fl_field_rc"] =
			$this->set_value_list($_POST["fl_field_rc"]); }

		$this->nv["fl_value_rc"] = [];
		if(isset($_POST["fl_value_rc"])){ $this->nv["fl_value_rc"] =
			$this->set_value_list($_POST["fl_value_rc"]); }

		$this->nv["fl_operator_rc"] = [];
		if(isset($_POST["fl_operator_rc"])){ $this->nv["fl_operator_rc"] =
			$this->set_value_list($_POST["fl_operator_rc"]); }

		$this->nv["fl_and_rc"] = [];
		if(isset($_POST["fl_and_rc"])){ $this->nv["fl_and_rc"] = $this->set_value_list($_POST["fl_and_rc"]); }

		$this->nv["fl_count_rc"] = (isset($_POST["fl_count_rc"])) ? $this->set_value($_POST["fl_count_rc"]) : _WHERE_CN_DEF;		

		$this->nv["view_rc"] = (isset($_POST["view_rc"])) ? $this->set_value($_POST["view_rc"]) : "";

		if(isset($_POST["key"])){ $this->key = $this->set_value($_POST["key"]); }

		if(isset($_POST["field"])){ $this->field = $this->set_value_list($_POST["field"]); }

		if(isset($_POST["function"])){ $this->function = $this->set_value_list($_POST["function"]); }

		if(isset($_POST["text"])){ $this->text = $this->set_value_list($_POST["text"]); }

		if(isset($_POST["file"])){ $this->file = $this->set_value_list($_POST["file"]); }

		if(isset($_POST["cl_df"])){ $this->cl_df = $this->set_value($_POST["cl_df"]); }

		if(isset($_POST["cl_in"])){ $this->cl_in = $this->set_value($_POST["cl_in"]); }

		if(isset($_POST["script"])){ $this->script = $this->set_value($_POST["script"]); }

		if(isset($_POST["script_id"])){ $this->script_id = $this->set_value($_POST["script_id"]); }

		if(isset($_POST["script_id_add"])){ $this->script_id_add = $this->set_value($_POST["script_id_add"]); }

		if(isset($_POST["script_id_php"])){ $this->script_id_php = $this->set_value($_POST["script_id_php"]); }
	}

	protected function set_value($value)
	{
		return $value;
	}

	protected function set_value_list($list)
	{
		$RT = [];

		foreach($list as $key=>$value){

			$RT[$key] = $this->set_value($value);
		}

		return $RT;
	}

}