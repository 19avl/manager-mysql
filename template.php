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
	in_stp: function(usr){

		var event = ms.st_event();

		if(event.keyCode === 13){

			this.get_ps(usr);
			event.preventDefault();
		}
	},

	get_ps: function(){

		var pass = document.getElementById("en_pass").value;
		var result = pass.replace(/^\s+/, '').replace(/\s+$/, '');

		document.getElementById("pass").innerHTML = this.sha1("<?php echo _SESSION; ?>"+result);

		ms.pst("session=<?php echo _SESSION; ?>"+"&request="+this.set_ps());
	},

	set_ps: function(oForm){

		if(document.getElementById("request")){

			var request = document.getElementById("request").value;
			var pass = document.getElementById("pass").innerHTML;
	
			return this.sha1(""+encodeURIComponent(request+pass)+this.str_request(oForm));
		}

		return "";
	},

	sha1: function(str)
	{
		str = unescape(encodeURIComponent(str));	

		var Hex = function (val) {
		
			var str = '';
			var i;

			for (i = 7; i >= 0; i--) {

				str += ((val >>> (i * 4)) & 0x0f).toString(16);
			}
		
			return str;
		}

		var s, i, j;
		var W = new Array(80);
		var H0 = 0x67452301;
		var H1 = 0xEFCDAB89;
		var H2 = 0x98BADCFE;
		var H3 = 0x10325476;
		var H4 = 0xC3D2E1F0;
		var A, B, C, D, E;
		var temp;
		var hash;
	
		var strLen = str.length;
		var wordArray = [];
  
		for (i = 0; i < strLen - 3; i += 4) {
		
			j = str.charCodeAt(i) << 24 | str.charCodeAt(i + 1) << 16 | str.charCodeAt(i + 2) << 8 | str.charCodeAt(i + 3);
			
			wordArray.push(j);
		}

		var sl = strLen % 4;	

		if(sl === 0){
	
			i = 0x080000000;
		}
		else if(sl === 1){
	
			i = str.charCodeAt(strLen - 1) << 24 | 0x0800000;
		}
		else if(sl === 2){
	
			i = str.charCodeAt(strLen - 2) << 24 | str.charCodeAt(strLen - 1) << 16 | 0x08000;
		}
		else if(sl === 3){
	
			i = str.charCodeAt(strLen - 3) << 24 |
				str.charCodeAt(strLen - 2) << 16 | str.charCodeAt(strLen - 1) << 8 | 0x80;
		}

		wordArray.push(i);
	
		while ((wordArray.length % 16) !== 14) {
			
			wordArray.push(0);
		}
	
		wordArray.push(strLen >>> 29);
		wordArray.push((strLen << 3) & 0x0ffffffff);
  
		for (s = 0; s < wordArray.length; s += 16) {
	  
			for (i = 0; i < 16; i++) {
			
				W[i] = wordArray[s + i];
			}
	
			for (i = 16; i <= 79; i++) {
			
				W[i] = ((W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16])<<1) | ((W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16])>>>(32-1));	
			}
	
			A = H0;
			B = H1;
			C = H2;
			D = H3;
			E = H4;
	
			for (i = 0; i <= 19; i++) {
			
				temp = (((A<<5) | (A>>>(32-5))) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
				E = D;
				D = C;
				C = (B<<30) | (B>>>(32-30));
				B = A;
				A = temp;
			}
	
			for (i = 20; i <= 39; i++) {
			
				temp = (((A<<5) | (A>>>(32-5))) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
				E = D;
				D = C;
				C = (B<<30) | (B>>>(32-30));
				B = A;
				A = temp;
			}
	
			for (i = 40; i <= 59; i++) {
			
				temp = (((A<<5) | (A>>>(32-5))) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
				E = D;
				D = C;
				C = (B<<30) | (B>>>(32-30));
				B = A;
				A = temp;
			}

			for (i = 60; i <= 79; i++) {
			
				temp = (((A<<5) | (A>>>(32-5))) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
				E = D;
				D = C;
				C = (B<<30) | (B>>>(32-30));
				B = A;
				A = temp;
			}
	
			H0 = (H0 + A) & 0x0ffffffff;
			H1 = (H1 + B) & 0x0ffffffff;
			H2 = (H2 + C) & 0x0ffffffff;
			H3 = (H3 + D) & 0x0ffffffff;
			H4 = (H4 + E) & 0x0ffffffff;
		}
  
		hash = Hex(H0) + Hex(H1) + Hex(H2) + Hex(H3) + Hex(H4);
	
		return hash.toLowerCase();
	},

	str_request: function(oForm)
	{
		var A = (<?php echo json_encode(Control::$CHECK); ?>).replace(/\s{1,}/g, "").split(",");

		var str_check = "";

		for(var i in A)
		{
			for(var c in oForm)
			{
				if ((c === A[i]) || (new RegExp('\^'+A[i]+'\\[')).test(c)){

					str_check += oForm[c];
				}
			}
		}

		return str_check.replace(/\&{1,}/g, "");
	},
};


var ms =
{
	pst: function(data)
	{
		var container = "content";
		var status = "status";

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
					document.getElementById(status).style.display = "none";

					if(document.getElementById("div_message") ||
						document.getElementById("div_error") || document.getElementById("div_result")){

						window.scrollTo(0,0);
					}

					ms.wdf();
					ms.rdf('script_text_sql');
				}
			}
			else{

				document.getElementById(status).style.display = "";
			}
		}

		X.open("POST", url, true);
		X.setRequestHeader("Max-Forwards", "0");
		X.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=utf-8");
		X.send(data);
	},


	RF: function(action, sh, table, form, request)
	{
		if(action !== ""){form.action.value=action;}

		if(sh !== ""){form.sh.value=sh;}
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

							if(oForm[form[i].name])	{

								oForm[form[i].name] += "&"+form[i].value;
							}
							else{

								oForm[form[i].name] = form[i].value;
							}
						}
					}
					else if(!form[i].disabled)
					{
						data += encodeURIComponent(form[i].name)+"="+encodeURIComponent(form[i].value)+"&";

						if(oForm[form[i].name])	{

							oForm[form[i].name] += "&"+form[i].value;
						}
						else{

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


	AL: function(action, id_win, el, form, cbName, text, warning_list, request_list, war)
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

		form.action.value = action;

		if( el.nodeName === "SELECT"){ el[0].selected='selected'; }

		if((warning === 1) && (request === 0)){

			this.open_confirm(id_win, text);
		}
		else{

			ms.RF('', '', '', form, request);
		}
	},


	AT: function(action, id_win, el, form, text, warning_list, request_list, id_tr, war)
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

				if(el[i].value === el.value){ text += el[i].innerHTML; }
			}

			var warning = 0;
			for (var w=0; w < warning_list.length; w++) {

				if(warning_list[w] === action){ warning = 1; }
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

	AV: function(action, sh, table, form, request, id, war)
	{
		if(document.getElementById(id).value === ""){

			this.open_alert(war);
		}
		else{

			this.RF(action, sh, table, form, request);
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


	view_wr: function(id, id_close)
	{
		if(id_close !== ""){
			
			document.getElementById(id_close).style.display = "none";
		}

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


	IRText: function(slID, objID, el)
	{
		var slt = document.getElementById(slID);
		var obj = document.getElementById(objID);

		var start = obj.selectionStart;
		var end = obj.selectionEnd;

		obj.focus();

		obj.value = obj.value.substr(0,start)+slt.value+obj.value.substr(end);

		obj.selectionStart = start;
		obj.selectionEnd = start+slt.value.length;

		el[0].selected='selected';
	},


	st_event: function()
	{
		var event = event || window.event;
		event.preventDefault = event.preventDefault || function(){this.returnValue = false}
		event.stopPropagation = event.stopPropagaton || function(){this.cancelBubble = true}

		return event;
	},


	rdf: function(script_text_id)
	{
		var script_text = "";
		
		document.body.addEventListener('dragover', handleDragOver, false);
		document.body.addEventListener('drop', handleDragOver, false);

		if (window.File && window.FileReader && window.FileList && window.Blob &&
			(script_text = document.getElementById(script_text_id))) {

			script_text.addEventListener('drop', handleFileDrag, false);
		}

		function handleFileDrag() {

			var evt = ms.st_event();

			evt.stopPropagation();
			evt.preventDefault();

			var files = evt.dataTransfer.files;

			ms.get_files(files, script_text_id);
		}

		function handleDragOver() {

			var evt = ms.st_event();

			evt.stopPropagation();
			evt.preventDefault();
			evt.dataTransfer.dropEffect = 'copy';
		}
	},


	get_files: function(files, script_text_id)
	{
		var str = "";

		for (var i = 0, f; f = files[i]; i++)
		{
			(function(e) {
				
				var reader = new FileReader();

				reader.onloadend = function(){
					
					if (reader.readyState == FileReader.DONE) 
					{
						str += "/* "+files[e].name+" */\r\n";

						try{ 

							str += decodeURIComponent(escape(reader.result))+"\r\n\r\n";
						}
						catch (CatchException){ 
			
							str += "/* ERROR */\r\n";
							str += reader.result+"\r\n\r\n";
						}

						if(e === (files["length"]-1)){

							document.getElementById(script_text_id).innerHTML = str;
						}
					}
				};

				reader.readAsBinaryString(f);

			})(i);
		}
	},


	get_rcul_file: function(files, tr, fn, pr, blob_ch_id, prev_name_id)
	{
		var name = "";
		var str = "";
		var prev = "";

		for (var i = 0, f; f = files[i]; i++)
		{
			(function(e) {

				var reader = new FileReader();

				reader.onloadend = function(){

					if (reader.readyState == FileReader.DONE) {

						name += files[e].name.replace (/\\/g, "/").split ('/').pop ()+"; ";

						str += reader.result;

						if(e === (files["length"]-1)){

							document.getElementById(fn).value = name;

							document.getElementById(tr).innerText = window.btoa(str);

							document.getElementById(pr).innerText = "";
							document.getElementById(pr).hidden = "hidden";

							document.getElementById(blob_ch_id).value = "2";

							document.getElementById(prev_name_id).style.display = "none";
						}
					}
				};

				reader.readAsBinaryString(f);

			})(i);
		}
	},


	reset_rcul_file: function(files_id, file_name_id, text_name_id, blob_ch_id, prev_name_id)
	{
		document.getElementById(files_id).innerText = "";

		document.getElementById(file_name_id).value = "";

		document.getElementById(text_name_id).innerText = "";
		document.getElementById(text_name_id).hidden = "none";

		document.getElementById(blob_ch_id).value = "2";

		document.getElementById(prev_name_id).style.display = "none";
	},


	get_rcul_text: function(files_id, file_name_id, text_name_id, blob_ch_id, prev_name_id)
	{
		document.getElementById(files_id).innerText = "";

		document.getElementById(file_name_id).value = "";

		document.getElementById(text_name_id).innerText = "";
		document.getElementById(text_name_id).hidden = "";

		document.getElementById(blob_ch_id).value = "2";

		document.getElementById(prev_name_id).style.display = "none";
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


	wdf: function()
	{
		document.body.addEventListener('click', el_close_com, false);

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
	creat_rcdl: function(this_id, text_id, mod_id, title)
	{
		document.getElementById(mod_id).style.display = '';

		document.getElementById('rcDl_ground').style.display = '';
		document.getElementById('rcDl_window').style.display = '';
		document.getElementById('rcDl_buf_id').value = this_id;
		document.getElementById('text_id').value = text_id;
		document.getElementById('note_function').innerText = title;	
		
	},

	close_rcdl: function()
	{
		document.getElementById('rcDl_ground').style.display = 'none';
		document.getElementById('rcDl_window').style.display = 'none';
		document.getElementById('function_dv').style.display = 'none';
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

}

</script>

<style type="text/css">


body{background: #555555; color: #eee; }

.user{color: #f70; font-size: 17px;}

.res{}
.res_message{background: #222;}
.res_message_close{color: #999; background: #222;}

.message_at{ color: #f70; }
.message{ color: #f70; }

.result{ color: #eee; }

.nav_wrap_m{
background: #333;
}

.nav_wrap_f{}

.nav_wrap{
border-right: 1px solid #111;
}

.nav_wrap_filter{}

.nav_value{
background: none;
border: 1px solid #333;
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

.slc_list{
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

.rt_label_nv{ 
cursor: pointer; 
color: #f70;
background: none;
border: 0px;
}

.rt_label_nv:hover{
color: #999;
}

.rt_label_tl1,
.rt_label_tl2{
font-size: 17px;
}

.rt_label_tl2:hover{
color:#999;
cursor: pointer;
}

.rt_label_sv,
.rt_label_sv_des{
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

#script_text_sql{
background: #555;
border: 1px solid #777;
color: #eee;
}

.wr_main_nav{
border-bottom: 3px solid #555;
}

.file_ul{
color: #eee;
}

.file_ul:hover{
color: #999;
}

.file_dl_a{
color: #eee;
}

.file_dl_a:hover{
color: #999;
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
width: 970px;
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
left: 0px;
padding: 0px 11px 7px 11px;
font-size: 15px;
cursor: pointer;
}

.message{ 
padding: 3px 11px 3px 51px;
}



.message_at{
padding-bottom: 5px;
}

.result{
padding-bottom: 11px;
}


.nav{
text-align: left;
width: 973px;
padding: 0px;
margin: 0px;
}

.nav_wrap_m{
width: 100%;
padding: 0px; margin: 0px;
border-spacing: 0px;
}

.nav_wrap_f{
padding: 0px; margin: 0px;
border-spacing: 0px;
}



.nav_wrap, .nav_wrap_filter{
margin-right: 2px;
width: 129px;
}

.nav_value{
width: 325px;
padding: 8px;
margin: 2px 4px 2px 2px;
}

.nav_label{
padding: 10px 3px 0px 10px;
margin: 2px;
border: 0px;
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

.slc_list{
width: 45px;
padding: 7px;
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


.rt_label_nv{
padding: 0px 5px 0px 5px; 
}

.rt_label_sv,
.rt_label_sv_des,
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
.st_value_D,
.st_select_value{
padding: 8px;
margin: 1px 2px 1px 0px;
}

.rt_label_sv{ width: 200px;}
.rt_label_sv_des{ width: 730px; }
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

.rt_list_tb{
border-spacing: 0;
}

.rt_list_tb td{
padding: 0 2px 2px 0;
}

#script_text_sql{
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
padding: 89px 43px 0px 43px;
}

.wr_main_nav{
z-index: 191;
}


#status{
z-index: 501;
position: fixed;
top: 0;
right: 0;
bottom: 0;
left: 0;
cursor: wait;
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


.nav_main_back_un,
.nav_main_back{
position:fixed; 
top: 0;
left: 0;
width: 100%;
}

.nav_main_back_un{
z-index: 200;
height: 94px;
background: #555555; 
}

.nav_main_back{
z-index: 201;
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
float: left;
}

.nav_main_nv{
display: flex;
flex-wrap: wrap; 
height: 45px; 
align-content: center;
}

.btn_nav_first_im{
border: 0px;
border-right: 1px solid #000;
width: 47px;
height: 47px;
text-align: center;
cursor: pointer;
}


.btn_nav,
.btn_nav:hover{
border: 0px;
border-right: 1px solid #000;
padding: 14px 5px 14px 5px;
width: 117px;
height: 47px;	
cursor: pointer;
background-color: #333;
color:#FFF;
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



.file_ul{
position: relative;
overflow: hidden;
width: 171px;
height: 41px;
line-height: 39px;
}

.file_ul input[type="file"]{
display: none;
}

.file_ul label{
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
cursor: pointer;
}

.file_ul span{
padding: 0px 11px 0px 0px;
font-size: 15px;
}


.file_dl{
margin: 0px;
padding: 0px 11px 0px 0px;
display:inline-block;
line-height: 39px;
}

.file_dl_a{
font-size: 15px;
text-decoration: none;
}


</style>

</head>
<body onload="ms.pst('session=<?php echo _SESSION; ?>');">

<?php Control::storage(); ?>

<div id="expl_page" class="page">

	<div id="status" style="display: none;"></div>
	<div class="block" id="content"></div>
	
</div>

<!--[if lt IE 11]>
<script type="text/javascript">
document.getElementById("expl_page").innerHTML = "<h2><?php echo _MESSAGE_SUPPORT; ?></h2>";
</script>
<![endif]-->

</body>
</html>
