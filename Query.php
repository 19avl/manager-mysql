<?php

defined("_EXEC") or die();


Class Query
{
	protected $_DB = "";
	protected $_TB = "";

	protected $action = "";
	protected $display = "";

	protected $list_db = [];
	protected $list_tb = [];
	protected $nv = [];
	protected $key = [];
	protected $field = [];
	protected $function = [];
	protected $file = [];

	protected $cl_in = "";
	protected $cl_sl = [];
	protected $cl_dl = "";
	protected $cl_df = "";
	protected $cl_tr = "";

	protected $script = "";
	protected $script_id = "";

	public function __construct(){}

	protected function request()
	{
		if(isset($_POST["action"])){ $this->action = $_POST["action"]; }

		if(isset($_POST["display"])){ $this->display = $_POST["display"]; }

		if(isset($_POST["db"])){ $this->_DB = $this->set_value($_POST["db"]); }

		if(isset($_POST["tb"])){ $this->_TB = $this->set_value($_POST["tb"]); }

		if(isset($_POST["list_db"])){ $this->list_db = $_POST["list_db"]; }

		if(isset($_POST["list_tb"])){ $this->list_tb = $_POST["list_tb"]; }


		$this->nv["page_db"] = (isset($_POST["page_db"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["page_db"])) ? $_POST["page_db"] : "0";

		$this->nv["from_db"] = (isset($_POST["from_db"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["from_db"])) ? $_POST["from_db"] : "0";

		$this->nv["order_db"] = (isset($_POST["order_db"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["order_db"])) ? $_POST["order_db"] : "0";

		$this->nv["field_db"] = (isset($_POST["field_db"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["field_db"])) ? $_POST["field_db"] : "0";

		$this->nv["page_rc"] = (isset($_POST["page_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["page_rc"])) ? $_POST["page_rc"] : "0";

		$this->nv["from_rc"] = (isset($_POST["from_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["from_rc"])) ? $_POST["from_rc"] : "0";

		$this->nv["order_rc"] = (isset($_POST["order_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["order_rc"])) ? $_POST["order_rc"] : "0";

		$this->nv["field_rc"] = (isset($_POST["field_rc"])) ? $this->set_value_list($_POST["field_rc"]) : [];

		$this->nv["page_tb"] = (isset($_POST["page_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["page_tb"])) ? $_POST["page_tb"] : "0";

		$this->nv["from_tb"] = (isset($_POST["from_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["from_tb"])) ? $_POST["from_tb"] : "0";

		$this->nv["order_tb"] = (isset($_POST["order_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["order_tb"])) ? $_POST["order_tb"] : "0";

		$this->nv["field_tb"] = (isset($_POST["field_tb"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["field_tb"])) ? $_POST["field_tb"] : "0";

		$this->nv["fl_field_db"] = isset($_POST["fl_field_db"]) ? $_POST["fl_field_db"] : "";

		$this->nv["fl_value_db"] = isset($_POST["fl_value_db"]) ? $this->set_value($_POST["fl_value_db"]) : "";

		$this->nv["fl_operator_db"] = (isset($_POST["fl_operator_db"]) &&
			($_POST["fl_operator_db"]) !== "..." ) ? $_POST["fl_operator_db"] : "";

		$this->nv["fl_field_tb"] = isset($_POST["fl_field_tb"]) ? $_POST["fl_field_tb"] : "";

		$this->nv["fl_value_tb"] = isset($_POST["fl_value_tb"]) ? $this->set_value($_POST["fl_value_tb"]) : "";

		$this->nv["fl_operator_tb"] = (isset($_POST["fl_operator_tb"]) &&
			($_POST["fl_operator_tb"]) !== "..." ) ? $_POST["fl_operator_tb"] : "";

		$this->nv["fl_field_rc"] = isset($_POST["fl_field_rc"]) ? $_POST["fl_field_rc"] : "";

		$this->nv["fl_value_rc"] = isset($_POST["fl_value_rc"]) ? $this->set_value($_POST["fl_value_rc"]) : "";

		$this->nv["fl_operator_rc"] = (isset($_POST["fl_operator_rc"]) &&
			($_POST["fl_operator_rc"] !== "...")) ? $_POST["fl_operator_rc"] : "";

		$this->nv["view_rc"] = (isset($_POST["view_rc"])) ? $_POST["view_rc"] : "";

		if(isset($_POST["key"])){ $this->key = $_POST["key"]; }

		if(isset($_POST["field"])){ $this->field = $this->set_value_list($_POST["field"]); }

		if(isset($_POST["function"])){

			$this->function = $this->set_value_list($_POST["function"]);
		}

		if(isset($_POST["file"])){

			$this->file = $this->set_value_list($_POST["file"]);
		}

		if(isset($_POST["cl_sl"])){ $this->cl_sl = $this->set_value_list($_POST["cl_sl"]); }

		if(isset($_POST["cl_dl"])){ $this->cl_dl = $this->set_value($_POST["cl_dl"]); }

		if(isset($_POST["cl_df"])){ $this->cl_df = $this->set_value($_POST["cl_df"]); }

		if(isset($_POST["cl_in"])){ $this->cl_in = $this->set_value($_POST["cl_in"]); }

		if(isset($_POST["cl_tr"])){ $this->cl_tr = $this->set_value($_POST["cl_tr"]); }

		if(isset($_POST["script"])){ $this->script = $this->set_value($_POST["script"]); }

		if(isset($_POST["script_id"])){ $this->script_id = $this->set_value($_POST["script_id"]); }
	}


}