<?php

defined("_EXEC") or die();

trait Wr_html
{
	protected function form_open($class="")
	{
		print "<form name='' method='post' action='' class='".$class."' enctype='' onSubmit='return false;'>";
	}		


	protected function form_close()
	{
		print "</form>";
	}


	protected function tg($name, $id, $class, $style, $value, $event)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		
		print "<".$name." ".$id." class='".$class."' style='".$style."' ".$event.">".$value."</".$name.">";
	}


	protected function tg_open($name, $id, $class, $style, $event)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		
		print "<".$name." ".$id." class='".$class."' style='".$style."' ".$event.">";
	}


	protected function tg_close($name)
	{
		print "</".$name.">";
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

		$id = ($id !== "") ? "id='".$id."'" : "";

		print "<input ".$id." name='".$name."' type='".$type."' class='".$class."' value='".$value."' ".
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


	protected function textarea($name, $id, $class, $value, $event, $flag)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";		
		
		print "<textarea ".$id." name='".$name."' class='".$class."' ".$event." ".$flag.">".$value."</textarea>";
	}


	protected function select($foreach, $selected, $name, $id, $class, $title, $event, $ch, $fk, $fv)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";		
		
		print "<select ".$id." name='".$name."' class='".$class."' size='1' ".$event.">";

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