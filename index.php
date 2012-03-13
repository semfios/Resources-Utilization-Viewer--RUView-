<?php
	error_reporting(E_ALL ^ E_NOTICE);
	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/lib.php");
	
	$x=new DataList;
	$x->TableTitle=$Translation['homepage'];
	$tablesPerRow=2;
	$arrTables=getTableList();
	
	// according to provided GET parameters, either log out, show login form (possibly with a failed login message), or show homepage
	if($_GET['signOut']==1){
		logOutMember();
	}elseif($_GET['loginFailed']==1 || $_GET['signIn']==1){
		include("$d/login.php");
	}else{
		include("$d/home.php");
	}