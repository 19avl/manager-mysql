<?php

defined("_EXEC") or die();


Class Query
{
	protected $_SH = "";
	protected $_TB = "";

	protected $action = "";
	protected $display = "";

	protected $list_sh = [];
	protected $list_tb = [];
	protected $nv = [];
	protected $key = [];
	protected $field = [];
	protected $function = [];
	protected $file = [];
	protected $blob_ch = [];

	protected $cl_in = "";
	protected $cl_df = "";
	protected $cl_tr = "";

	protected $script = "";
	protected $script_id = "";
	protected $script_id_add = "";

	public function __construct(){}

	protected function query()
	{
		if(isset($_POST["action"])){ $this->action = $this->set_value($_POST["action"]); }

		if(isset($_POST["display"])){ $this->display = $this->set_value($_POST["display"]); }

		if(isset($_POST["sh"])){ $this->_SH = $this->set_value($_POST["sh"]); }

		if(isset($_POST["tb"])){ $this->_TB = $this->set_value($_POST["tb"]); }

		if(isset($_POST["list_sh"])){ $this->list_sh = $this->set_value_list($_POST["list_sh"]); }

		if(isset($_POST["list_tb"])){ $this->list_tb = $this->set_value_list($_POST["list_tb"]); }


		$this->nv["page_sh"] = (isset($_POST["page_sh"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["page_sh"]))) ?
				$this->set_value($_POST["page_sh"]) : "0";

		$this->nv["from_sh"] = (isset($_POST["from_sh"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["from_sh"]))) ?
				$this->set_value($_POST["from_sh"]) : "0";

		$this->nv["order_sh"] = (isset($_POST["order_sh"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["order_sh"]))) ?
				$this->set_value($_POST["order_sh"]) : "0";

		$this->nv["order_desc_sh"] = (isset($_POST["order_desc_sh"]) &&
			($_POST["order_desc_sh"] === "desc")) ? "DESC" : "";

		$this->nv["field_sh"] = (isset($_POST["field_sh"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["field_sh"]))) ?
				$this->set_value($_POST["field_sh"]) : "0";


		$this->nv["page_tb"] = (isset($_POST["page_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["page_tb"]))) ?
				$this->set_value($_POST["page_tb"]) : "0";

		$this->nv["from_tb"] = (isset($_POST["from_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["from_tb"]))) ?
				$this->set_value($_POST["from_tb"]) : "0";

		$this->nv["order_tb"] = (isset($_POST["order_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["order_tb"]))) ?
				$this->set_value($_POST["order_tb"]) : "0";

		$this->nv["order_desc_tb"] = (isset($_POST["order_desc_tb"]) &&
			($_POST["order_desc_tb"] === "desc")) ? "DESC" : "";

		$this->nv["field_tb"] = (isset($_POST["field_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["field_tb"]))) ?
				$this->set_value($_POST["field_tb"]) : "0";


		$this->nv["page_rc"] = (isset($_POST["page_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["page_rc"]))) ?
				$this->set_value($_POST["page_rc"]) : "0";

		$this->nv["from_rc"] = (isset($_POST["from_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["from_rc"]))) ?
				$this->set_value($_POST["from_rc"]) : "0";

		$this->nv["order_rc"] = (isset($_POST["order_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $this->set_value($_POST["order_rc"]))) ?
				$this->set_value($_POST["order_rc"]) : "0";

		$this->nv["order_desc_rc"] = (isset($_POST["order_desc_rc"]) &&
			($_POST["order_desc_rc"] === "desc")) ? "DESC" : "";

		$this->nv["field_rc"] = (isset($_POST["field_rc"])) ? $this->set_value_list($_POST["field_rc"]) : [];



		$this->nv["fl_field_sh"] = [];
		if(isset($_POST["fl_field_sh"])){ $this->nv["fl_field_sh"] =
			$this->set_value_list($_POST["fl_field_sh"]); }

		$this->nv["fl_value_sh"] = [];
		if(isset($_POST["fl_value_sh"])){ $this->nv["fl_value_sh"] =
			$this->set_value_list($_POST["fl_value_sh"]); }

		$this->nv["fl_operator_sh"] = [];
		if(isset($_POST["fl_operator_sh"])){ $this->nv["fl_operator_sh"] =
			$this->set_value_list($_POST["fl_operator_sh"]); }

		$this->nv["fl_and_sh"] = [];
		if(isset($_POST["fl_and_sh"])){ $this->nv["fl_and_sh"] =
			$this->set_value_list($_POST["fl_and_sh"]); }


		$this->nv["fl_field_tb"] = [];
		if(isset($_POST["fl_field_tb"])){ $this->nv["fl_field_tb"] =
			$this->set_value_list($_POST["fl_field_tb"]); }

		$this->nv["fl_value_tb"] = [];
		if(isset($_POST["fl_value_tb"])){ $this->nv["fl_value_tb"] =
			$this->set_value_list($_POST["fl_value_tb"]); }

		$this->nv["fl_operator_tb"] = [];
		if(isset($_POST["fl_operator_tb"])){ $this->nv["fl_operator_tb"] =
			$this->set_value_list($_POST["fl_operator_tb"]); }

		$this->nv["fl_and_tb"] = [];
		if(isset($_POST["fl_and_tb"])){ $this->nv["fl_and_tb"] =
			$this->set_value_list($_POST["fl_and_tb"]); }


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
		if(isset($_POST["fl_and_rc"])){ $this->nv["fl_and_rc"] =
			$this->set_value_list($_POST["fl_and_rc"]); }



		$this->nv["view_rc"] = (isset($_POST["view_rc"])) ? $this->set_value($_POST["view_rc"]) : "";

		if(isset($_POST["key"])){ $this->key = $this->set_value($_POST["key"]); }

		if(isset($_POST["field"])){ $this->field = $this->set_value_list($_POST["field"]); }

		if(isset($_POST["function"])){

			$this->function = $this->set_value_list($_POST["function"]);
		}

		if(isset($_POST["file"])){

			$this->file = $this->set_value_list($_POST["file"]);
		}

		if(isset($_POST["blob_ch"])){

			$this->blob_ch = $this->set_value_list($_POST["blob_ch"]);
		}

		if(isset($_POST["cl_df"])){ $this->cl_df = $this->set_value($_POST["cl_df"]); }

		if(isset($_POST["cl_in"])){ $this->cl_in = $this->set_value($_POST["cl_in"]); }

		if(isset($_POST["cl_tr"])){ $this->cl_tr = $this->set_value($_POST["cl_tr"]); }

		if(isset($_POST["script_file"])){ $this->script_file = $this->set_value($_POST["script_file"]); }

		if(isset($_POST["script"])){ $this->script = $this->set_value($_POST["script"]); }

		if(isset($_POST["script_id"])){ $this->script_id = $this->set_value($_POST["script_id"]); }
		
		if(isset($_POST["script_id_add"])){ $this->script_id_add = $this->set_value($_POST["script_id_add"]); }		
	}


}