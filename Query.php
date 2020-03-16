<?php

defined("_EXEC") or die();


Class Query
{

	protected $action = "";
	protected $display = "";
	protected $_DB = "";
	protected $_TB = "";
	protected $list_db = [];
	protected $list_tb = [];
	protected $nv = [];
	protected $key = [];
	protected $field = [];
	protected $name_new = "";
	protected $cl_del = "";
	protected $cl_def = "";
	protected $cl_in = "";
	protected $cl_change = "";
	protected $tb_def = "";
	protected $script = "";
	protected $script_id = "";

	public function __construct(){}

	protected function request()
	{
		if(isset($_POST["action"])){ $this->action = $_POST["action"]; }

		if(isset($_POST["display"])){ $this->display = $_POST["display"]; }
	
		if(isset($_POST["bd"])){ $this->_DB = $this->set_value($_POST["bd"]); }

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
			($_POST["fl_operator_rc"]) !== "..." ) ? $_POST["fl_operator_rc"] : "";


		if(isset($_POST["key"])){ $this->key = $_POST["key"]; }

		if(isset($_POST["field"])){ $this->field = $this->set_value_list($_POST["field"]); }

		if(isset($_POST["name_new"])){ $this->name_new = $this->set_value($_POST["name_new"]); }

		if(isset($_POST["cl_del"])){ $this->cl_del = $this->set_value($_POST["cl_del"]); }

		if(isset($_POST["cl_def"])){ $this->cl_def = $this->set_value($_POST["cl_def"]); }

		if(isset($_POST["cl_in"])){ $this->cl_in = $this->set_value($_POST["cl_in"]); }

		if(isset($_POST["cl_change"])){ $this->cl_change = $this->set_value($_POST["cl_change"]); }

		if(isset($_POST["tb_def"])){ $this->tb_def = $this->set_value($_POST["tb_def"]); }

		if(isset($_POST["script"])){ $this->script = $this->set_value($_POST["script"]); }

		if(isset($_POST["script_id"])){ $this->script_id = $this->set_value($_POST["script_id"]); }
	}


}