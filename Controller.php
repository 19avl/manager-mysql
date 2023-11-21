<?php

defined("_EXEC") or die();


Class Controller extends Query
{
	use Convert;

	private $manager;
	private $view;
	private $DATA;

	public function __construct($LIMIT, $USER)
	{
		$this->query();

		$this->control = new Control();
		$this->control->main($USER["pass"]);

		require __DIR__."/Manager.php";

		$this->manager = new Manager();

		$this->manager->connect($USER["server"]);

		if($this->manager->connect){

			$this->control->ms($this->manager->_LOG["MESSAGE"]["connect"]);

			return;
		}

		$this->action();

		$SQL_SCRIPTS = [];

		if(file_exists(__DIR__."/sql.php")){

			require __DIR__."/sql.php";
		}

		if(!isset($SQL_FUNCTIONS)){$SQL_FUNCTIONS = ["..." => []];}

		if($this->_SH === ""){

			$this->DATA = $this->manager->sh($this->nv, $LIMIT);
		}
		elseif(($this->_SH !== "") && ($this->_TB !== ""))
		{
			$this->DATA = $this->manager->rc( $this->_SH, $this->_TB, $this->nv, $LIMIT, "" );

			$SQL_SCRIPTS["objects"] = $this->DATA["SQL"];
		}
		elseif(($this->_SH !== "") && ($this->_TB === ""))
		{
			$this->DATA = $this->manager->tb( $this->_SH, $this->nv, $this->cl_sl, $LIMIT);

			$SQL_SCRIPTS["objects"] = $this->DATA["SQL"];
		}

		$this->script_sql = "";

		if($this->script_id !== ""){
	
			$this->script_sql = $SQL_SCRIPTS["userscripts"][$this->script_id];
		}

		require __DIR__."/View.php";

		$this->view = new View();

		$this->view->main($this->manager->current_user, $this->_SH, $this->_TB, $this->nv);

		$this->view->dl_message();

		$this->view->sql($this->_SH, $this->_TB, $SQL_SCRIPTS, $this->script_sql, $this->nv, $this->display);

		$this->view->message($this->manager->_LOG);

		if($this->_SH === ""){

			$this->view->sh($this->manager->current_user,
				$this->_SH, $this->_TB, $this->DATA, $this->nv);
		}
		elseif(($this->_SH !== "") && ($this->_TB !== "")){

			$this->view->rc($this->_SH, $this->_TB,
				$this->DATA, $this->nv, $SQL_FUNCTIONS, $this->manager->ext);
		}
		elseif(($this->_SH !== "") && ($this->_TB === "")){

			$this->view->tb($this->_SH, $this->DATA, $this->action, $this->nv);
		}
	}


	private function action()
	{
		if($this->action !== "")
		{
			switch($this->action)
			{
				case "_RESET_FILTER_sh":
				{
					$this->nv["fl_field_sh"] = [];
					$this->nv["fl_value_sh"] = [];
					$this->nv["fl_operator_sh"] = [];
					$this->nv["fl_and_sh"] = [];
				}
				break;

				case "_RESET_FILTER_tb":
				{
					$this->nv["fl_field_tb"] = [];
					$this->nv["fl_value_tb"] = [];
					$this->nv["fl_operator_tb"] = [];
					$this->nv["fl_and_tb"] = [];
				}
				break;

				case "_RESET_FILTER_rc":
				{
					$this->nv["fl_field_rc"] = [];
					$this->nv["fl_value_rc"] = [];
					$this->nv["fl_operator_rc"] = [];
					$this->nv["fl_and_rc"] = [];
				}
				break;

				case "_FILE_SQL":
				{
					$this->manager->sqlsm(base64_decode($this->script_file), $this->_SH);
				}
				break;

				case "_RUN_SQL":
				{
					$this->manager->sqlsm($this->script, $this->_SH);
				}
				break;

				case "_CLEAR_SH":
				{
					$this->manager->clear_sh($this->list_sh);

					if($this->_SH === ""){

						$this->display = "sh";
					}
				}
				break;

				case "_DELETE_SH":
				{
					$this->manager->delete_sh($this->list_sh);
					$this->_SH = "";
					$this->display = "sh";
					$this->nv["from_sh"] = "0";
				}
				break;

				case "_EXPORT_SQL_SH":
				{
					if(count($this->list_sh) === 0){

						$this->list_sh[] = $this->_SH;
					}

					$this->manager->export_sql($this->list_sh, [], $this->nv, "SH");
				}
				break;

				case "_CLEAR_TB":
				{
					$this->manager->clear_tb($this->_SH, $this->list_tb);
				}
				break;

				case "_DELETE_TB":
				{
					$this->manager->delete_tb($this->_SH, $this->list_tb);
					$this->_TB = "";
					$this->nv["from_tb"] = "0";
				}
				break;

				case "_EXPORT_SQL_TB":
				{
					$this->manager->export_sql([$this->_SH], $this->list_tb, $this->nv, "TB");
				}
				break;

				case "_INSERT_RC":
				case "_UPDATE_RC":
				{
					$this->manager->update_rc(
						$this->_SH, $this->_TB, $this->key, $this->field, $this->file, $this->blob_ch,
							$this->function, $this->action);
				}
				break;

				case "_DELETE_RC":
				{
					$this->manager->delete_rc($this->_SH, $this->_TB, $this->key);
					$this->nv["from_rc"] = "0";
				}
				break;

				case "_FIND_TL":
				{
					$this->manager->searching(
						$this->manager->get_list_sh(), [], $this->cl_in, $this->cl_df);
				}
				break;

				case "_FIND_SH":
				{
					$this->manager->searching(
						[$this->_SH], $this->manager->get_list_tb($this->_SH), $this->cl_in, $this->cl_df);
				}
				break;

				case "_FIND_TB":
				{
					$this->manager->searching(
						[$this->_SH], [$this->_TB], $this->cl_in, $this->cl_df);
				}
				break;

   				default:{}
				break;

			}
		}
	}

}



