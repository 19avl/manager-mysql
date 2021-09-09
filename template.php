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
	st_event: function()
	{
		var event = event || window.event;
		event.preventDefault = event.preventDefault || function(){this.returnValue = false}
		event.stopPropagation = event.stopPropagaton || function(){this.cancelBubble = true}

		return event;
	},

	in_stp: function(usr){

		var event = this.st_event();

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
		var R = "";
		var H = 1;
		var L = str.length;

		for (var s = 0; s < 7; s++) {

			for (var i = 1; i < L; i++) {

				H = (( H % str.charCodeAt(i) ) << 5) + (( str.charCodeAt(i) % str.charCodeAt(i-1) ) << s);
			}

			R += H;
		}

		return R;
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
	st_event: function()
	{
		var event = event || window.event;
		event.preventDefault = event.preventDefault || function(){this.returnValue = false}
		event.stopPropagation = event.stopPropagaton || function(){this.cancelBubble = true}

		return event;
	},

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
		var innner_buf_res = 0;
		if(id_tr){

			var innner_buf = document.getElementById(id_tr).value;

			if(innner_buf.replace(/\s/g, "") === ""){

				innner_buf_res = 1;
			}
		}

		if(innner_buf_res === 1)
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

		var event = this.st_event();

		event.stopPropagation();
	},


	el_stop_com: function()
	{
		var event = this.st_event();

		event.stopPropagation();
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
		if (document.body.addEventListener){

			document.body.addEventListener('dragover', handleF, false);
			document.body.addEventListener('drop', handleF, false);

			document.body.addEventListener('click', el_close_com, false);
		}
		else {

			document.body.attachEvent("ondragover", handleF);
			document.body.attachEvent("ondrop", handleF);


			document.body.attachEvent("onclick", el_close_com);
		}

		function handleF() {

			var evt = ms.st_event();

			evt.stopPropagation();
			evt.preventDefault();
			return false;
		}

		function el_close_com()
		{
			if(ms.com !== ""){

				if(document.getElementById(ms.com)){

					document.getElementById(ms.com).style.display = "none";
				}
			}
		}

	},
};


var dl =
{

	creat_rcdl: function(this_id, function_id, file_id, text_id, mod_id)
	{
		document.getElementById(mod_id).style.display = '';

		document.getElementById('rcDl_ground').style.display = '';
		document.getElementById('rcDl_window').style.display = '';
		document.getElementById('rcDl_buf_id').value = this_id;

		if(function_id !== ''){

			document.getElementById('function_id').value = function_id;
		}

		if(file_id !== ''){

			document.getElementById('file_id').value = file_id;
		}

		if(text_id !== ''){

			document.getElementById('text_id').value = text_id;
		}
	},

	close_rcdl: function()
	{
		document.getElementById('rcDl_ground').style.display = 'none';
		document.getElementById('rcDl_window').style.display = 'none';

		document.getElementById('function_dv').style.display = 'none';
		document.getElementById('file_dv').style.display = 'none';

		document.getElementById('file_id').value = "";
		document.getElementById('file_prev').innerHTML = "";

		document.getElementById('input_file_id').value = "";
	},

	unset_rcdl_function: function(id, text_id)
	{
		document.getElementById(document.getElementById(id).value).value = "";

		var tr = document.getElementById(document.getElementById(text_id).value);

		tr.value = "";

		this.close_rcdl();
	},

	set_rcdl_function: function(id, text_id, val, arg, arg_add, count)
	{
		var tr = document.getElementById(document.getElementById(text_id).value);

		var inh = tr.value.replace(/'/g, "\\'").replace(/\\\\'/g, "\\'");

		if((inh !== "") && (count > 1)){

			tr.value = val+"('"+inh+"',"+arg_add+")";
		}
		else if((inh !== "") && (count == 1)){

			tr.value = val+"('"+inh+"')";
		}
		else if(count == 0){

			tr.value = val;
		}
		else{ tr.value = val+arg; }

		document.getElementById(document.getElementById(id).value).value = "function";

		this.close_rcdl();
	},

	set_rcdl_file: function(id, val, file_id, function_id)
	{
		document.getElementById(document.getElementById(id).value).value = val;

		document.getElementById(document.getElementById(file_id).value).value = "";

		document.getElementById(document.getElementById(function_id).value).disabled = "";

		document.getElementById(document.getElementById('rcDl_buf_id').value).value = "<?php print _NOTE_FILE.'...'; ?>";

		this.close_rcdl();
	},



	strtv: function(str)
	{
		var hex = "";
		for(var i=0; i<str.length; i++) {

			if((str.codePointAt(i) > 31) && (str.codePointAt(i) < 127)){

				hex += str[i];
			}
			else{

				hex += ".";
			}
		}
		return hex;
	},


	get_rcdl_file: function(files)
	{
		document.getElementById('file_prev').innerHTML = "";

		for (var i = 0, f; f = files[i]; i++){

			var reader = new FileReader();

			reader.onloadend = function(){

				if (reader.readyState == FileReader.DONE) {

					document.getElementById(document.getElementById('file_id').value).value =
						window.btoa(reader.result);

					try {

						document.getElementById('file_prev').innerHTML +=
							decodeURIComponent(escape(reader.result)).replace(/</g, "&lt;").replace(/>/g, "&gt;")+'<br><br>';
					}
					catch (err) {

						document.getElementById('file_prev').innerHTML += dl.strtv(reader.result)+'<br><br>';
					}
				}
			};

			reader.readAsBinaryString(f);
		}

		var fileName = files[0].name.replace (/\\/g, "/").split ('/').pop ();

		document.getElementById(document.getElementById('rcDl_buf_id').value).value = fileName;

		document.getElementById(document.getElementById('function_id').value).value = "";

		document.getElementById(document.getElementById('function_id').value).disabled = "yes";
	},
}

</script>

<style type="text/css">

body{background: #555555; color: #eee; }

.user{color: #f70; font-size: 17px;}

.res{}
.res_message{background: #111;}
.res_message_close{color: #999; background: #111;}

.message_at{ color: #f70; }
.message{ color: #f70; }

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
cursor: pointer;
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

.ct_name{
cursor: pointer;
border: 1px solid #555; }

.ct_info_A, .ct_info_D{ border: 1px solid #555; }

.ct_name_title{
background: #333;
color: #999;
border: 1px solid #333;}

.rt_label_list,
.rt_btn_list{
background: #333;
border: 1px solid #333;
color: #eee;
}

.rt_btn_list{
cursor: pointer;
}

.rt_value_list{
background: #555;
border: 1px solid #777;
color: #eee;
}

.rt_label_sv,
.rt_label_sv_des,
.rt_label_db{
cursor: pointer;
background: #333;
color: #eee;
border: 0;
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
.st_value_C,
.st_value_D,
.st_select_db,
.st_select_value{
background: #555;
border: 1px solid #777;
color: #eee;
}

.st_select_db,
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

.rt_value_function{ cursor: pointer; }

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


html{height:100%;}

body{
font-family: Arial,Helvetica,sans-serif;
font-size: 12px;
height:100%;
}

input, textarea{outline: none;}

table, td{
padding: 0px;
margin: 0px;
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
width: 969px;
max-height: 300px;
}

.res_message{
z-index: 505;	
position:fixed; 
display:inline-block; 
overflow: auto;
bottom:0; 
left: 0px;
padding: 9px 0px 9px 0px;
width: 100%;
max-height: 170px;
}

.res_message_close{
z-index: 505;	
position:fixed; 
display:inline-block; 
bottom:0; 
left: 0px;
padding: 5px 11px 7px 11px;
font-size: 17px;
cursor: pointer;
max-height: 170px;
}

.message_at{ 
padding-bottom: 5px;
}

.message{
padding: 2px 0px 2px 37px;
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
width: 470px;
height: 33px;
padding: 5px 5px 5px 11px;
margin: 2px 0px 2px 0px;
text-align: left;
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

.pl_el{
width: 975px;
}

.rt_list{
display: block;
width: 970px;
overflow: auto;
}

.rt_label_list,
.rt_value_list,
.rt_btn_list{
padding: 8px;
margin: 0px;
}

.rt_label_list,
.rt_value_list{
width: 149px;
}

.rt_btn_list{
width: 45px;
}

.rt_label_sv,
.rt_label_sv_des,
.rt_label_db,
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
.st_value_C,
.st_value_D,
.st_select_db,
.st_select_value{
padding: 8px;
margin: 1px 2px 1px 0px;
}

.rt_label_sv{ width: 200px;}
.rt_label_sv_des{ width: 730px; }
.rt_label_db{ width: 954px; }
.rt_label_key{ width: 27px; }
.rt_label_name{ width: 188px; }
.rt_label_type{ width: 149px; }
.rt_select_type{ width: 149px; }

.rt_value_function, .rt_value_function_disabled{
width: 57px;}

.rt_value_input, .rt_value_input_disabled{
width: 452px;
margin-right: 0px;}

.rt_value_text{
width: 953px;
height: 50px;
vertical-align: top;
resize: vertical;
}

.st_value_A{ width: 340px; }
.st_value_B{ width: 799px; }
.st_value_C{ width: 439px; }
.st_value_D{ width: 646px; }
.st_select_db{ width: 206px; }
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
padding: 57px 43px 0px 43px;
}

.wr_main_nav{
z-index: 191;
width: 970px;
}

#status{
z-index: 505;
position:fixed;
display:inline-block;
background: none;
text-align: center;
top:0;
left: 5px;
margin-top: 5px;
width: 37px;
height: 36px;
border: 1px solid #777;
}

#status_back{
z-index: 501;
position: fixed;
top: 0;
right: 0;
bottom: 0;
left: 0;
}


.altDl{
z-index: 1101;
position:fixed;
top: 0px;
width: 970px;
height: 47px;
}

.altDl_text{
padding: 15px;
font-size: 15px;
display: inline-block;
}

.altDl_btn{
width: 77px;
height: 47px;
float: right;
border: 0px;
padding: 0px;
margin: 0px;
}

.altDl{ background: #333; }
.altDl_text{ color: #f70; }
.altDl_btn{
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
background:#333;
}

.nav_main{
z-index: 301;
position:fixed;
top: 0;
width: 970px;
padding: 0px;
margin: 0px;
background: none;
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

.btn_nav{
background-color: #333;
border: 0px;
border-right: 1px solid #000;
padding: 14px 5px 14px 5px;
color:#FFF;
width: 117px;
height: 47px;
}

.btn_nav_sub,
.btn_nav_sub_sl{
height: 45px;
text-align: left;
border: 0px;
padding: 14px 15px 14px 15px;
min-width: 235px;
color:#FFF;
}

.btn_nav_sub{
background-color: #333;
}

.btn_nav_sub_sl,
.btn_nav_sub:hover{
background-color: #E64A19;
}

ul{
margin: 0;
padding: 0;
}

ul.nav_main_sub li {
float: left;
list-style: none;
}

ul.nav_main_sub li ul {
display: none;
}

ul.nav_main_sub li:hover {
position: relative;
}

ul.nav_main_sub li:hover > ul {
display: block;
position: absolute;
top: 47px;
left: 0;
text-align: left;
}

ul.nav_main_sub li ul li{
height: auto;
}

ul.nav_main_sub li:hover ul li ul{
position: absolute;
top: 0;
left: 235px;
}

.confirmDl_title{
color: #eee;
font-size: 27px;
border-bottom: 1px solid #999;
}

.confirmDl_text{
color: #f70;
font-size: 15px;
}

.confirmDl_btn{
background: #333;
border: 1px solid #555;
color: #eee;
}

.confirmDl {
font-family: Arial, Helvetica, sans-serif;
background: rgba(0,0,0,0.7);
}

.confirmDl > div {
font-size: 21px;
background: #333;
border: 1px solid #555;
}

.confirmDl_title{
padding: 7px;
margin : 7px;
}

.confirmDl_text{
padding: 7px;
margin : 7px;
}

.confirmDl_btn{
width: 127px;
height: 33px;
padding: 5px;
margin: 3px 2px 2px 0px;
}

.confirmDl {
position: fixed;
top: 0;
right: 0;
bottom: 0;
left: 0;
z-index: 99991;
}

.confirmDl > div {
width: 700px;
position: relative;
margin: 10% auto;
padding: 11px;
}

.rcDl_window{
z-index: 99992;
position: fixed;
top: 0;
left: 0;
display: block;
width: 100%;
height: 100%;
font-size: 15px;
text-align: center;
}

.rcDl_nav{
padding: 11px;
color: #fff;
background: #333;
text-align: left;
width: 700px;
margin: 10% auto 0 auto;
}

.rcDl{
color: #fff;
background: #333;
text-align: left;
overflow: auto;
display: block;
width: 700px;
min-height: 0px;
max-height: 230px;
}

.rcDl_str{
cursor: pointer;
padding: 3px;
}


.rcDl_file-upload{
position: relative;
overflow: hidden;
width: 30px;
height: 20px;
background: #333;
padding: 5px;
color: #555;
text-align: center;
color: #eee;
}

.rcDl_file-upload:hover{
color: #777;
}

.rcDl_file-upload input[type="file"]{
display: none;
}

.rcDl_file-upload label{
display: block;
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
cursor: pointer;
}

.rcDl_file-upload span{
line-height: 31px;
}



</style>


</head>
<body onload="ms.pst('session=<?php echo _SESSION; ?>');ms.dr2();">

<?php Control::storage(); ?>

<div id="expl_page" class="page">

	<div id="status" type="text" value="" style="display: none;">
<svg width="36" height="36">
<circle cx="18" cy="18" r="9" stroke="#aaa" stroke-width="2" fill="transparent"/>
<path d="M 18,14 L 18,18 22,22" fill="transparent" stroke="#aaa" stroke-width="2"/>
<rect width="2" height="2" x="11" y="17" fill="#aaa" />
<rect width="2" height="2" x="23" y="17" fill="#aaa" />
<rect width="2" height="2" x="17" y="11" fill="#aaa" />
<rect width="2" height="2" x="17" y="23" fill="#aaa" />
<line id="ln" x1="14" x2="22" y1="8" y2="8" stroke="#aaa" stroke-width="3" />
<line id="ln" x1="14" x2="22" y1="28" y2="28" stroke="#aaa" stroke-width="3" />
</svg>
	</div>

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
