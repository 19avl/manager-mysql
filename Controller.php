<?php

defined("_EXEC") or die();


Class Controller
{
	var $LIST_SQL;
	var $DATA_DB;
	var $DATA;



	public function __construct($SERVER, $SQL, $LIMIT)
	{
		$this->manager = new Manager();
		$this->view = new View();

		$this->exceptions = [
			"geometry", "point", "linestring", "polygon",
			"multipoint", "multilinestring", "multipolygon","geomcollection","geometrycollection"
		];

		$this->manager->connectdb($SERVER);

		if($this->manager->connect){

			$this->view->message($this->manager->_LOG);
			return;
		}

		$this->request();

		$this->action();

		$this->DATA_DB = $this->manager->db($this->nv, $LIMIT);

		$this->LIST_SQL = $this->manager->mk($this->script_id, $SQL);

		if(($this->_DB !== "") && ($this->_TB !== ""))
		{
			$this->DATA = $this->manager->rc( $this->_DB, $this->_TB, $this->nv, $this->exceptions, $LIMIT );
		}
		elseif(($this->_DB !== "") && ($this->_TB === ""))
		{
			$this->DATA = $this->manager->tb( $this->_DB, $this->nv, $LIMIT);
		}

		$this->view->alt_message();

		$this->view->main($this->_DB, $this->_TB, $this->nv, $SQL);

		$this->view->db($this->DATA_DB, $this->_DB, $this->_TB, $this->nv, $this->display);

		$this->view->mk($this->_DB, $this->_TB, $this->LIST_SQL, $this->nv, $this->display);

		if($this->_DB === ""){

			$this->view->info($this->manager->info());
		}

		$this->view->stat($this->manager->dbc);

		$this->view->message($this->manager->_LOG);

		if(($this->_DB !== "") && ($this->_TB !== ""))
		{
			$this->view->rc( $this->_DB, $this->_TB, $this->DATA, $this->nv, $this->exceptions);
		}
		elseif(($this->_DB !== "") && ($this->_TB === ""))
		{
			$this->view->tb($this->_DB, $this->DATA, $this->action, $this->nv);
		}
	}


	private function request()
	{
		$this->action = isset($_POST["action"]) ? $_POST["action"] : "";

		$this->display = isset($_POST["display"]) ? $_POST["display"] : "";

		$this->list_db = isset($_POST["list_db"]) ? $_POST["list_db"] : [];

		$this->nv = [];

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

		$this->nv["field_rc"] = (isset($_POST["field_rc"]) &&
			preg_match("/^[0-9]{1,}$/", $_POST["field_rc"])) ? $_POST["field_rc"] : "0";

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

		$this->_DB = isset($_POST["bd"]) ? $this->set_value($_POST["bd"]) : "";

		$this->_TB = isset($_POST["tb"]) ? $this->set_value($_POST["tb"]) : "";

		$this->list_tb = isset($_POST["list_tb"]) ? $_POST["list_tb"] : [];

		$this->key = isset($_POST["key"]) ? $_POST["key"] : [];

		$this->field = isset($_POST["field"]) ? $this->set_value_list($_POST["field"]) : [];

		$this->name_new = isset($_POST["name_new"]) ? $this->set_value($_POST["name_new"]) : "";

		$this->cl_del = isset($_POST["cl_del"]) ? $this->set_value($_POST["cl_del"]) : "";

		$this->cl_def = isset($_POST["cl_def"]) ? $this->set_value($_POST["cl_def"]) : "";

		$this->cl_in = isset($_POST["cl_in"]) ? $this->set_value($_POST["cl_in"]) : "";

		$this->cl_change = isset($_POST["cl_change"]) ? $this->set_value($_POST["cl_change"]) : "";

		$this->tb_def = isset($_POST["tb_def"]) ? $this->set_value($_POST["tb_def"]) : "";

		$this->script = isset($_POST["script"]) ? $this->set_value($_POST["script"]) : "";

		$this->script_id = isset($_POST["script_id"]) ? $this->set_value($_POST["script_id"]) : "";
	}


	private function set_value($value)
	{
		if(get_magic_quotes_gpc() === 1){

			return stripslashes(trim($value));
		}
		else{

			return trim($value);
		}
	}


	private function set_value_list($list)
	{
		$RT = [];

		foreach($list as $key=>$value){

				$RT[$key] = $this->set_value($value);
		}

		return $RT;
	}


	private function action()
	{
		if($this->action !== "")
		{
			switch($this->action)
			{
				case "_FIND_DB":
				{
					$this->manager->searching($this->_DB, $this->manager->get_list_tb($this->_DB), $this->cl_in);
				}
				break;

				case "_FIND_TB":
				{
					$this->manager->searching($this->_DB, [$this->_TB], $this->cl_in);
				}
				break;

				case "_CLEAR_DB":
				{
					$this->manager->clear_db($this->list_db);
					if($this->_DB === ""){$this->display = "db";}
				}
				break;

				case "_DELETE_DB":
				{
					$this->manager->delete_db($this->list_db);
					$this->_DB = "";
					$this->display = "db";
					$this->nv["from_db"] = "0";
				}
				break;

				case "_RENAME_TB":
				{
					if($this->manager->rename_tb($this->_DB, $this->_TB, $this->name_new)){

						$this->_TB = unpack('H*', "$this->name_new")[1];
					}
				}
				break;

				case "_COPY_TB":
				{
					$this->manager->copy_tb($this->_DB, [$this->_TB], $this->_DB, $this->name_new, true);
					$this->_TB = "";
				}
				break;

				case "_CLEAR_TB":
				{
					$this->manager->clear_tb($this->_DB, $this->list_tb);
				}
				break;

				case "_DELETE_TB":
				{
					$this->manager->delete_tb($this->_DB, $this->list_tb);
					$this->_TB = "";
					$this->nv["from_tb"] = "0";
				}
				break;

				case "_EXPORT_TB":
				{
					$this->manager->export_tb($this->_DB, $this->list_tb, $this->exceptions);
				}
				break;

				case "_INSERT_FL":
				{
					$this->manager->insert_cl($this->_DB, $this->_TB, $this->cl_def, $this->cl_in);
					$this->nv["field_rc"] = "0";
				}
				break;

				case "_UPDATE_FL":
				{
					$this->manager->update_cl($this->_DB, $this->_TB, $this->cl_change, $this->cl_def);
				}
				break;

				case "_DELETE_FL":
				{
					$this->manager->delete_cl($this->_DB, $this->_TB, $this->cl_del);
					$this->nv["field_rc"] = "0";
				}
				break;

				case "_UPDATE_TB":
				{
					$this->manager->update_tb($this->_DB, $this->_TB, $this->tb_def);
				}
				break;

				case "_INSERT_RC":
				{
					$this->manager->insert_rc($this->_DB, $this->_TB, $this->field);
				}
				break;

				case "_UPDATE_RC":
				{
					$this->manager->update_rc($this->_DB, $this->_TB, $this->key, $this->field);
				}
				break;

				case "_DELETE_RC":
				{
					$this->manager->delete_rc($this->_DB, $this->_TB, $this->key);
					$this->nv["from_rc"] = "0";
				}
				break;

				case "_RUN_SQL":
				{
					$this->manager->sqls_eval_list($this->script, $this->_DB);
				}
				break;

   				default: {}
				break;
			}
		}
	}

}



