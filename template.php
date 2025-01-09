<?php

defined("_EXEC") or die();

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>manager-mysql</title>

<script type="text/javascript">

"use strict";

var cs =
{
	pst: function(data, container = "content")
	{
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
					ms.rdf('script_text_php');
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
	
	set_rs: function(oForm = []){

		if(document.getElementById("request")){

			var request = document.getElementById("request").value;
			var pass = document.getElementById("pass").innerHTML;

			return this.sha1(""+encodeURIComponent(request+pass)+this.str_request(oForm));
		}

		return "";
	},

	str_request: function(oForm)
	{
		var A = (<?php echo json_encode($CHECK); ?>).replace(/\s{1,}/g, "").split(",");

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
	}
}


var as =
{
	in_stu: function(){

		var event = ms.st_event();

		if(event.keyCode === 13){

			this.set_usr();
			event.preventDefault();
		}
	},

	in_stp: function(usr){

		var event = ms.st_event();

		if(event.keyCode === 13){

			this.set_ps(usr);
			event.preventDefault();
		}
	},

	clr_ps: function(){

		document.getElementById("pass").innerHTML = "";
		document.getElementById("request").value = "";
	},

	set_usr: function(){

		var us = document.getElementById("en_user").value;
		document.getElementById("en_user").value = "";

		cs.pst("session=<?php echo _SESSION; ?>"+"&request="+cs.set_rs()+"&usr="+us);
	},

	set_ps: function(us){

		var pass = document.getElementById("en_pass").value;
		var result = pass.replace(/^\s+/, '').replace(/\s+$/, '');

		document.getElementById("pass").innerHTML = cs.sha1("<?php echo _SESSION; ?>"+result);

		cs.pst("session=<?php echo _SESSION; ?>"+"&request="+cs.set_rs()+"&usr="+us);
	}
};


var ms =
{
	RF: function(action, sh, table, form, request, container = "content")
	{
		form.session.value = "<?php echo _SESSION; ?>";		
		
		if(action !== ""){form.action.value=action;}

		if(sh !== ""){form.sh.value=sh;}
		if(table !== ""){form.tb.value=table;}

		var data = "";
		var oForm = [];

		if(form != "")
		{
			for(var i=0;i<form.length;i++)
			{
				if(form[i].name && (form[i].name !== "request"))
				{
					if(form[i].type === "checkbox")
					{
						if(form[i].checked)
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
			form["request"].value = cs.set_rs(oForm);
			form.submit();
		}
		else
		{
			data += "request=" + cs.set_rs(oForm);
			cs.pst(data, container);
		};
	},


	AL: function(action, id_win, obj, form, cbName, text, warning_list, request_list, war)
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

					if( obj.nodeName === "SELECT"){ obj[0].selected='selected'; }

					return false;
				}
			}
		}

		for (var i=0; i < obj.length; i++) {

			if(obj[i].value === obj.value){ text += obj[i].innerHTML; }
		}

		var warning = 0;
		for (var w=0; w < warning_list.length; w++) {

			if(warning_list[w] === obj.value){ warning = 1; }
		}

		var request = 0;
		for (var r=0; r < request_list.length; r++) {

			if(request_list[r] === obj.value){ request = 1;}
		}

		form.action.value = action;

		if( obj.nodeName === "SELECT"){ obj[0].selected='selected'; }

		if((warning === 1) && (request === 0)){

			this.open_confirm(id_win, text);
		}
		else{

			ms.RF('', '', '', form, request);
		}
	},


	AT: function(action, id_win, obj, form, text, warning_list, request_list)
	{
		for (var i=0; i < obj.length; i++) {

			if(obj[i].value === obj.value){ text += obj[i].innerHTML; }
		}

		var warning = 0;
		for (var w=0; w < warning_list.length; w++) {

			if(warning_list[w] === action){ warning = 1; }
		}

		form.action.value = action;

		if((warning === 1)){

			this.open_confirm(id_win, text);
		}
		else{

			ms.RF('', '', '', form, 0);
		}
	},


	AV: function(action, sh, table, form, request, id, war)
	{
		if(document.getElementById(id).value.replace(/\s/g, "") === ""){

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


	set_list_ch_fr: function(form, cbName, checked)
	{
		if(form[cbName].length){

			for (var i=0; i < form[cbName].length; i++) {

				form[cbName][i].checked = checked;
			}
		}
		else{

			form[cbName].checked = checked;
		}

		form['totalH'].checked = checked;

		if(form['totalF']){

			form['totalF'].checked = checked;
		}
	},


	set_vl_sl: function(id, ts, wr)
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

		this.check_change(t, 'dt_in_value_change');

		document.getElementById(wr).style.display = "none";
	},


	set_vl: function(id, vl){

		document.getElementById(id).value = vl;
	},


	check_change: function(obj, style)
	{
		obj.className = style;			
	},


	view_wr: function(id, id_close, obj)
	{
		for (var i = 0, f; f = id_close[i]; i++){

			document.getElementById(f).style.display = "none";

			if(document.getElementById("nav_"+f)){	
			
				document.getElementById("nav_"+f).className = "btn_nav";
			}
		}

		if(document.getElementById(id).style.display === "none"){

			document.getElementById(id).style.display = "";
			
			obj.className = "btn_nav_focus";
		}
		else{

			document.getElementById(id).style.display = "none";
			document.getElementById("nav_"+id).className = "btn_nav";		
		}

		window.scrollTo(0,0);
	},


	view_va: function(id, display)
	{
		document.getElementById(id).style.display = display;
	},


	view_vb: function(id)
	{
		if(document.getElementById(id).style.display === "none"){

			document.getElementById(id).style.display = "";
		}
		else{

			document.getElementById(id).style.display = "none";
		}
	},


	edit_text: function(slID, textID, obj)
	{
		var slt = document.getElementById(slID);
		var text = document.getElementById(textID);

		var start = text.selectionStart;
		var end = text.selectionEnd;

		text.focus();

		text.value = text.value.substr(0,start)+slt.value+text.value.substr(end);

		text.selectionStart = start;
		text.selectionEnd = start+slt.value.length;

		obj[0].selected='selected';
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
		var fn = "";

		for (var i = 0, f; f = files[i]; i++)
		{
			(function(e) {

				var reader = new FileReader();

				reader.onloadend = function(){

					if (reader.readyState == FileReader.DONE)
					{
						str += "/* "+files[e].name+" */\r\n";

						fn += files[e].name+"; ";

						try{

							str += decodeURIComponent(escape(reader.result))+"\r\n\r\n";
						}
						catch (CatchException){

							str += "/* ERROR */\r\n";
							str += reader.result+"\r\n\r\n";
						}

						if(e === (files["length"]-1)){

							document.getElementById(script_text_id).value = str;
						}
					}
				};

				reader.readAsBinaryString(f);

			})(i);
		}
	},


	get_rcul_file: function(files, id, text)
	{
		var name = "";
		var str = "";

		for (var i = 0, f; f = files[i]; i++)
		{
			(function(e) {

				var reader = new FileReader();

				reader.onloadend = function(){

					if (reader.readyState == FileReader.DONE) {

						name += files[e].name.replace (/\\/g, "/").split ('/').pop ()+"; ";

						str += reader.result;

						if(e === (files["length"]-1))
						{
							document.getElementById("file_name"+id).style.padding = "7px";
							document.getElementById("file_name"+id).innerText = name;

							if(text === "blob"){

								document.getElementById("file"+id).innerText = window.btoa(str);
							}
							else{

								document.getElementById("file"+id).innerText = str;
							}
						}
					}
				};

				reader.readAsBinaryString(f);

			})(i);
		}
	},


	reset_rcul_file_click: function(id)
	{
		document.getElementById("file"+id).disabled = "";
		document.getElementById("file_name"+id).style.display = "";
		document.getElementById("file_name_sl"+id).style.display = "block";
		document.getElementById("text"+id).innerText = "";
		document.getElementById("text"+id).hidden = "hidden";
		document.getElementById("text"+id).disabled = "disabled";
		document.getElementById("prev"+id).style.display = "none";
		document.getElementById("function_"+id).disabled = "disabled";
	},


	reset_rcul_file: function(id)
	{
		document.getElementById("file"+id).disabled = "disabled";
		document.getElementById("file_name"+id).style.display = "none";
		document.getElementById("file_name_sl"+id).style.display = "none";
		document.getElementById("text"+id).innerText = "";
		document.getElementById("text"+id).hidden = "";
		document.getElementById("text"+id).disabled = "";
		document.getElementById("prev"+id).style.display = "none";
		document.getElementById("function_"+id).disabled = "";
	},


	"com": "",

	el_open_com: function(id)
	{
		if(document.getElementById(id).style.display == "block")
		{
			document.getElementById(id).style.display = "none";
		}
		else
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
		}
	},


	el_stop_com: function()
	{
		var event = this.st_event();

		event.stopPropagation();
	},


	wdf: function()
	{
		document.body.addEventListener('click', close_com, false);

		function close_com()
		{
			if(ms.com !== ""){

				if(document.getElementById(ms.com)){

					document.getElementById(ms.com).style.display = "none";
				}
			}
		}
	},
	
	
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
	}


};


</script>

<style type="text/css">

body{background: #555555; color: #eee; }

.at_user{color: #f70;}

.at_pass{
background: none;
border: 1px solid #777;
color: #eee;}

.at_btn{
color: #eee;
background: #333;
border: 1px solid #333;
}

.at_message{ color: #f70; }

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

.slcus{
background: #333;
color: #eee;
}

.nav_wr_main{
border-bottom: 3px solid #555;
}

.nav_main_back_un{
background: #555555
}

.nav_label_nv{
cursor: pointer;
color: #fff;
background: none;
border: 0px;
}

.nav_label_nv:hover{
color: #999;
}

.nav_wrap_table{}

.sc_slc1{
background:#555;
color: #eee;
border: 1px solid #777;
}

.sc_slc2{
background:#555;
color: #eee;
border: 1px solid #777;
}

.sc_btn{
color: #eee;
background: #333;
border: 1px solid #333;
}

#script_text_sql,
#script_text_php{
background: #555;
border: 1px solid #777;
color: #eee;
}

.nav_wr_message{background: #222;}
.nav_wr_message_close{color: #999; background: #222;}
.nav_message{ color: #f70; }

.nav_wr_result{}
.nav_result{ color: #eee; }

.dt_in_key,
.dt_in_name,
.dt_in_type,
.dt_in_type_sl,
.dt_in_function,
.dt_in_function_disabled,
.dt_in_value,
.dt_in_value_change,
.dt_in_value_disabled,
.dt_in_text,
.dt_in_text_change,
.dt_in_search,
.dt_slc{
background: #555;
border: 1px solid #777;
color: #eee;
}

.dt_in_key,
.dt_in_name,
.dt_in_type{
background: #333;
border: 1px solid #333; }

.dt_in_type_sl{
outline: none;
}

.dt_in_function{}

.dt_in_function_disabled{
background:#333;
border: 1px solid #333;
}

.dt_in_value_disabled{
background:#333;
border: 1px solid #333;
}

.dt_slc{
border: 0px;
background: #333;
outline: none;
}

.dt_btn,
.dt_btn_text{
color: #eee;
background: #333;
border: 1px solid #333;
}


.dt_slc_list{
background:#333;
color: #eee;
border: 1px solid #333;
}

.dt_btn_search{
background: #333;
border: 1px solid #333;
color: #eee;
}

.dt_in_label_list,
.dt_btn_list{
background: #333;
border: 1px solid #333;
color: #eee;
}

.dt_btn_list{}

.dt_in_value_list{
background: #555;
border: 1px solid #777;
color: #eee;
}

.fl_wrap_main_table{
background: #333;
}

.fl_wrap_table{}

.fl_wrap_main_tb{
border-right: 1px solid #111;
}

.fl_wrap_td{}

.fl_value{
background: none;
border: 1px solid #777;
color: #eee;
}

.fl_slc,
.fl_slc2{
background:#333;
color: #eee;
border: 1px solid #333;
}

.fl_btn{
background: #333;
color: #eee;
border: 2px solid #333;
}



html{height:100%;}

body{
font-family: Arial,Helvetica,sans-serif;
font-size: 12px;
margin: 17px;
}

input, textarea{outline: none;}

table, td{
padding: 0px;
margin: 0px;

vertical-align: top;
border-spacing: 0;
}

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

.page{
text-align: left;
width:100%;
}

.block{
width: 100%;
display:inline-block;
text-align: left;
padding: 77px 0px 0px 0px;
}

.at_app{
font-size: 21px;
margin-left: 89px;
}

.at_user{
font-size: 14px;
margin-left: 89px;
}

.at_pass{
width: 211px;
padding: 7px;
margin: 3px 3px 2px 0px;
margin-left: 89px;
}

.at_btn{
width: 147px;
height: 33px;
padding: 5px;
margin: 2px 2px 2px 0px;
margin-left: 89px;
cursor: pointer;
}

.at_message{
padding-bottom: 5px;
margin-left: 89px;
}

.file_ul{
padding: 7px;
display: inline-block;
}

.file_ul input[type="file"]{
display: none;
}

.file_ul label{
width: 100%;
height: 100%;
cursor: pointer;
}

.file_ul span{}

.file_dl{
margin: 0px;
padding: 0px 0px 0px 11px;
display:inline-block;
}

.file_dl_a{
text-decoration: none;
}

.slcus{
z-index: 101;
position: absolute;
width: 299px;
padding: 3px 0px 0px 0px;
margin: 3px 2px 0px 0px;
}

.slcus_sl{
display: block;
padding: 9px;
max-height: 210px;
overflow: auto;
width: 278px;
}

.slcus_sl_k{
display:inline-block;
white-space: nowrap;
}

.nav_wr_main{
z-index: 191;
width: 100%;
}

.nav_main_nv{
display: flex;
flex-wrap: wrap;
height: 47px;
align-content: center;
}

.nav_label_nv{
padding: 2px 10px 0px 0px;
}

.nav_wrap_table{
padding: 0px; margin: 0px;
border-spacing: 0px;
}

.nav_wr_script{
width: 970px;
}

.sc_slc1,
.sc_slc2{
width: 105px;
padding: 7px;
margin: 2px 2px 2px 0px;
outline: none;
text-align: left;
}

.sc_btn{
width: 147px;
height: 33px;
padding: 5px;
margin: 2px 2px 2px 0px;
cursor: pointer;
}

#script_text_sql,
#script_text_php{
width: 100%;
height: 190px;
resize: vertical;
padding: 0px;
margin: 2px 0px 0px 0px;
}

.nav_wr_message{
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

.nav_wr_message_close{
z-index: 505;
position:fixed;
display:inline-block;
left: 0px;
padding: 0px 11px 7px 11px;
font-size: 15px;
cursor: pointer;
}

.nav_message{
padding: 3px 11px 3px 51px;
}

.nav_wr_result{
overflow: auto;
width: 100%;
max-height: 300px;
}

.nav_result{
padding-bottom: 11px;
}

.dt_label_tl{
font-size: 17px;
}

.dt_label_tl:hover{
color:#999;
cursor: pointer;
}

.dt_check{
margin: 9px;
padding: 0px;
}

.dt_in_key,
.dt_in_name,
.dt_in_type,
.dt_in_type_sl,
.dt_in_function,
.dt_in_function_disabled,
.dt_in_value,
.dt_in_value_change,
.dt_in_value_disabled,
.dt_in_text,
.dt_in_text_change,
.dt_in_search,
.dt_slc{
padding: 8px;
margin: 1px 2px 1px 0px;
}

.dt_in_value_change{
font-style: italic;
}
.dt_in_text_change{
font-style: italic;
}

.dt_in_key{
width: 27px;
}

.dt_in_name{ width: 188px; }
.dt_in_type{ width: 149px; }

.dt_in_type_sl{
width: 149px;
cursor: pointer;
}

.dt_in_function{
width: 57px;
cursor: pointer;
}

.dt_in_function_disabled{
width: 57px;}

.dt_in_value,
.dt_in_value_change,
.dt_in_value_disabled{
width: 417px;
margin-right: 0px;
}

.dt_in_value_disabled{
cursor: default;
}

.dt_in_text,
.dt_in_text_change{
width: 919px;
height: 100px;
vertical-align: top;
resize: vertical;
}

.dt_in_search{ width: 646px; }
.dt_slc{ width: 151px; }

.dt_btn{
width: 147px;
height: 33px;
padding: 5px;
margin: 2px 2px 2px 0px;
cursor: pointer;
}

.dt_btn_text{
width: 435px;
height: 33px;
padding: 5px 5px 5px 11px;
margin: 2px 0px 2px 0px;
text-align: left;
}

.dt_wr{
width: 1075px
}

.dt_slc_list{
width: 45px;
padding: 7px;
outline: none;
text-align: left;
}

.dt_btn_search{
width: 151px;
height: 33px;
padding: 5px;
margin-right: 2px;
cursor: pointer;
}

.dt_list_table{
border-spacing: 0;
}

.dt_list_table td{
padding: 0 3px 2px 0;
}

.dt_in_label_list,
.dt_in_value_list,
.dt_btn_list{
padding: 8px;
margin: 0px;
cursor: default;
}

.dt_in_label_list,
.dt_in_value_list{
width: 149px;
}

.dt_btn_list{
width: 45px;
cursor: pointer;
}

.fl_wrap_main_table{
width: 100%;
padding: 0px; margin: 0px;
border-spacing: 0px;
width:970px;
}

.fl_wrap_table{
padding: 0px; margin: 0px;
border-spacing: 0px;
}

.fl_wrap_main_tb,
.fl_wrap_td{
margin-right: 2px;
width: 129px;
}

.fl_value{
width: 325px;
padding: 7.3px;
margin: 3px 4px 2px 2px;
}

.fl_label{
display:inline-block;
width: 51px;
padding: 10px 3px 0px 10px;
margin: 2px;
border: 0px;
text-align: right;
}

.fl_slc{
width: 125px;
padding: 7px;
margin: 3px 2px 2px 0px;
outline: none;
text-align: left;
}

.fl_slc2{
width: 235px;
padding: 7px;
margin: 3px 2px 2px 0px;
outline: none;
text-align: left;
}

.fl_btn{
width: 125px;
padding: 7px 7px 7px 11px;
margin: 3px 2px 2px 0px;
outline: none;
text-align: left;
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
width: 100%;
height: 47px;
}

.altDl_text{
padding: 15px;
font-size: 15px;
display: inline-block;
}

.altDl_btn{
width: 57px;
height: 47px;
float: left;
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

.btn_nav_first_im{
border: 0px;
border-right: 1px solid #000;
width: 47px;
height: 47px;
text-align: center;
cursor: pointer;
}

.btn_nav_first_im path:nth-of-type(1){
fill: white;
}

.btn_nav_first_im path:nth-of-type(2){
stroke: #fff;
stroke-width: 2px;	
fill: transparent;
}

.btn_nav,
.btn_nav:hover,
.btn_nav:active,
.btn_nav:focus{
border: 0px;
border-right: 1px solid #000;
padding: 14px 5px 14px 5px;
width: 117px;
height: 47px;
cursor: pointer;
background-color: #333;
color:#FFF;
}

.btn_nav_focus{
border: 0px;
border-right: 1px solid #000;
padding: 14px 5px 14px 5px;
width: 117px;
height: 47px;	
cursor: pointer;
background-color: #111;
color:#FFF;
}

.confirmDl_title{
color: #eee;
font-size: 19px;
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
padding-bottom: 2px;
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
max-height: 230px;
}

.rcDl_str{
cursor: pointer;
padding: 3px;
}

</style>


</head>
<body onload="cs.pst('session=<?php echo _SESSION; ?>');">

<div id='pass' class='' style='display: none;'>...</div>

<div id="expl_page" class="page">

	<div id="status" style="display: none;"></div>
	<div class="block" id="content"></div>

</div>

</body>
</html>



<?php

/*
<!--[if lt IE 11]>
<script type="text/javascript">
document.getElementById("expl_page").innerHTML = "<h2><?php echo _MESSAGE_SUPPORT; ?></h2>";
</script>
<![endif]-->
*/

?>