<?php

// Data functions for table projects

// This script and data application were generated by AppGini 4.70
// Download AppGini for free from http://bigprof.com/appgini/download/

function projects_insert(){
	global $Translation;

	if($_GET['insert_x']!=''){$_POST=$_GET;}

	// mm: can member insert record?
	$arrPerm=getTablePermissions('projects');
	if(!$arrPerm[1]){
		return 0;
	}

	$data['Name'] = makeSafe($_POST['Name']);
	$data['StartDate'] = makeSafe($_POST['StartDateYear']) . '-' . makeSafe($_POST['StartDateMonth']) . '-' . makeSafe($_POST['StartDateDay']);
	$data['StartDate'] = parseMySQLDate($data['StartDate'], '');
	$data['EndDate'] = makeSafe($_POST['EndDateYear']) . '-' . makeSafe($_POST['EndDateMonth']) . '-' . makeSafe($_POST['EndDateDay']);
	$data['EndDate'] = parseMySQLDate($data['EndDate'], '');

	// hook: projects_before_insert
	if(function_exists('projects_before_insert')){
		$args=array();
		if(!projects_before_insert($data, getMemberInfo(), $args)){ return FALSE; }
	}

	$o=array('silentErrors' => true);
	sql('insert into `projects` set `Name`=' . (($data['Name'] != '') ? "'{$data['Name']}'" : 'NULL') . ', `StartDate`=' . (($data['StartDate'] != '') ? "'{$data['StartDate']}'" : 'NULL') . ', `EndDate`=' . (($data['EndDate'] != '') ? "'{$data['EndDate']}'" : 'NULL'), $o);
	if($o['error']!=''){
		echo $o['error'];
		echo "<a href=\"projects_view.php?addNew_x=1\">{$Translation['< back']}</a>";
		exit;
	}
	
	$recID=mysql_insert_id();

	// hook: projects_after_insert
	if(function_exists('projects_after_insert')){
		$data['selectedID']=$recID;
		$args=array();
		if(!projects_after_insert($data, getMemberInfo(), $args)){ return (get_magic_quotes_gpc() ? stripslashes($recID) : $recID); }
	}

	// mm: save ownership data
	sql("insert into membership_userrecords set tableName='projects', pkValue='$recID', memberID='".getLoggedMemberID()."', dateAdded='".time()."', dateUpdated='".time()."', groupID='".getLoggedGroupID()."'", $eo);

	return (get_magic_quotes_gpc() ? stripslashes($recID) : $recID);
}

function projects_delete($selected_id, $AllowDeleteOfParents=false, $skipChecks=false){
	// insure referential integrity ...
	global $Translation;
	$selected_id=makeSafe($selected_id);

	// mm: can member delete record?
	$arrPerm=getTablePermissions('projects');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='projects' and pkValue='$selected_id'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='projects' and pkValue='$selected_id'");
	if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
		// delete allowed, so continue ...
	}else{
		return FALSE;
	}

	// hook: projects_before_delete
	if(function_exists('projects_before_delete')){
		$args=array();
		if(!projects_before_delete($selected_id, $skipChecks, getMemberInfo(), $args)){ return FALSE; }
	}

	// child table: assignments
	$res = sql("select `Id` from `projects` where `Id`='$selected_id'", $eo);
	$Id = mysql_fetch_row($res);
	$rires = sql("select count(1) from `assignments` where `ProjectId`='".addslashes($Id[0])."'", $eo);
	$rirow = mysql_fetch_row($rires);
	if($rirow[0] && !$AllowDeleteOfParents && !$skipChecks){
		$RetMsg = $Translation["couldn't delete"];
		$RetMsg = str_replace("<RelatedRecords>", $rirow[0], $RetMsg);
		$RetMsg = str_replace("<TableName>", "assignments", $RetMsg);
		return $RetMsg;
	}elseif($rirow[0] && $AllowDeleteOfParents && !$skipChecks){
		$RetMsg = $Translation["confirm delete"];
		$RetMsg = str_replace("<RelatedRecords>", $rirow[0], $RetMsg);
		$RetMsg = str_replace("<TableName>", "assignments", $RetMsg);
		$RetMsg = str_replace("<Delete>", "<input tabindex=\"2\" type=\"button\" class=\"button\" value=\"".$Translation['yes']."\" onClick=\"window.location='projects_view.php?SelectedID=".urlencode($selected_id)."&delete_x=1&confirmed=1';\">", $RetMsg);
		$RetMsg = str_replace("<Cancel>", "<input tabindex=\"2\" type=\"button\" class=\"button\" value=\"".$Translation['no']."\" onClick=\"window.location='projects_view.php?SelectedID=".urlencode($selected_id)."';\">", $RetMsg);
		return $RetMsg;
	}

	sql("delete from `projects` where `Id`='$selected_id'", $eo);

	// hook: projects_after_delete
	if(function_exists('projects_after_delete')){
		$args=array();
		projects_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("delete from membership_userrecords where tableName='projects' and pkValue='$selected_id'", $eo);
}

function projects_update($selected_id){
	global $Translation;

	if($_GET['update_x']!=''){$_POST=$_GET;}

	// mm: can member edit record?
	$arrPerm=getTablePermissions('projects');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='projects' and pkValue='".makeSafe($selected_id)."'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='projects' and pkValue='".makeSafe($selected_id)."'");
	if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){ // allow update?
		// update allowed, so continue ...
	}else{
		return;
	}

	$data['Name'] = makeSafe($_POST['Name']);
	$data['StartDate'] = makeSafe($_POST['StartDateYear']) . '-' . makeSafe($_POST['StartDateMonth']) . '-' . makeSafe($_POST['StartDateDay']);
	$data['StartDate'] = parseMySQLDate($data['StartDate'], '');
	$data['EndDate'] = makeSafe($_POST['EndDateYear']) . '-' . makeSafe($_POST['EndDateMonth']) . '-' . makeSafe($_POST['EndDateDay']);
	$data['EndDate'] = parseMySQLDate($data['EndDate'], '');
	$data['selectedID']=makeSafe($selected_id);

	// hook: projects_before_update
	if(function_exists('projects_before_update')){
		$args=array();
		if(!projects_before_update($data, getMemberInfo(), $args)){ return FALSE; }
	}

	$o=array('silentErrors' => true);
	sql('update `projects` set `Name`=' . (($data['Name'] != '') ? "'{$data['Name']}'" : 'NULL') . ', `StartDate`=' . (($data['StartDate'] != '') ? "'{$data['StartDate']}'" : 'NULL') . ', `EndDate`=' . (($data['EndDate'] != '') ? "'{$data['EndDate']}'" : 'NULL') . " where `Id`='".makeSafe($selected_id)."'", $o);
	if($o['error']!=''){
		echo $o['error'];
		echo '<a href="projects_view.php?SelectedID='.urlencode($selected_id)."\">{$Translation['< back']}</a>";
		exit;
	}


	// hook: projects_after_update
	if(function_exists('projects_after_update')){
		$args=array();
		if(!projects_after_update($data, getMemberInfo(), $args)){ return FALSE; }
	}

	// mm: update ownership data
	sql("update membership_userrecords set dateUpdated='".time()."' where tableName='projects' and pkValue='".makeSafe($selected_id)."'", $eo);

}

function projects_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $ShowCancel = 0){
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;

	// mm: get table permissions
	$arrPerm=getTablePermissions('projects');
	if(!$arrPerm[1] && $selected_id==''){ return ''; }
	// combobox: StartDate
	$combo_StartDate = new DateCombo;
	$combo_StartDate->DateFormat = "dmy";
	$combo_StartDate->MinYear = 1900;
	$combo_StartDate->MaxYear = 2100;
	$combo_StartDate->DefaultDate = parseMySQLDate('', '');
	$combo_StartDate->MonthNames = $Translation['month names'];
	$combo_StartDate->CSSOptionClass = 'Option';
	$combo_StartDate->CSSSelectedClass = 'SelectedOption';
	$combo_StartDate->NamePrefix = 'StartDate';
	// combobox: EndDate
	$combo_EndDate = new DateCombo;
	$combo_EndDate->DateFormat = "dmy";
	$combo_EndDate->MinYear = 1900;
	$combo_EndDate->MaxYear = 2100;
	$combo_EndDate->DefaultDate = parseMySQLDate('', '');
	$combo_EndDate->MonthNames = $Translation['month names'];
	$combo_EndDate->CSSOptionClass = 'Option';
	$combo_EndDate->CSSSelectedClass = 'SelectedOption';
	$combo_EndDate->NamePrefix = 'EndDate';

	if($selected_id){
		// mm: check member permissions
		if(!$arrPerm[2]){
			return "";
		}
		// mm: who is the owner?
		$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='projects' and pkValue='".makeSafe($selected_id)."'");
		$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='projects' and pkValue='".makeSafe($selected_id)."'");
		if($arrPerm[2]==1 && getLoggedMemberID()!=$ownerMemberID){
			return "";
		}
		if($arrPerm[2]==2 && getLoggedGroupID()!=$ownerGroupID){
			return "";
		}

		// can edit?
		if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){
			$AllowUpdate=1;
		}else{
			$AllowUpdate=0;
		}

		$res = sql("select * from `projects` where `Id`='".makeSafe($selected_id)."'", $eo);
		$row = mysql_fetch_array($res);
		$urow = $row; /* unsanitized data */
		$hc = new CI_Input();
		$row = $hc->xss_clean($row); /* sanitize data */
		$combo_StartDate->DefaultDate = $row["StartDate"];
		$combo_EndDate->DefaultDate = $row["EndDate"];
	}else{
	}

	// code for template based detail view forms

	// open the detail view template
	if(($_POST['dvprint_x']!='' || $_GET['dvprint_x']!='') && $selected_id){
		$templateCode=@implode('', @file('./templates/projects_templateDVP.html'));
		$dvprint=true;
	}else{
		$templateCode=@implode('', @file('./templates/projects_templateDV.html'));
		$dvprint=false;
	}

	// process form title
	$templateCode=str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Detail View', $templateCode);
	// unique random identifier
	$rnd1=($dvprint ? rand(1000000, 9999999) : '');
	$templateCode=str_replace('<%%RND1%%>', $rnd1, $templateCode);
	// process buttons
	if($arrPerm[1]){ // allow insert?
		if(!$selected_id) $templateCode=str_replace('<%%INSERT_BUTTON%%>', '<button tabindex="2" type="submit" class="positive" id="insert" name="insert_x" value="1" onclick="return projects_validateData();"><img src="addNew.gif" /> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode=str_replace('<%%INSERT_BUTTON%%>', '<button tabindex="2" type="submit" class="positive" id="insert" name="insert_x" value="1" onclick="return projects_validateData();"><img src="addNew.gif" /> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	}else{
		$templateCode=str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}
	if($selected_id){
		$templateCode=str_replace('<%%DVPRINT_BUTTON%%>', '<button tabindex="2" type="submit" id="dvprint" name="dvprint_x" value="1" onclick="document.myform.reset(); return true;"><img src="print.gif" /> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate){
			$templateCode=str_replace('<%%UPDATE_BUTTON%%>', '<button tabindex="2" type="submit" class="positive" id="update" name="update_x" value="1" onclick="return projects_validateData();"><img src="update.gif" /> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		}else{
			$templateCode=str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);

			// set records to read only if user can't insert new records
			if(!$arrPerm[1]){
				$jsReadOnly.="\n\n\tif(document.getElementsByName('Id').length){ document.getElementsByName('Id')[0].readOnly=true; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('Name').length){ document.getElementsByName('Name')[0].readOnly=true; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('StartDate').length){ document.getElementsByName('StartDate')[0].readOnly=true; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('StartDateDay').length){ var StartDateDay=document.getElementsByName('StartDateDay')[0]; StartDateDay.disabled=true; StartDateDay.style.backgroundColor='white'; StartDateDay.style.color='black'; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('StartDateMonth').length){ var StartDateMonth=document.getElementsByName('StartDateMonth')[0]; StartDateMonth.disabled=true; StartDateMonth.style.backgroundColor='white'; StartDateMonth.style.color='black'; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('StartDateYear').length){ var StartDateYear=document.getElementsByName('StartDateYear')[0]; StartDateYear.disabled=true; StartDateYear.style.backgroundColor='white'; StartDateYear.style.color='black'; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('EndDate').length){ document.getElementsByName('EndDate')[0].readOnly=true; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('EndDateDay').length){ var EndDateDay=document.getElementsByName('EndDateDay')[0]; EndDateDay.disabled=true; EndDateDay.style.backgroundColor='white'; EndDateDay.style.color='black'; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('EndDateMonth').length){ var EndDateMonth=document.getElementsByName('EndDateMonth')[0]; EndDateMonth.disabled=true; EndDateMonth.style.backgroundColor='white'; EndDateMonth.style.color='black'; }\n";
				$jsReadOnly.="\n\n\tif(document.getElementsByName('EndDateYear').length){ var EndDateYear=document.getElementsByName('EndDateYear')[0]; EndDateYear.disabled=true; EndDateYear.style.backgroundColor='white'; EndDateYear.style.color='black'; }\n";

				$noUploads=true;
			}
		}
		if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
			$templateCode=str_replace('<%%DELETE_BUTTON%%>', '<button tabindex="2" type="submit" class="negative" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');"><img src="delete.gif" /> ' . $Translation['Delete'] . '</button>', $templateCode);
		}else{
			$templateCode=str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode=str_replace('<%%DESELECT_BUTTON%%>', '<button tabindex="2" type="submit" id="deselect" name="deselect_x" value="1" onclick="document.myform.reset(); return true;"><img src="deselect.gif" /> ' . $Translation['Back'] . '</button>', $templateCode);
	}else{
		$templateCode=str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode=str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		$templateCode=str_replace('<%%DESELECT_BUTTON%%>', ($ShowCancel ? '<button tabindex="2" type="submit" id="deselect" name="deselect_x" value="1" onclick="document.myform.reset(); return true;"><img src="deselect.gif" /> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
	}

	// process combos
	$templateCode=str_replace('<%%COMBO(StartDate)%%>', $combo_StartDate->GetHTML(), $templateCode);
	$templateCode=str_replace('<%%COMBOTEXT(StartDate)%%>', $combo_StartDate->GetHTML(true), $templateCode);
	$templateCode=str_replace('<%%COMBO(EndDate)%%>', $combo_EndDate->GetHTML(), $templateCode);
	$templateCode=str_replace('<%%COMBOTEXT(EndDate)%%>', $combo_EndDate->GetHTML(true), $templateCode);

	// process foreign key links
	if($selected_id){
	}

	// process images
	$templateCode=str_replace('<%%UPLOADFILE(Id)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(Name)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(StartDate)%%>', '', $templateCode);
	$templateCode=str_replace('<%%UPLOADFILE(EndDate)%%>', '', $templateCode);

	// process values
	if($selected_id){
		$templateCode=str_replace('<%%VALUE(Id)%%>', htmlspecialchars($row['Id'], ENT_QUOTES), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Id)%%>', urlencode($urow['Id']), $templateCode);
		$templateCode=str_replace('<%%VALUE(Name)%%>', htmlspecialchars($row['Name'], ENT_QUOTES), $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Name)%%>', urlencode($urow['Name']), $templateCode);
		$templateCode=str_replace('<%%VALUE(StartDate)%%>', @date('j/n/Y', @strtotime(htmlspecialchars($row['StartDate'], ENT_QUOTES))), $templateCode);
		$templateCode=str_replace('<%%VALUE(EndDate)%%>', @date('j/n/Y', @strtotime(htmlspecialchars($row['EndDate'], ENT_QUOTES))), $templateCode);
	}else{
		$templateCode=str_replace('<%%VALUE(Id)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Id)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(Name)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(Name)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(StartDate)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(StartDate)%%>', urlencode(''), $templateCode);
		$templateCode=str_replace('<%%VALUE(EndDate)%%>', '', $templateCode);
		$templateCode=str_replace('<%%URLVALUE(EndDate)%%>', urlencode(''), $templateCode);
	}

	// process translations
	foreach($Translation as $symbol=>$trans){
		$templateCode=str_replace("<%%TRANSLATION($symbol)%%>", $trans, $templateCode);
	}

	// clear scrap
	$templateCode=str_replace('<%%', '<!--', $templateCode);
	$templateCode=str_replace('%%>', '-->', $templateCode);

	// hide links to inaccessible tables
	if($_POST['dvprint_x']==''){
		$templateCode.="\n\n<script>\n";
		$arrTables=getTableList();
		foreach($arrTables as $name=>$caption){
			$templateCode.="\tif(document.getElementById('".$name."_link')!=undefined){\n";
			$templateCode.="\t\tdocument.getElementById('".$name."_link').style.visibility='visible';\n";
			$templateCode.="\t}\n";
			for($i=1; $i<10; $i++){
				$templateCode.="\tif(document.getElementById('".$name."_plink$i')!=undefined){\n";
				$templateCode.="\t\tdocument.getElementById('".$name."_plink$i').style.visibility='visible';\n";
				$templateCode.="\t}\n";
			}
		}

		$templateCode.=$jsReadOnly;

		if(!$selected_id){
		}

		$templateCode.="\n</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode.="<script>";
	$templateCode.="document.observe('dom:loaded', function() {";


	$templateCode.="});";
	$templateCode.="</script>";

	// handle enforced parent values for read-only lookup fields

	// don't include blank images in lightbox gallery
	$templateCode=preg_replace('/blank.gif" rel="lightbox\[.*?\]"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	// hook: projects_dv
	if(function_exists('projects_dv')){
		$args=array();
		projects_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}
?>