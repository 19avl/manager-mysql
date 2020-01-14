<?php

defined("_EXEC") or die();

?>

<!DOCTYPE html>
<html>
<head>
<META http-equiv=Content-Type content="text/html; charset=utf-8"/>
<title>manager-mysql</title>

<script type="text/javascript">

"use strict";

var ct =
{
	pst: function(arg){

		var status = "status";
		var container = "content";

		var data = "session=1"+"&request="+this.set_ps();

		var url = "<?php echo _URL; ?>";

		var X = false;

		if (window.XMLHttpRequest){ X = new XMLHttpRequest(); }
		else if (window.ActiveXObject){

			try{ X = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (CatchException){ X = new ActiveXObject("Msxml2.XMLHTTP"); }
		}

		if (!X){

			alert("<?php echo _AT_ERROR; ?>");
			return;
		}

		X.onreadystatechange = function(){

			if (X.readyState == 4){

				if (X.status == 200){

					document.getElementById(container).innerHTML = X.responseText;
					document.getElementById(status).innerHTML = "";
				}
			}
			else{ document.getElementById(status).innerHTML = "<?php echo _MESSAGE_WAIT; ?>"; }
		}

		X.open("POST", url, true);
		X.setRequestHeader("Max-Forwards", "0");
		X.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=utf-8");
		X.send(data);
	},

	clr_ps: function(){

		document.getElementById("pass").innerHTML = "";
		this.pst("&exit=exit");
	},

	get_ps: function(){

		var pass = document.getElementById("en_pass").value;
		var result = pass.replace(/^\s+/, '').replace(/\s+$/, '');

		document.getElementById("pass").innerHTML = this.hashE(result);

		this.pst("");
	},

	set_ps: function(oForm){

		if(document.getElementById("request")){

			var request = document.getElementById("request").value;
			var pass = document.getElementById("pass").innerHTML;

			return this.hashE(""+encodeURIComponent(this.enc(request, pass))+this.str_request(oForm));
		}

		return "";
	},

	rs: function()
	{
		var str = "";

		m = parseInt(0);
		n = parseInt(16);

		for(var i=0;i<16;i++){

			num = Math.floor( Math.random() * (n - m + 1) ) + m;
			str += num.toString(16);
		}

		return str;
	},

	enc: function(key, str)
	{
		var $hash = "";
		var m = 251;

		for (var i = 0; i < str.length; i++) {

			$hash += (Math.pow(parseInt(key[i]), parseInt(str[i])) % m).toString(16);
		}

		return $hash;
	},

	hashE: function(str)
	{
		var str = unescape(encodeURIComponent(str));

		var M = 25717;
		var L = str.length;

		return this.hc(0, L, 2, M, str)+""+this.hc(1, L, 2, M, str);
	},

	hc: function(N, L, S, M, str)
	{
		var H = 0;
		for (var i = N; i < L; i=i+S) {

			H = (( H % M ) * 10000) + ( str.charCodeAt(i) % M );
		}
		return H;
	},

	str_request: function(oForm)
	{
		var A = (<?php echo json_encode(Control::$CHECK); ?>).replace(/\s{1,}/g, "").split(",");

		var R = [];

		for(var i in A)
		{
			for(var c in oForm)
			{
				if (c === A[i]){

						R[A[i]] = oForm[c]+"&";
				}
				else if((new RegExp('\^'+A[i]+'\\[')).test(c)){

					if(R[A[i]]){ R[A[i]] += oForm[c]+"&"; }
					else{ R[A[i]] = oForm[c]+"&"; }
				}
			}
		}

		var str_check = "";
		for(i in A){

			if(R[A[i]]){

				if(typeof R[A[i]] === "string"){ str_check += R[A[i]]; }
			}
		}

		return str_check;
	},
};



var ms =
{
	pst: function(data)
	{
		var container = "content";
		var status = "status";
		var status_back = "status_back";

		var url = "<?php echo _URL; ?>";

		var X = false;

		if (window.XMLHttpRequest){ X = new XMLHttpRequest(); }
		else if (window.ActiveXObject){

			try{ X = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (CatchException){ X = new ActiveXObject("Msxml2.XMLHTTP"); }
		}

		if (!X){

			alert("<?php echo _MESSAGE_ERROR; ?>");
			return;
		}

		X.onreadystatechange = function(){

			if (X.readyState == 4){

				if (X.status == 200){

					document.getElementById(container).innerHTML = X.responseText;

					document.getElementById(status).value = "";
					document.getElementById(status).style.display = "none";

					document.getElementById(status_back).style.display = "none";

					if(document.getElementById("div_message") || document.getElementById("div_error")){

						window.scrollTo(0,0);
					}
				}
			}
			else{

				document.getElementById(status).value = "<?php echo _MESSAGE_WAIT; ?>";
				document.getElementById(status).style.display = "";

				document.getElementById(status_back).style.display = "";
			}
		}

		X.open("POST", url, true);
		X.setRequestHeader("Max-Forwards", "0");
		X.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=utf-8");
		X.send(data);
	},


	RF: function(action, bd, table, form, request)
	{
		if(action !== ""){form.action.value=action;}
		if(bd !== ""){form.bd.value=bd;}
		if(table !== ""){form.tb.value=table;}

		var data = "";
		var oForm = [];

		if(form != "")
		{
			for(var i=0;i<form.length;i++)
			{
				if(form[i].name)
				{
					if(form[i].type == "checkbox")
					{
						if(form[i].checked){

							data += encodeURIComponent(form[i].name)+"="+encodeURIComponent(form[i].value)+"&";
							oForm[form[i].name] = form[i].value;
						}
					}
					else
					{
						if(!form[i].disabled){

							data += encodeURIComponent(form[i].name)+"="+encodeURIComponent(form[i].value)+"&";
							oForm[form[i].name] = form[i].value;
						}
					}
				}
			}
		}
		else{data += "session=1$";}

		if(request === 1){

			form["request"].value = ct.set_ps(oForm);
			form.submit();
		}
		else{

			data += "request=" + ct.set_ps(oForm);
			this.pst(data);
		};

	},

	AL: function(action, id_win, id, name, el, form, cbName, text, warning_list, request_list, war)
	{
		if(cbName !== "")
		{
			var flag = 0;

			if(form[cbName])
			{
				if(form[cbName].length){

					for (var i=0; i < form[cbName].length; i++) {

						if(form[cbName][i].checked == true){flag = 1;}
					}
				}
				else{ if(form[cbName].checked == true){flag = 1;} }

				if(flag === 0){

					this.open_alert(war);

					if( el.nodeName === "SELECT"){ el[0].selected='selected'; }

					return false;
				}
			}
		}

		for (var i=0; i < el.length; i++) {

			if(el[i].value === el.value){ text += el[i].innerHTML; }
		}

		var warning = 0;
		for (var w=0; w < warning_list.length; w++) {

			if(warning_list[w] === el.value){ warning = 1; }
		}

		var request = 0;
		for (var r=0; r < request_list.length; r++) {

			if(request_list[r] === el.value){ request = 1;}
		}

		if(name !== "")
		{
			document.getElementById(id).name = name;
			document.getElementById(id).value = el.value;
		}

		form.action.value = action;

		if( el.nodeName === "SELECT"){ el[0].selected='selected'; }

		if((warning === 1) && (request === 0)){

			this.open_confirm(id_win, text);
		}
		else{

			ms.RF('', '', '', form, request);
		}
	},

	AT: function(action, id_win, id, name, el, form, text, warning_list, request_list, id_tr, war)
	{
		if((id_tr) && (document.getElementById(id_tr).value === ""))
		{
			this.open_alert(war);

			if( el.nodeName === "SELECT"){ el[0].selected='selected'; }
		}
		else
		{
			for (var i=0; i < el.length; i++) {

				if(el[i].value === el.value){ text = el[i].innerHTML+text; }
			}

			var warning = 0;
			for (var w=0; w < warning_list.length; w++) {

				if(warning_list[w] === action){ warning = 1; }
			}

			if(name !== "")	{

				document.getElementById(id).name = name;
				document.getElementById(id).value = el.value;
			}

			form.action.value = action;

			if( el.nodeName === "SELECT"){ el[0].selected='selected'; }

			if((warning === 1)){

				this.open_confirm(id_win, text);
			}
			else{

				ms.RF('', '', '', form, 0);
			}
		}
	},

	AV: function(action, db, table, form, request, id, war)
	{
		if(document.getElementById(id).value === ""){

			this.open_alert(war);
		}
		else{

			this.RF(action, db, table, form, 0);
		}
	},

	open_alert: function(text)
	{
		document.getElementById("id_alt_message_text").innerHTML = text;
		document.getElementById("id_alt_message").style.display = "";
	},

	open_confirm: function(id_win, text)
	{
		document.getElementById(id_win+"_text").innerHTML = text;
		document.getElementById(id_win).style.display = "";
	},

	get_stp: function(id, ts, wr)
	{
		var data = "";
		var div = document.getElementById(ts);
		var sl = div.getElementsByTagName('*')

		for(var i=0;i<sl.length;i++){

			if(sl[i].type === "checkbox"){

				if(sl[i].checked){

					if(data === ""){data += sl[i].value;}
					else{data += ","+sl[i].value }
				}
			}

			if(sl[i].type === "radio"){

				if(sl[i].checked){

					data = sl[i].value;
				}
			}
		}

		var t = document.getElementById(id);
		t.value = data;

		this.check_change(t);

		document.getElementById(wr).style.display = "none";
	},

	check_sl: function(form, cbName, checked)
	{
		if(form[cbName].length){

			for (var i=0; i < form[cbName].length; i++) {

				form[cbName][i].checked = checked;
			}
		}
		else{ form[cbName].checked = checked; }
	},


	check_change: function(el)
	{
		el.style.background = "#ffeab4";
		el.style.color = "#555555";
	},


	set_vl: function(id, vl){

		document.getElementById(id).value = vl;
	},

	"com": "",

	el_open_com: function(id)
	{
		if(this.com !== ""){

			if(document.getElementById(this.com)){

				document.getElementById(this.com).style.display = "none";
			}
		}

		this.com = id;

		document.getElementById(id).style.display = "block";
	},

	view_wr: function(id, id_close)
	{
		document.getElementById(id_close).style.display = "none";

		if(document.getElementById(id).style.display === "none"){

			document.getElementById(id).style.display = "";
		}
		else{

			document.getElementById(id).style.display = "none";
		}

		window.scrollTo(0,0);
	},

	alt_list: function(oForm, cbName, id, display, title)
	{
		if(oForm[cbName])
		{
			if(this.check_al(oForm, cbName)){

				this.alt(id, display);
			}
			else{

				document.getElementById("id_alt_message_text").innerText = title;
				document.getElementById("id_alt_message").style.display = "";
			}
		}
	},

	el_view: function(id, display)
	{
		document.getElementById(id).style.display = display;
	},

	view_text: function(id)
	{
		var h = document.getElementById(id).style.height;

		if(h === "350px"){document.getElementById(id).style.height = 50+"px";}
		else{document.getElementById(id).style.height = 350+"px";}
	},


};


</script>



<style type="text/css">

body{background: #555555; color: #eee; }

.message{ color: #f70;  }

.result{ color: #eee; }

.nav_main_back{ background:#333;  border-bottom: 2px solid #555;}
.nav_main{ background: none;}

.nav_wrap, .nav_wrap_filter{
background: #333;
}

.nav_value{
background:#333; border: 0px; color: #eee;
}

.btn, .btn_text{
background: #333;
border: 1px solid #777;
color: #eee;
}

.btn_record{
background: #f70;
border: 1px solid #777;
color: #eee;
}

.slc{
background:#333;
border: 0px; color: #eee;
}

.int, .int_pass{ background: none; border: 1px solid #777; color: #eee;}

.ct_row{
background: #333;
}


.ct_pre,
.ct_name,
.ct_name_title,
.ct_info_A,
.ct_info_B,
.ct_info_D{
background: #555;
border: 1px solid #777;
color: #EEE;
}

.ct_name{ cursor: pointer; }
.ct_pre, .ct_name_title{ background: #333; color: #999; border: 0px;}

.rt_label_key,
.rt_label_name,
.rt_label_type,
.rt_select_type,
.rt_value_input,
.rt_value_input_disabled,
.rt_value_text,
.st_value_field,
.st_value_table,
.st_select_value,
.st_fn_value{
background: #555;
border: 1px solid #777;
color: #eee;
}

.st_select_value{
border: 0px;
background: #333;
outline: none;
}

.st_btn, .std_btn{
background: #333;
border: 1px solid #333;
color: #eee;
}

.rt_label_key,
.rt_label_name,
.rt_label_type{ background: #333; }
.rt_select_type{ outline: none; cursor: pointer; }
.rt_value_input_disabled{ background:#333; }
.rt_value_text{	margin-bottom:0px;}

.type_value{
background: #333;
color: #eee;
}

#script_text{
background: #555;
border: 1px solid #777;
color: #eee;
}

#status{ color: #eee; border: 1px solid #777; }

.wr_main_nav{
border-top: 7px solid #555;
border-bottom: 3px solid #555;
}



.altDialog{ background: #333; }
.altDialog_text{ color: #f70; }
.altDialog_btn{
background: #333;
border: 1px solid #555;
color: #eee;
}

.confirmDialog_title{
color: #eee;
font-size: 27px;
border-bottom: 1px solid #999;
}

.confirmDialog_text{
color: #f70;
font-size: 15px;
}

.confirmDialog_btn{
background: #333;
border: 1px solid #555;
color: #eee;
}

.confirmDialog {
font-family: Arial, Helvetica, sans-serif;
background: rgba(0,0,0,0.7);
}

.confirmDialog > div {
font-size: 21px;
background: #333;
border: 1px solid #555;
}


html{height:100%;}

body{
font-family: Arial,Helvetica,sans-serif;
font-size: 12px;
height:100%;
}

.app{font-size: 21px;}

option{font-size: 14px;}

.separator0,
.separator3,
.separator11,
.separator21{
clear:both;
margin: 0px;
}

.separator0{padding: 0px;}
.separator3{padding: 3px;}
.separator11{padding: 11px;}
.separator21{padding: 21px;}

.res{
overflow: auto;
max-height: 300px;
}

.message{
font-size: 14px;
padding-bottom: 11px;
}

.result{
font-size: 14px;
padding-bottom: 11px;
}

.nav_main_back{
z-index: 201;
position:fixed;
top: 0;
left: 0;
width: 100%;
height: 47px;
}

.nav_main{
z-index: 301;
position:fixed;
top: 0;
width: 970px;
padding-top: 5px;
margin: 0px;
}

.nav_main_form{
display: inline-block;
}

.nav{
text-align: left;
width: 971px;
padding: 0px;
margin: 0px;
}


.nav_wrap,
.nav_wrap_filter{
margin-right: 2px;
display:inline-block;
}

.nav_wrap{
width: 129px;
}

.nav_wrap_filter{
width: 445px;
}

.nav_value{
width: 173px;
padding: 7px;
margin: 3px 0px 2px 2px;
}

.nav_label{
width: 119px;
padding: 7px;
margin: 2px;
border: 0px;
display:inline-block;
}

.btn, .btn_record{
width: 147px;
height: 33px;
padding: 5px;
margin: 2px 2px 2px 0px;
}

.btn_text{
width: 546px;
height: 33px;
padding: 5px;
margin: 2px 0px 2px 0px;
text-align: right;
}

.slc{
width: 125px;
padding: 7px;
margin: 3px 0px 2px 2px;
outline: none;
}

.int{
width: 211px;
padding: 7px;
margin: 3px 3px 2px 2px;
}

.int_pass{
width: 211px;
padding: 7px;
margin: 3px 3px 2px 0px;
}

.ct_row{
margin: 0px;
padding: 0px;
}

.ct_check{
margin: 9px;
padding: 0px;
}

.ct_pre,
.ct_name,
.ct_name_title,
.ct_info_A,
.ct_info_B,
.ct_info_D{
padding: 7px;
margin-top: 2px;
margin-left: 2px;
}

.ct_pre{ width: 17px; }
.ct_name{ width: 618px; }
.ct_name_title{ width: 618px; }
.ct_info_A{ width: 85px; }
.ct_info_D{ width: 179px; }


.st_btn{
width: 151px;
height: 33px;
padding: 5px;
margin-right: 2px;
}

.std_btn{
width: 151px;
height: 33px;
padding: 5px;
margin-right: 2px;
text-align: left;
padding-left: 12px;
}

.run_tr{cursor: pointer;}

.pl_el{width: 973px;}

.rt_label_key,
.rt_label_name,
.rt_label_type,
.rt_select_type,
.rt_value_input,
.rt_value_input_disabled,
.rt_value_text,
.st_value_field,
.st_value_table,
.st_select_value,
.st_fn_value{
padding: 8px;
margin: 1px 2px 1px 0px;
}

.rt_label_key{ width: 27px; }
.rt_label_name{ width: 188px; }
.rt_label_type{ width: 149px; }
.rt_select_type{ width: 149px; }
.rt_value_input{ width: 528px; margin-right: 0px;}
.rt_value_input_disabled{ width: 528px; margin-right: 0px;}
.rt_value_text{	width: 952px; height: 50px; resize: vertical; }
.st_value_field{ width: 493px; }
.st_value_table{ width: 799px; }
.st_select_value{ width: 151px; }
.st_fn_value{ width: 646px; }


.type_value{
z-index: 101;
position: absolute;
width: 299px;
padding: 3px 0px 0px 3px;
margin-top: 2px;
}

.type_value_sl{
display: block;
padding: 9px;
max-height: 99px;
overflow: auto;
width: 278px;
}

.type_value_sl_k{
display:inline-block;
white-space: nowrap;
}

#script_text{
width: 958px;
height: 190px;
resize: vertical;
padding: 5px;
margin: 3px 0px 0px 0px;
}

#status{
z-index: 505;
position:fixed;
top:0;
font-size: 12px;
text-align: left;
display:inline-block;
padding: 8px;
margin-top: 7px;
width: 39px;
height: 15px;
background: none;
text-align: center;
}

#status_back{
z-index: 501;
position: fixed;
top: 0;
right: 0;
bottom: 0;
left: 0;
}

.page{
text-align: center;
width:100%;
}

.block{
width:970px;
display:inline-block;
text-align: left;
padding: 0px 59px 0px 59px;
}

.wr_main_nav{
z-index: 191;
width: 970px;
}




.altDialog{
z-index: 1101;
position:fixed;
top: 0px;
width: 970px;
padding-top: 5px;
margin: 0px;
}

.altDialog_text{
padding: 11px;
font-size: 15px;
display: inline-block;
}

.altDialog_btn{
width: 127px;
height: 33px;
padding: 5px;
margin: 3px 2px 2px 0px;
float: right;
}

.confirmDialog_title{
padding: 7px;
margin : 7px;
}

.confirmDialog_text{
padding: 7px;
margin : 7px;
}

.confirmDialog_btn{
width: 127px;
height: 33px;
padding: 5px;
margin: 3px 2px 2px 0px;
}

.confirmDialog {
position: fixed;
top: 0;
right: 0;
bottom: 0;
left: 0;
z-index: 99999;
}

.confirmDialog > div {
width: 700px;
position: relative;
margin: 10% auto;
padding: 11px;
}


</style>


</head>
<body onload="ms.pst('session=1');">

<?php Control::storage(); ?>

<div id="expl_page" class="page">
	<input id="status" type="text" value="" style="display: none;">
	<div id="status_back" style="display: none;"></div>
	<div class="block" id="content"></div>
</div>



<!--[if lt IE 11]>
<script type="text/javascript">
document.getElementById("expl_page").innerHTML = "<h2><?php echo _MESSAGE_SUPPORT; ?></h2>";
</script>
<![endif]-->



</body>
</html>