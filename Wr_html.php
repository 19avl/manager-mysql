<?php

defined("_EXEC") or die();

Class Wr_html
{
	public function __construct(){}	


	protected function form_open($class="")
	{
		print "<form name='' method='post' action='' class='".$class."' enctype='multipart/form-data' onSubmit='return false;'>";
	}		


	protected function form_close()
	{
		print "</form>";
	}


	protected function div($id, $class, $style, $value, $event)
	{
		print "<div id='".$id."' class='".$class."' style='".$style."' ".$event.">".$value."</div>";
	}


	protected function div_open($id, $class, $style, $event)
	{
		print "<div id='".$id."' class='".$class."' style='".$style."' ".$event.">";
	}


	protected function div_close()
	{
		print "</div>";
	}


	protected function btn($name, $class, $value, $event)
	{
		print "<input type='button' name='".$name."' class='".$class."' value='".$value."' ".$event.">";
	}


	protected function input($name, $id, $class, $value, $event, $flag, $placeholder)
	{
		$type = "text";
		if($flag === "hidden"){

			$type = "hidden";
			$flag = "";
		}

		print "<input id='".$id."' name='".$name."' type='".$type."' class='".$class."' value='".$value."' ".
			$event." ".$flag." placeholder='".$placeholder."'>";
	}


	protected function checkbox($name, $id, $class, $value, $event, $checked)
	{
		print "<input type='checkbox' name='".$name."' class='".$class."' value='".$value."' ".$event." ".$checked.">";
	}


	protected function radio($name, $id, $class, $value, $event, $checked)
	{
		print "<input type='radio' name='".$name."' value='".$value."' ".$event." ".$checked.">";
	}


	protected function textarea($name, $id, $class, $value, $event)
	{
		print "<textarea id='".$id."' name='".$name."' class='".$class."' ".$event.">".$value."</textarea>";
	}


	protected function select($foreach, $selected, $name, $id, $class, $title, $event, $ch, $fk, $fv)
	{
		print "<select id='".$id."' name='".$name."' class='".$class."' size='1' ".$event.">";

		if($title !== ""){print "<OPTION SELECTED value='' disabled> ".$title." </OPTION>";}

		foreach($foreach as $k=>$v){

			if($selected === (string)call_user_func($ch, $k, $v)){

				print "<OPTION SELECTED value='".call_user_func($fk, $k, $v)."' > ".call_user_func($fv, $k, $v)." </OPTION>";
			}
			else{

				print "<OPTION value='".call_user_func($fk, $k, $v)."' > ".call_user_func($fv, $k, $v)." </OPTION>";
			}
		}

		print "</select>";
	}


}