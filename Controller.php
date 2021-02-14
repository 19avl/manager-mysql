<?php

defined("_EXEC") or die();


Class Controller extends Query
{
	use Convert;

	private $manager;
	private $view;

	private $LIST_SQL;
	private $DATA_DB;
	private $DATA;

	public function __construct($SERVER, $LIMIT, $PASS)
	{
		$this->exceptions = [
			"geo" => ["geometry", "point", "linestring", "polygon",
				"multipoint", "multilinestring", "multipolygon","geomcollection","geometrycollection"],
			"bin" => ["tinyblob", "blob", "mediumblob", "longblob", "varbinary"]
		];

		$this->view = new View();

		$this->request();

		$this->control = new Control();

		$this->control->main($PASS);

		$this->manager = new Manager();

		$this->manager->connectdb($SERVER);

		if($this->manager->connect){

			$this->view->message($this->manager->_LOG);

			return;
		}

		$this->action();

		if(file_exists(__DIR__."/sql.php")){

			require __DIR__."/sql.php";
		}
		if(!isset($SQL)){$SQL = [];}

		$this->DATA_DB = $this->manager->db($this->nv, $LIMIT);

		$this->LIST_SQL = $this->manager->mk($this->script_id, $SQL);

		if(($this->_DB !== "") && ($this->_TB !== ""))
		{
			$this->DATA = $this->manager->rc( $this->_DB, $this->_TB, $this->nv, $this->exceptions, $LIMIT, "" );
		}
		elseif(($this->_DB !== "") && ($this->_TB === ""))
		{
			$this->DATA = $this->manager->tb( $this->_DB, $this->nv, $this->cl_sl, $LIMIT);
		}

		$this->view->dl_message();

		$this->view->main($this->_DB, $this->_TB, $this->nv, $SQL);

		$this->view->db($this->DATA_DB, $this->_DB, $this->_TB, $this->nv, $this->display);

		$this->view->mk($this->_DB, $this->_TB, $this->LIST_SQL, $this->nv, $this->display);

		$this->view->stat($this->manager->status(), $SERVER);

		if($this->_DB === ""){

			$this->view->info($this->manager->info($SERVER["host"]));
		}

		$this->view->message($this->manager->_LOG);

		if(($this->_DB !== "") && ($this->_TB !== ""))
		{
			$this->view->rc($this->_DB, $this->_TB, $this->DATA, $this->nv, $this->exceptions, $this->display);
		}
		elseif(($this->_DB !== "") && ($this->_TB === ""))
		{
			$this->view->tb($this->_DB, $this->DATA, $this->action, $this->nv, $this->display);
		}
	}


	private function action()
	{
		if($this->action !== "")
		{
			switch($this->action)
			{
				case "_RUN_SQL":
				{
					$this->manager->sqls_eval_list($this->script, $this->_DB);
				}
				break;

				case "_CLEAR_DB":
				{
					$this->manager->clear_db($this->list_db);

					if($this->_DB === ""){

						$this->display = "db";
					}
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

				case "_EXPORT_DB":
				{
					if(count($this->list_db) === 0){

						$this->list_db[] = $this->_DB;
					}

					$this->manager->export_sql($this->list_db, [], $this->nv, $this->exceptions, "DB");
				}
				break;

				case "_CREATE_SUB":
				{
					$this->manager->create_sub($this->_DB, $this->cl_df);
				}
				break;

				case "_UPDATE_SUB":
				{
					$this->manager->update_sub($this->_DB, $this->cl_tr, $this->cl_in, $this->cl_df, $this->cl_dl);
				}
				break;

				case "_DELETE_SUB":
				{
					$this->manager->delete_sub($this->_DB, $this->cl_tr, $this->cl_in);
				}
				break;

				case "_RENAME_TB":
				{
					if($this->manager->rename_tb($this->_DB, $this->_TB, $this->cl_in)){

						$this->_TB = $this->s2h($this->set_name($this->cl_in));
					}
				}
				break;

				case "_COPY_TB":
				{
					$this->manager->copy_tb($this->_DB, [$this->_TB], $this->_DB, $this->cl_in, true);
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
					$this->manager->export_sql([$this->_DB], $this->list_tb, $this->nv, $this->exceptions, "TB");
				}
				break;

				case "_UPDATE_TB":
				{
					$this->manager->update_tb($this->_DB, $this->_TB, $this->cl_df);
				}
				break;


				case "_INSERT_RC":
				{
					$this->manager->insert_rc($this->_DB, $this->_TB, $this->field, $this->exceptions);
				}
				break;

				case "_UPDATE_RC":
				{
					$this->manager->update_rc(
						$this->_DB, $this->_TB, $this->key, $this->field, $this->exceptions);
				}
				break;

				case "_DELETE_RC":
				{
					$this->manager->delete_rc($this->_DB, $this->_TB, $this->key);
					$this->nv["from_rc"] = "0";
				}
				break;

				case "_FIND_DB":
				{
					$this->manager->searching($this->_DB, $this->manager->get_list_tb($this->_DB), $this->cl_in, $this->cl_df);
				}
				break;

				case "_FIND_TB":
				{
					$this->manager->searching($this->_DB, [$this->_TB], $this->cl_in, $this->cl_df);
				}
				break;

   				default:{}
				break;
			}

		}
	}

}



