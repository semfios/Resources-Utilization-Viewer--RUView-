<?php
	@header("Content-Type: text/javascript; charset=iso-8859-1");
	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
?>

document.observe("dom:loaded", function() {
	colorize();
	loadScript('resources/modalbox/modalbox.js', 'resources/modalbox/modalbox.css');
});

function colorize(){
	$$('tr.colorize').invoke('observe', 'mouseover', function(){ 
		this.descendants().grep(new Selector("td,a")).invoke('setStyle', { backgroundColor: '#FFF0C2' }); 
	});
	$$('tr.colorize').invoke('observe', 'mouseout', function(){ 
		this.descendants().grep(new Selector("td,a")).invoke('setStyle', { backgroundColor: '' }); 
	});
}

function showDescription(tableName, fieldName, e){
	var desc = {};
	desc["resources"] = {};
	desc["projects"] = {};
	desc["assignments"] = {};
	desc["assignments"]["Commitment"] = "1.00 means full time commitment, 0.50 means half-time, ... etc.";

	if(desc[tableName][fieldName] == undefined) return false;

	var x=0;
	var y=0;
	if(e.pageX){
		x=e.pageX-10;
		y=e.pageY-10;
	}else if(e.clientX){
		x=e.clientX-10;
		y=e.clientY-10;
		if(document.body && ( document.body.scrollLeft || document.body.scrollTop )){
			x+=document.body.scrollLeft;
			y+=document.body.scrollTop;
		}else if(document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop )){
			x+=document.documentElement.scrollLeft;
			y+=document.documentElement.scrollTop;
		}
	}else{
		return false;
	}

	$('fieldDescription').innerHTML=desc[tableName][fieldName];
	$('fieldDescription').style.left=x+'px';
	$('fieldDescription').style.top=y+'px';
	$('fieldDescription').style.visibility='visible';

	return false;
}
function resources_validateData(){
	return true;
}
function projects_validateData(){
	return true;
}
function assignments_validateData(){
	if($('Commitment').value==''){ Modalbox.show('<div class="Error" style="width: 90%; margin: 0;"><?php echo addslashes($Translation['field not null']); ?></div>', { title: "<?php echo addslashes($Translation['error:']); ?> Commitment", afterHide: function(){ $('Commitment').focus(); } }); return false; };
	return true;
}
function post(url, params, update, disable, loading){
	new Ajax.Request(
		url, {
			method: 'post',
			parameters: params,
			onCreate: function() {
				if($(disable) != undefined) $(disable).disabled=true;
				if($(loading) != undefined && update != loading) $(loading).update('<div style="direction: ltr;"><img src="loading.gif"> <?php echo $Translation['Loading ...']; ?></div>');
			},
			onSuccess: function(resp) {
				if($(update) != undefined) $(update).update(resp.responseText);
			},
			onComplete: function() {
				if($(disable) != undefined) $(disable).disabled=false;
				if($(loading) != undefined && loading != update) $(loading).update('');
			}
		}
	);
}
function post2(url, params, notify, disable, loading, redirectOnSuccess){
	new Ajax.Request(
		url, {
			method: 'post',
			parameters: params,
			onCreate: function() {
				if($(disable) != undefined) $(disable).disabled=true;
				if($(loading) != undefined) $(loading).show();
			},
			onSuccess: function(resp) {
				/* show notification containing returned text */
				if($(notify) != undefined) $(notify).removeClassName('Error').appear().update(resp.responseText);

				/* in case no errors returned, */
				if(!resp.responseText.match(/<?php echo $Translation['error:']; ?>/)){
					/* redirect to provided url */
					if(redirectOnSuccess != undefined){
						window.location=redirectOnSuccess;

					/* or hide notification after a few seconds if no url is provided */
					}else{
						if($(notify) != undefined) window.setTimeout(function(){ $(notify).fade(); }, 15000);
					}

				/* in case of error, apply error class */
				}else{
					$(notify).addClassName('Error');
				}
			},
			onComplete: function() {
				if($(disable) != undefined) $(disable).disabled=false;
				if($(loading) != undefined) $(loading).hide();
			}
		}
	);
}
function passwordStrength(password, username){
	// score calculation (out of 10)
	var score = 0;
	re = new RegExp(username, 'i');
	if(password.match(re)) score -= 5;
	if(password.length < 6) score -= 3;
	else if(password.length > 8) score += 5;
	else score += 3;
	if(password.match(/(.*[0-9].*[0-9].*[0-9])/)) score += 2;
	if(password.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) score += 3;
	if(password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) score += 2;

	if(score >= 9)
		return 'strong';
	else if(score >= 5)
		return 'good';
	else
		return 'weak';
}
function validateEmail(email) { 
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}
function loadScript(jsUrl, cssUrl, callback){
	// adding the script tag to the head
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = jsUrl;

	if(cssUrl != ''){
		var css = document.createElement('link');
		css.href = cssUrl;
		css.rel = "stylesheet";
		css.type = "text/css";
		head.appendChild(css);
	}

	// then bind the event to the callback function 
	// there are several events for cross browser compatibility
	if(script.onreadystatechange != undefined){ script.onreadystatechange = callback; }
	if(script.onload != undefined){ script.onload = callback; }

	// fire the loading
	head.appendChild(script);
}