<?php

defined("_EXEC") or die();

trait Wr_html
{
	protected function tg($name, $id, $class, $style, $value, $event)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$style = ($style !== "") ? "style='".$style."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<".$name." ".$id." ".$class." ".$style." ".$event.">".$value."</".$name.">";
	}


	protected function tg_open($name, $id, $class, $style, $event)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$style = ($style !== "") ? "style='".$style."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<".$name." ".$id." ".$class." ".$style." ".$event.">";
	}


	protected function tg_close($name)
	{
		print "</".$name.">";
	}


	protected function form_open($class="", $id="")
	{
		print "<form id='".$id."' name='' method='post' action='' class='".$class."' enctype='' onSubmit='return false;'>";
	}


	protected function form_close()
	{
		print "</form>";
	}


	protected function btn($name, $id, $class, $value, $event)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<input type='button' ".$id." name='".$name."' ".$class." value='".$value."' ".$event.">";
	}


	protected function file($name, $id, $class, $value, $event, $flag)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<input type='file' ".$id." name='".$name."' ".$class." value='".$value."' ".$event." ".$flag.">";
	}

	protected function input($name, $id, $class, $value, $event, $flag, $placeholder)
	{
		$type = "text";
		if($flag === "hidden"){

			$type = "hidden";
			$flag = "";
		}

		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";

		print "<input ".$id." name='".$name."' type='".$type."' ".$class." value='".$value."' ".
			$event." ".$flag." placeholder='".$placeholder."'>";
	}


	protected function checkbox($name, $id, $class, $value, $event, $checked)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";
		
		print "<input ".$id." type='checkbox' name='".$name."' ".$class." value='".$value."' ".$event." ".$checked.">";
	}


	protected function radio($name, $id, $class, $value, $event, $checked)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";		
		
		print "<input ".$id." type='radio' name='".$name."' ".$class." value='".$value."' ".$event." ".$checked.">";
	}


	protected function textarea($name, $id, $class, $value, $event, $flag)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";	

		print "<textarea ".$id." name='".$name."' ".$class." ".$event." ".$flag.">".$value."</textarea>";
	}


	protected function select($foreach, $selected, $disabled, $name, $id, $class, $title, $event, $ch, $fk, $fv)
	{
		$id = ($id !== "") ? "id='".$id."'" : "";
		$class = ($class !== "") ? "class='".$class."'" : "";	

		print "<select ".$id." name='".$name."' ".$class." size='1' ".$event.">";

		if($title !== ""){print "<OPTION SELECTED value='' disabled> ".$title." </OPTION>";}

		foreach($foreach as $k=>$v){

			if(($disabled !== "") && preg_match("/".$disabled."/", (string)call_user_func($ch, $k, $v))){
				
				print "<OPTION value='".call_user_func($fk, $k, $v)."' disabled> ".
					call_user_func($fv, $k, $v)." </OPTION>";	
			}
			elseif($selected === (string)call_user_func($ch, $k, $v)){

				print "<OPTION SELECTED value='".call_user_func($fk, $k, $v)."' > ".
					call_user_func($fv, $k, $v)." </OPTION>";
			}
			else{

				print "<OPTION value='".call_user_func($fk, $k, $v)."' > ".
					call_user_func($fv, $k, $v)." </OPTION>";
			}	
		}

		print "</select>";
	}


}