<?php
	$d=dirname(__FILE__);
	require("$d/incCommon.php");

	// validate input
	$memberID=makeSafe(strtolower($_GET['memberID']));

	sql("delete from membership_users where lcase(memberID)='$memberID'", $eo);
	sql("update membership_userrecords set memberID='' where lcase(memberID)='$memberID'", $eo);

	if($_SERVER['HTTP_REFERER']){
		redirect($_SERVER['HTTP_REFERER'], TRUE);
	}else{
		redirect("pageViewMembers.php");
	}

?>