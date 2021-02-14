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
	in_stp: function(event, usr){

		if(event.keyCode === 13){

			this.get_ps(usr);
			event.preventDefault();
		}
	},

	get_ps: function(){

		var pass = document.getElementById("en_pass").value;
		var result = pass.replace(/^\s+/, '').replace(/\s+$/, '');

		document.getElementById("pass").innerHTML = this.hashE(result);

		ms.pst("session=<?php echo _SESSION; ?>"+"&request="+this.set_ps());
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

					if(document.getElementById("div_message") ||
						document.getElementById("div_error") || document.getElementById("div_result")){

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


	RF: function(action, db, table, form, request)
	{
		if(action !== ""){form.action.value=action;}

		if(db !== ""){form.db.value=db;}
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

		if(request === 1)
		{
			if(form["request"])
			{
				form["request"].value = ct.set_ps(oForm);
			}

			if(form["session"])
			{
				form["session"].value = "<?php echo _SESSION; ?>";
			}

			form.submit();
		}
		else
		{
			if(form["request"])
			{
				data += "request=" + ct.set_ps(oForm);
			}

			if(form["session"])
			{
				data += "&session=<?php echo _SESSION; ?>";
			}

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

			this.RF(action, db, table, form, request);
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
		else{ form[cbName].checked = checked;

		}

		form['totalH'].checked = checked;
		form['totalF'].checked = checked;
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

		this.rdf();
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

	el_va: function(id, display)
	{
			document.getElementById(id).style.display = display;
	},

	el_vb: function(id)
	{
		if(document.getElementById(id).style.display === "none"){

			document.getElementById(id).style.display = "";
		}
		else{

			document.getElementById(id).style.display = "none";
		}
	},

	sub_tb: function(id, el)
	{
		document.getElementById(id).value = el.value;
		el[0].selected='selected';
	},

	get_sub: function(id, name)
	{
		var a = "";
		var b = "";

		if(id !== ""){

			var a = document.getElementById(id).value;
		}

		if(name !== ""){var b = " / "+document.getElementById(name).innerText;}

		return a+b;
	},

	view_text: function(id)
	{
		var h = document.getElementById(id).style.height;

		if(h === "350px"){document.getElementById(id).style.height = 50+"px";}
		else{document.getElementById(id).style.height = 350+"px";}
	},

	dr2: function()
	{
		document.body.addEventListener('dragover', handleF, false);
		document.body.addEventListener('drop', handleF, false);

		function handleF(evt) {

			evt.stopPropagation();
			evt.preventDefault();
			return false;
		}
	},

	rdf: function()
	{
		var script_text = "";

		if (window.File && window.FileReader && window.FileList && window.Blob &&
			(script_text = document.getElementById('script_text'))) {

			script_text.addEventListener('dragover', handleDragOver, false);
			script_text.addEventListener('drop', handleFileDrag, false);
		}

		function handleFileDrag(evt) {

			evt.stopPropagation();
			evt.preventDefault();

			var files = evt.dataTransfer.files;
			var file = files[0];
			var reader = new FileReader();

			reader.onloadend = function(evt) {

				if (evt.target.readyState == FileReader.DONE){

					document.getElementById("script_text").innerHTML =
						decodeURIComponent(escape(evt.target.result) );
				}
			};

			reader.readAsBinaryString(file);
		}

		function handleDragOver(evt) {

			evt.stopPropagation();
			evt.preventDefault();
			evt.dataTransfer.dropEffect = 'copy';
		}
	},
};

</script>

<style type="text/css">

body{background: #555555; color: #eee; }

.user{color: #f70; font-size: 17px;}

.message{ color: #f70;  }

.result{ color: #eee; }

.nav_wrap, .nav_wrap_filter{
background: #333;
}

.nav_value{
background:#333;
border: 0px;
color: #eee;
}

.btn, .btn_text{
background: #333;
border: 1px solid #333;
color: #eee;
}

.blc{
background: none;
color: #eee;
border: 2px solid #333;
}

.slc{
background:#333;
color: #eee;
border: 1px solid #333;
}

.int, .int_pass{
background: none;
border: 1px solid #777;
color: #eee;}

.ct_row{
background: #333;
}

.ct_name,
.ct_name_title,
.ct_info_A,
.ct_info_B,
.ct_info_D{
background: #555;
border: 1px solid #777;
color: #EEE;
}

.ct_name{ cursor: pointer; border: 1px solid #555; }
.ct_info_A, .ct_info_D{ border: 1px solid #555; }
.ct_name_title{
background: #333;
color: #999;
border: 1px solid #333;}

.rt_label_sv{
border: 1px;
background: #333;
color: #eee;
cursor: pointer;
}

.rt_label_key,
.rt_label_name,
.rt_label_type,

.rt_value_function_disabled,
.rt_value_function,
.rt_value_input,
.rt_value_input_disabled,

.rt_value_text,
.rt_select_type,
.st_value_A,
.st_value_B,
.st_value_D,
.st_select_value{
background: #555;
border: 1px solid #777;
color: #eee;
}

.st_select_value{
border: 0px;
background: #333;
outline: none;
}

.st_btn{
background: #333;
border: 1px solid #333;
color: #eee;
}

.rt_label_key,
.rt_label_name,
.rt_label_type{
background: #333;
border: 1px solid #333; }

.rt_value_function{
cursor: pointer; }

.rt_value_function_disabled{
background:#333;
border: 1px solid #333; }

.rt_value_input_disabled{
background:#333;
border: 1px solid #333; }

.rt_select_type{
outline: none;
cursor: pointer; }

.type_value{
background: #333;
color: #eee;
}

#script_text{
background: #555;
border: 1px solid #777;
color: #eee;
}

.wr_main_nav{
border-top: 7px solid #555;
border-bottom: 3px solid #555;
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

input, textarea{outline: none;}

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
width: 969px;
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

.nav{
text-align: left;
width: 973px;
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
width: 446px;
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

.btn{
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

.blc, .slc{
width: 125px;
padding: 7px;
margin: 3px 2px 2px 0px;
outline: none;
text-align: left;
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

.ct_name,
.ct_name_title,
.ct_info_A,
.ct_info_B,
.ct_info_D{
padding: 7px;
margin-top: 2px;
margin-left: 2px;
}

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

.pl_el{width: 973px;}

.rt_label_sv,
.rt_label_key,
.rt_label_name,
.rt_label_type,
.rt_value_function,
.rt_value_function_disabled,
.rt_value_input,
.rt_value_input_disabled,
.rt_value_text,
.rt_select_type,
.st_value_A,
.st_value_B,
.st_value_D,
.st_select_value{
padding: 8px;
margin: 1px 2px 1px 0px;
}

.rt_label_sv{ width: 954px; }
.rt_label_key{ width: 27px; }
.rt_label_name{ width: 188px; }
.rt_label_type{ width: 149px; }
.rt_select_type{ width: 149px; }

.rt_value_function, .rt_value_function_disabled{ width: 57px;}

.rt_value_input, .rt_value_input_disabled{
width: 528px;
margin-right: 0px;}

.rt_value_text{
width: 953px;
height: 50px;
vertical-align: top;
resize: vertical;
}

.st_value_A{ width: 340px; }
.st_value_B{ width: 799px; }
.st_value_D{ width: 646px; }
.st_select_value{ width: 151px; }

.type_value{
z-index: 101;
position: absolute;
width: 299px;
padding: 3px 0px 0px 3px;
margin: 3px 2px 2px 0px;
}

.type_value_sl{
display: block;
padding: 9px;
max-height: 210px;
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

.page{
text-align: center;
width:100%;
}

.block{
width:970px;
display:inline-block;
text-align: left;
padding: 57px 59px 0px 59px;
}

.wr_main_nav{
z-index: 191;
width: 970px;
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




#status{
z-index: 505;
position:fixed;
top:0;
left: 5px;
font-size: 12px;
display:inline-block;
padding: 8px;
margin-top: 3px;
width: 39px;
height: 23px;
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

#status{
color: #aaa;
border: 1px solid #777;
}



.altDialog{
z-index: 1101;
position:fixed;
top: 0px;
width: 970px;
height: 47px;
}

.altDialog_text{
padding: 15px;
font-size: 15px;
display: inline-block;
}

.altDialog_btn{
width: 77px;
height: 47px;
float: right;
border: 0px;
padding: 0px;
margin: 0px;
}

.altDialog{ background: #333; }
.altDialog_text{ color: #f70; }
.altDialog_btn{
background: #E64A19;
color: #eee;
}



.nav_main_back{
z-index: 201;
position:fixed;
top: 0;
left: 0;
width: 100%;
height: 47px;
}

.nav_main_back{
background:#333;
}

.nav_main{ background: none;}

.nav_main{
z-index: 301;
position:fixed;
top: 0;
width: 970px;
padding: 0px;
margin: 0px;
}

.nav_main_form{
display: inline-block;
}


.btn_nav_first{
width: 47px;
height: 47px;
border: 0px;
padding: 1px;
background-color: #E64A19;
border-right: 1px solid #000;
color:#FFF;
}

.btn_nav,
.btn_nav_sub,
.btn_nav_sub_sl{
border: 0px;
padding: 14px 5px 14px 5px;
color:#FFF;
}

.btn_nav{
background-color: #333;
width: 117px;
height: 47px;
}

.btn_nav_sub{
background-color: #252525;
width: 210px;
height: 45px;
text-align: left;
padding-left: 21px;
}

.btn_nav_sub_sl{
background-color: #E64A19;
width: 210px;
height: 45px;
text-align: left;
padding-left: 21px;
}

.btn_nav:hover,
.btn_nav_sub:hover{
background-color: #E64A19;
position: relative;
}



ul{
margin: 0;
padding: 0;
}

ul.nav_main_sub li {
background-color: #333;
float: left;
height: 47px;
list-style: none;
text-align: center;
}


ul.nav_main_sub li ul {
display: none;
}

ul.nav_main_sub {
background-color: #333;
height: 47px;
}

ul.nav_main_sub li:hover {
background-color: #E64A19;
position: relative;
}

ul.nav_main_sub li:hover > ul {
display: block;
position: absolute;
top: 47px;
left: 0;
text-align: left;
width: 210px;
}

ul.nav_main_sub li ul li{
height: auto;
width: 210px;
}

ul.nav_main_sub li:hover ul li ul{
position: absolute;
top: 0;
left: 210px;
}


</style>

</head>
<body onload="ms.pst('session=<?php echo _SESSION; ?>');ms.dr2();">

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