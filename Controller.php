<?php

defined("_EXEC") or die();


Class Controller extends Query
{
	use Convert;

	private $manager;
	private $view;
	private $DATA;

	public function __construct($SERVER, $LIMIT, $PASS)
	{
		$this->query();

		$this->control = new Control();
		$this->control->main($PASS);

		$this->manager = new Manager();

		$this->manager->connect($SERVER);

		if($this->manager->connect){

			$this->control->ms($this->manager->_LOG["MESSAGE"]["connect"]);
			
			return;
		}

		$this->action();
		
		$manager_info = $this->manager->info();

		if(file_exists(__DIR__."/sql.php")){

			require __DIR__."/sql.php";
		}
		if(!isset($SQL)){$SQL = [];}
		if(!isset($FUNCTION)){$FUNCTION = [];}

		if($this->_SH === ""){

			$this->DATA = $this->manager->sh($this->nv, $LIMIT);

			$this->script = $this->manager->mk($this->script_id, $SQL);	
		}
		elseif(($this->_SH !== "") && ($this->_TB !== ""))
		{
			$this->DATA = $this->manager->rc( $this->_SH, $this->_TB, $this->nv, $LIMIT, "" );
			
			if(isset($this->DATA["ALTER_TABLE"]))
			{
				$SQL = array_merge($SQL, 
					$this->DATA["ALTER_TABLE"]["ADD"],
					$this->DATA["ALTER_TABLE"]["CHANGE"],
					$this->DATA["ALTER_TABLE"]["DROP"]);
			}			
			
			$this->script = $this->manager->mk($this->script_id, $SQL);
		}
		elseif(($this->_SH !== "") && ($this->_TB === ""))
		{
			$this->DATA = $this->manager->tb( $this->_SH, $this->nv, $this->cl_sl, $LIMIT);

			if(isset($this->DATA["ALTER_SCHEMA"]["CHANGE"]))
			{
				$SQL = array_merge($SQL, $this->DATA["ALTER_SCHEMA"]["CHANGE"]);
			}

			if(isset($this->DATA["SU_A"]["LIST"]))
			{
				$SQL = array_merge($SQL, $this->DATA["SU_A"]["LIST"]);
			}

			$this->script = $this->manager->mk($this->script_id, $SQL);
		}

		$this->view = new View();

		$this->view->main($this->manager->current_user, $this->_SH, $this->_TB, $this->nv);

		$this->view->dl_message();

		$this->view->mk($this->_SH, $this->_TB, $SQL, $this->script, $this->nv, $this->display);

		$this->view->message($this->manager->_LOG);

		if($this->_SH === ""){

			$this->view->sh($this->manager->current_user,
				$this->_SH, $this->_TB, $this->DATA, $this->nv, $manager_info);	
		}
		elseif(($this->_SH !== "") && ($this->_TB !== ""))
		{
			$this->view->rc($this->manager->current_user,
				$this->_SH, $this->_TB, $this->DATA, $this->nv, $FUNCTION, $this->manager->ext, $this->display);
		}
		elseif(($this->_SH !== "") && ($this->_TB === ""))
		{
			$this->view->tb($this->manager->current_user,
				$this->_SH, $this->DATA, $this->action, $this->nv, $this->display);
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

				case "_COPY_SH":
				{
					$this->manager->copy_sh($this->_SH, $this->cl_in);
					$this->display = "sh";
					$this->_SH = "";
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

				case "_RENAME_TB":
				{
					if($this->manager->rename_tb($this->_SH, $this->_TB, $this->cl_tr, $this->cl_in)){

						$this->_TB = "";					
					}
				}
				break;

				case "_COPY_TB":
				{
					$this->manager->copy_tb($this->_SH, [$this->_TB], $this->cl_tr, $this->cl_in, true);

					$this->_TB = "";
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



