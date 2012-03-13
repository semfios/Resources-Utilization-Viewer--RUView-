<?php
	@header("Content-Type: text/javascript; charset=iso-8859-1");
	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
?>
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
function makeResizeable(id){ // sets the textarea with given id as resizeable
	var minHeight=50;
	$(id).insert({ after: '<div id="resize'+id+'" title="Drag the bar up or down to resize the text box." style="width:50px; height:5px; margin: -2px 0 2px; padding: 0; cursor:n-resize; border:1px solid #000;" class="TableHeader"></div>' });
	$('resize'+id).setStyle({ width: ($(id).getWidth()-2)+'px' });
	$(id).setStyle({ resize: 'none' });
	new Draggable('resize'+id, {
		constraint: 'vertical', scroll: window, revert: true,
		change: function(d){
			var poRA=$('resize'+id).positionedOffset();
			var poA=$(id).positionedOffset();
			var Ah=poRA[1]-poA[1];
			
			if(Ah<minHeight){ // enforce min height
				$(id).setStyle({ height: minHeight+'px' });
			}else{
				$(id).setStyle({ height: Ah+'px' });
			}
		}
	});
}
function resources_validateData(){
	return true;
}
function projects_validateData(){
	return true;
}
function assignments_validateData(){
	if($('Commitment').value==''){ alert('<?php echo addslashes($Translation['error:']); ?> Commitment: <?php echo addslashes($Translation['field not null']); ?>'); $('Commitment').focus(); return false; };
	return true;
}
function post(url, params, update, disable, loading){
	new Ajax.Request(
		url, {
			method: 'post',
			parameters: params,
			onCreate: function() {
				if($(disable) != undefined) $(disable).disabled=true;
				if($(loading) != undefined && update != loading) $(loading).innerHTML='<div style="direction: ltr;"><img src="loading.gif"> <?php echo $Translation['Loading ...']; ?></div>';
			},
			onSuccess: function(resp) {
				if($(update) != undefined) $(update).innerHTML=resp.responseText;
			},
			onComplete: function() {
				if($(disable) != undefined) $(disable).disabled=false;
				if($(loading) != undefined && loading != update) $(loading).innerHTML='';
			}
		}
	);
}
