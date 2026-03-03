<?php

defined("_EXEC") or die();


Class Controller extends Request
{
	private $manager;
	private $view;
	private $DATA;

	private $auth;

	private $script_sql;
	protected $LIMIT;

	public function __construct($USER, $CHECK, $SQL)
	{
		$this->LIMIT = ["15", "50", "100", "250", "500"];

		define("_WHERE_CN_DEF", 2);

		$this->auth = new Auth($USER, $CHECK);

		$this->request();

		require __DIR__."/Manager.php";

		$this->manager = new Manager($this->LIMIT);

		$this->manager->connect($USER[$this->nv["_US"]]["server"]);

		if($this->manager->connect){

			$this->auth->user_form($this->manager->_RS["MESSAGE"]["connect"]);

			return;
		}

		$this->action();

		require __DIR__."/View.php";

		$this->view = new View($USER[$this->nv["_US"]], $this->manager->GT);

		if($this->action === "VIEW_DATA")
		{
			$this->DATA = $this->manager->rc( $this->nv, "" );

			$this->view->rc_data_async($this->DATA, $this->nv, "edit");

			die();
		}

		if($this->nv["_SH"] === "")
		{
			$this->DATA = $this->manager->sh($this->nv);
		}
		elseif(($this->nv["_SH"] !== "") && ($this->nv["_TB"] !== ""))
		{
			$this->DATA = $this->manager->rc($this->nv, "");
		}
		elseif(($this->nv["_SH"] !== "") && ($this->nv["_TB"] === ""))
		{
			$this->DATA = $this->manager->tb($this->nv);
		}

		$this->script_sql["userscripts"] = (isset( $SQL["userscripts"])) ?  $SQL["userscripts"] : [];
		$this->script_sql["objects"] = $this->DATA["SQL"];

		$this->view->main($this->manager->current_user, $this->nv, $this->display);
		$this->view->sql($this->script_sql, $this->script_id_sql, $this->nv, $this->display);
		$this->view->ms($this->manager->_RS);

		$this->view->rc_nav($this->DATA, $this->nv);

		$this->view->rc($this->DATA, $this->nv, ((isset($SQL["functions"])) ? $SQL["functions"] : ["..." => []]));
	}


	private function action()
	{
		if($this->action !== "")
		{
			switch($this->action)
			{
				case "_RESET_FILTER_rc":
				{
					$this->nv = $this->manager->reset_fl($this->nv);
				}
				break;

				case "_ADD_FILTER_rc":
				{
					$this->nv["fl_count_rc"] = ($this->nv["fl_count_rc"]+1);
					$this->nv["fl_view"] = true;
				}
				break;

				case "_RUN_SQL":
				{
					$this->manager->sqlsm($this->script, $this->nv["_SH"]);
					$this->nv = $this->manager->reset_nv($this->nv, $this->LIMIT);
				}
				break;

				case "_CLEAR_SH":
				{
					$this->manager->clear_sh($this->list_rc);
				}
				break;

				case "_CLEAR_SH_FILTER":
				{
					$this->manager->clear_sh($this->manager->list_sh_filter($this->nv));
				}
				break;

				case "_DELETE_SH":
				{
					$this->manager->delete_sh($this->list_rc);
					$this->nv["_SH"] = "";
				}
				break;

				case "_DELETE_SH_FILTER":
				{
					$this->manager->delete_sh($this->manager->list_sh_filter($this->nv));
					$this->nv = $this->manager->reset_fl($this->nv);
					$this->nv["_SH"] = "";
				}
				break;

				case "_CLEAR_TB":
				{
					$this->manager->clear_tb($this->nv["_SH"], $this->list_rc);
				}
				break;

				case "_CLEAR_TB_FILTER":
				{
					$this->manager->clear_tb($this->nv["_SH"], $this->manager->list_tb_filter($this->nv));
				}
				break;

				case "_DELETE_TB":
				{
					$this->manager->delete_tb($this->nv["_SH"], $this->list_rc);
					$this->nv["_TB"] = "";
				}
				break;

				case "_DELETE_TB_FILTER":
				{
					$this->manager->delete_tb($this->nv["_SH"], $this->manager->list_tb_filter($this->nv));
					$this->nv = $this->manager->reset_fl($this->nv);
				}
				break;

				case "_INSERT_RC":
				case "_COPY_RC":
				case "_UPDATE_RC":
				case "_DELETE_RC":				
				{	
					$this->manager->edit_rc($this->nv["_SH"], $this->nv["_TB"], $this->key, $this->list_rw, $this->field,
						$this->text, $this->file, $this->function, $this->action);						
				}
				break;

				case "_DELETE_RC_FILTER":
				{
					$this->manager->delete_rc_filter($this->nv);
					$this->nv = $this->manager->reset_fl($this->nv);
				}
				break;

				case "_FIND_TB":
				{
					$this->manager->searching($this->nv["_SH"], $this->nv["_TB"], $this->cl_in, $this->cl_df);
				}
				break;

				case "_VIEW_SQL_SH":
				{
					if(count($this->list_rc) === 0){$this->list_rc[] = $this->nv["_SH"];}
					$this->manager->res_get($this->manager->export_sql($this->list_rc, [], $this->nv, "SH"));
				}
				break;

				case "_VIEW_SQL_SH_FILTER":
				{
					$this->manager->res_get($this->manager->export_sql($this->manager->list_sh_filter($this->nv), [], $this->nv, "SH"));
				}
				break;

				case "_VIEW_SQL_TB":
				{
					$this->manager->res_get($this->manager->export_sql([$this->nv["_SH"]], $this->list_rc, $this->nv, "TB"));
				}
				break;

				case "_VIEW_SQL_TB_FILTER":
				{
					$this->manager->res_get($this->manager->export_sql([$this->nv["_SH"]], $this->manager->list_tb_filter($this->nv), $this->nv, "TB"));
				}
				break;

				case "_VIEW_SQL_RC_FILTER":
				{
					$this->manager->res_get($this->manager->export_sql([$this->nv["_SH"]], [$this->nv["_TB"]], $this->nv, "RC"));
				}
				break;

				case "_SAVE_SQL_SH":
				{
					if(count($this->list_rc) === 0){$this->list_rc[] = $this->nv["_SH"];}
					$this->manager->export_get($this->manager->export_sql($this->list_rc, [], $this->nv, "SH"));
				}
				break;

				case "_SAVE_SQL_SH_FILTER":
				{
					$this->manager->export_get($this->manager->export_sql($this->manager->list_sh_filter($this->nv), [], $this->nv, "SH"));
				}
				break;

				case "_SAVE_SQL_TB":
				{
					$this->manager->export_get($this->manager->export_sql([$this->nv["_SH"]], $this->list_rc, $this->nv, "TB"));
				}
				break;

				case "_SAVE_SQL_TB_FILTER":
				{
					$this->manager->export_get($this->manager->export_sql([$this->nv["_SH"]], $this->manager->list_tb_filter($this->nv), $this->nv, "TB"));
				}
				break;

				case "_SAVE_SQL_RC_FILTER":
				{
					$this->manager->export_get($this->manager->export_sql([$this->nv["_SH"]], [$this->nv["_TB"]], $this->nv, "RC"));
				}
				break;

   				default:{}
				break;
			}
		}
	}

}
