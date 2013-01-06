<?php
	error_reporting(E_ALL ^ E_NOTICE);
	if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);
	ob_start();

	// check if setup was performed or not yet
	$currDir=dirname(__FILE__);
	$setupStyle="border: solid 1px red; background-color: #FFFFE0; color: red; font-size: 16px; font-family: arial; font-weight: bold; padding: 10px; width:400px; text-align: left;";
	if(!is_file("$currDir/config.php")){
		header('Content-Type: text/html; charset=iso-8859-1');
		?>
		<META HTTP-EQUIV="Refresh" CONTENT="2;url=setup.php">
		<center>
		<div style="<?php echo $setupStyle ?>">
			<?php echo $Translation['db setup needed']; ?>
			</div>
			</center>
		<?php
		exit;
	}
	if(!is_file("$currDir/admin/incConfig.php")){
		header('Content-Type: text/html; charset=iso-8859-1');
		?>
		<META HTTP-EQUIV="Refresh" CONTENT="2;url=admin/">
		<center>
		<div style="<?php echo $setupStyle ?>">
			<?php echo $Translation['admin setup needed']; ?>
			</div>
			</center>
		<?php
		exit;
	}
	// -----------------------------------------

	include("$currDir/admin/incFunctions.php");
	include("$currDir/admin/incConfig.php");
	// include global hook functions
	@include("$currDir/hooks/__global.php");

	// check sessions config
	$noPathCheck=True;
	$arrPath=explode(';', ini_get('session.save_path'));
	$save_path=$arrPath[count($arrPath)-1];
	if(!$noPathCheck && !is_dir($save_path)){
		?>
		<center>
		<div style="<?php echo $setupStyle ?>">
			Your site is not configured to support sessions correctly. Please edit your php.ini file and change the value of <i>session.save_path</i> to a valid path.
			</div>
			</center>
		<?php
		exit;
	}
	if(session_id()){ session_write_close(); }
	@ini_set('session.save_handler', 'files');
	@ini_set('session.serialize_handler', 'php');
	@ini_set('session.use_cookies', '1');
	@ini_set('session.use_only_cookies', '1');
	@ini_set('session.cache_limiter', 'nocache');
	@session_name('resources_utilization');
	session_start();
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: text/html; charset=iso-8859-1');

	// check if membership system exists
	setupMembership();

	// silently apply db changes, if any
	@include_once("$currDir/updateDB.php");

	// do we have a login request?
	logInMember();

	// convert expanded sorting variables, if provided, to SortField and SortDirection
	define('maxSortBy', 4);
	$postedOrderBy = array();
	for($i = 0; $i < maxSortBy; $i++){
		if(isset($_POST["OrderByField$i"])){
			$sd = ($_POST["OrderDir$i"] == 'desc' ? 'desc' : 'asc');
			if($sfi = intval($_POST["OrderByField$i"])){
				$postedOrderBy[] = array($sfi => $sd);
			}
		}
	}
	if(count($postedOrderBy)){
		$_POST['SortField'] = '';
		$_POST['SortDirection'] = '';
		foreach($postedOrderBy as $obi){
			$sfi = ''; $sd = '';
			foreach($obi as $sfi => $sd);
			$_POST['SortField'] .= "$sfi $sd,";
		}
		$_POST['SortField'] = substr($_POST['SortField'], 0, -2 - strlen($sd));
		$_POST['SortDirection'] = $sd;
	}

	#########################################################
	/*
	~~~~~~ LIST OF FUNCTIONS ~~~~~~
		getTableList() -- returns an associative array (tableName=>tableData, tableData is array(tableCaption, tableDescription, tableIcon)) of tables accessible by current user
		getLoggedMemberID() -- returns memberID of logged member. If no login, returns anonymous memberID
		getLoggedGroupID() -- returns groupID of logged member, or anonymous groupID
		logOutMember() -- destroys session and logs member out.
		logInMember() -- checks POST login. If not valid, redirects to index.php, else returns TRUE
		getTablePermissions($tn) -- returns an array of permissions allowed for logged member to given table (allowAccess, allowInsert, allowView, allowEdit, allowDelete) -- allowAccess is set to true if any access level is allowed
		htmlUserBar() -- returns html code for displaying user login status to be used on top of pages.
		showNotifications($msg, $class) -- returns html code for displaying a notification. If no parameters provided, processes the GET request for possible notifications.
		parseMySQLDate(a, b) -- returns a if valid mysql date, or b if valid mysql date, or today if b is true, or empty if b is false.
		parseCode(code) -- calculates and returns special values to be inserted in automatic fields.
		addFilter(i, filterAnd, filterField, filterOperator, filterValue) -- enforce a filter over data
		clearFilters() -- clear all filters
		getMemberInfo() -- returns an array containing the currently signed-in member's info
		filterDropdownBy($filterable, $filterers, $parentFilterers, $parentPKField, $parentCaption, $parentTable, &$filterableCombo) -- applies cascading drop-downs for a lookup field, returns js code to be inserted into the page
		br2nl($text) -- replaces all variations of HTML <br> tags with a new line character
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	*/
	#########################################################
	function getTableList(){
		$arrTables=array(
			'resources'=>array('Resources', '', 'resources/table_icons/account_balances.png'),
			'projects'=>array('Projects', '', 'resources/table_icons/application_from_storage.png'),
			'assignments'=>array('Assignments', '', 'resources/table_icons/client_account_template.png')
			);
		if(is_array($arrTables)){
			foreach($arrTables as $tn=>$tc){
				$arrPerm=getTablePermissions($tn);
				if($arrPerm[0]){
					$arrAccessTables[$tn]=$tc;
				}
			}
		}

		return $arrAccessTables;
	}
	#########################################################
	function getTablePermissions($tn){
		$groupID=getLoggedGroupID();
		$memberID=makeSafe(getLoggedMemberID());
		if(sqlValue("select count(1) from membership_userpermissions where lcase(memberID)='$memberID' and tableName='$tn'")){
			$res=sql("select allowInsert, allowView, allowEdit, allowDelete from membership_userpermissions where lcase(memberID)='$memberID' and tableName='$tn'", $eo);
		}else{
			$res=sql("select allowInsert, allowView, allowEdit, allowDelete from membership_grouppermissions where groupID='$groupID' and tableName='$tn'", $eo);
		}

		if($row=mysql_fetch_row($res)){
			if($row[0] || $row[1] || $row[2] || $row[3]){
				$arrRet[0]=TRUE; /* allowAccess */
			}else{
				$arrRet[0]=FALSE;
			}
			$arrRet[1]=$row[0]; /* allowInsert */
			$arrRet[2]=$row[1]; /* allowView */
			$arrRet[3]=$row[2]; /* allowEdit */
			$arrRet[4]=$row[3]; /* allowDelete */

			return $arrRet;
		}

		return array(FALSE, 0, 0, 0, 0);
	}
	#########################################################
	function getLoggedGroupID(){
		if($_SESSION['memberGroupID']!=''){
			return $_SESSION['memberGroupID'];
		}else{
			setAnonymousAccess();
			return getLoggedGroupID();
		}
	}
	#########################################################
	function getLoggedMemberID(){
		if($_SESSION['memberID']!=''){
			return strtolower($_SESSION['memberID']);
		}else{
			setAnonymousAccess();
			return getLoggedMemberID();
		}
	}
	#########################################################
	function setAnonymousAccess(){
		global $adminConfig;

		$anonGroupID=sqlValue("select groupID from membership_groups where name='".$adminConfig['anonymousGroup']."'");
		$_SESSION['memberGroupID']=($anonGroupID ? $anonGroupID : 0);

		$anonMemberID=sqlValue("select lcase(memberID) from membership_users where lcase(memberID)='".strtolower($adminConfig['anonymousMember'])."' and groupID='$anonGroupID'");
		$_SESSION['memberID']=($anonMemberID ? $anonMemberID : 0);
	}
	#########################################################
	function logInMember(){
		$redir='index.php';
		if($_POST['signIn']!=''){
			if($_POST['username']!='' && $_POST['password']!=''){
				$username=makeSafe(strtolower(trim($_POST['username'])));
				$password=md5(trim($_POST['password']));

				if(sqlValue("select count(1) from membership_users where lcase(memberID)='$username' and passMD5='$password' and isApproved=1 and isBanned=0")==1){
					$_SESSION['memberID']=$username;
					$_SESSION['memberGroupID']=sqlValue("select groupID from membership_users where lcase(memberID)='$username'");
					if($_POST['rememberMe']==1){
						@setcookie('resources_utilization_rememberMe', md5($username.$password), time()+86400*30);
					}else{
						@setcookie('resources_utilization_rememberMe', '', time()-86400*30);
					}

					// hook: login_ok
					if(function_exists('login_ok')){
						$args=array();
						if(!$redir=login_ok(getMemberInfo(), $args)){
							$redir='index.php';
						}
					}

					redirect($redir);
					exit;
				}
			}

			// hook: login_failed
			if(function_exists('login_failed')){
				$args=array();
				login_failed(array(
					'username' => $_POST['username'],
					'password' => $_POST['password'],
					'IP' => $_SERVER['REMOTE_ADDR']
					), $args);
			}

			redirect("index.php?loginFailed=1");
			exit;
		}elseif((!$_SESSION['memberID'] || $_SESSION['memberID']==$adminConfig['anonymousMember']) && $_COOKIE['resources_utilization_rememberMe']!=''){
			$chk=makeSafe($_COOKIE['resources_utilization_rememberMe']);
			if($username=sqlValue("select memberID from membership_users where convert(md5(concat(memberID, passMD5)), char)='$chk' and isBanned=0")){
				$_SESSION['memberID']=$username;
				$_SESSION['memberGroupID']=sqlValue("select groupID from membership_users where lcase(memberID)='$username'");
			}
		}
	}
	#########################################################
	function logOutMember(){
		logOutUser();
		redirect("index.php?signIn=1");
	}
	#########################################################
	function htmlUserBar(){
		global $adminConfig, $Translation;

		if($_POST['Print_x']!='' || $_GET['Print_x']!='' || $_POST['dvprint_x']!='' || $_GET['dvprint_x']!=''){
			return '';
		}

		ob_start();
		$homePage = (basename($_SERVER['PHP_SELF'])=='index.php' ? true : false);

		?>
		<div class="TableFooter" style="height: 20px; margin: 0 0 25px 0; padding: 3px 10px; border-radius: 4px;">
			<?php if(!$homePage){ ?>
				<div style="float: left; margin: 0 20px 0 0;"><a href="./index.php"><img src="home.png" border="0" title="<?php echo $Translation['homepage']; ?>" /></a></div>
				<div style="float: left;">
					<?php echo NavMenus(); ?>
				</div>
			<?php } ?>
			<div style="float: right;"><?php
				if(!$_GET['signIn'] && !$_GET['loginFailed']){
					if(getLoggedMemberID()==$adminConfig['anonymousMember']){
						?><?php echo $Translation['not signed in']; ?>. <a href="index.php?signOut=1"><?php echo $Translation['sign in']; ?></a><?php
					}else{
						?><?php echo $Translation['signed as']; ?> '<a href="membership_profile.php"><?php echo getLoggedMemberID(); ?></a>'. <a href="index.php?signOut=1"><?php echo $Translation['sign out']; ?></a><?php
					}
				}
			?></div>
		</div>
		<?php

		$html=ob_get_contents();
		ob_end_clean();

		return $html;
	}
	#########################################################
	function showNotifications($msg='', $class=''){
		global $Translation;

		$notifyTemplate='<div id="%%ID%%" class="%%CLASS%%" style="display: none;">%%MSG%%</div>'.
					'<script>new Effect.Appear("%%ID%%", {duration:2, from:0.0, to:1.0}); '.
					'new PeriodicalExecuter(function(pe){ '.
					'new Effect.Fade("%%ID%%", {duration:2}); '.
					'pe.stop();'.
					'}, 5); </script>'."\n";

		if(!$msg){ // if no msg, use url to detect message to display
			if($_GET['record-added-ok']!=''){
				$msg=$Translation['new record saved'];
				$class='SuccessNotify';
			}elseif($_GET['record-updated-ok']!=''){
				$msg=$Translation['record updated'];
				$class='SuccessNotify';
			}else{
				return '';
			}
		}
		$id='notification-'.rand();

		$out=$notifyTemplate;
		$out=str_replace('%%ID%%', $id, $out);
		$out=str_replace('%%MSG%%', $msg, $out);
		$out=str_replace('%%CLASS%%', $class, $out);

		return $out;
	}
	#########################################################
	function parseMySQLDate($date, $altDate){
		// is $date valid?
		if(preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", trim($date))){
			return trim($date);
		}

		if($date != '--' && preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", trim($altDate))){
			return trim($altDate);
		}

		if($date != '--' && $altDate && intval($altDate)==$altDate){
			return @date('Y-m-d', @time() + ($altDate >= 1 ? $altDate - 1 : $altDate) * 86400);
		}

		return '';
	}
	#########################################################
	function parseCode($code, $isInsert=true, $rawData=false){
		if($isInsert){
			$arrCodes=array(
				'<%%creatorusername%%>' => $_SESSION['memberID'],
				'<%%creatorgroupid%%>' => $_SESSION['memberGroupID'],
				'<%%creatorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%creatorgroup%%>' => sqlValue("select name from membership_groups where groupID='{$_SESSION['memberGroupID']}'"),

				'<%%creationdate%%>' => ($rawData ? @date('Y-m-d') : @date('j/n/Y')),
				'<%%creationtime%%>' => ($rawData ? @date('H:i:s') : @date('h:i:s a')),
				'<%%creationdatetime%%>' => ($rawData ? @date('Y-m-d H:i:s') : @date('j/n/Y h:i:s a')),
				'<%%creationtimestamp%%>' => ($rawData ? @date('Y-m-d H:i:s') : @time())
			);
		}else{
			$arrCodes=array(
				'<%%editorusername%%>' => $_SESSION['memberID'],
				'<%%editorgroupid%%>' => $_SESSION['memberGroupID'],
				'<%%editorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%editorgroup%%>' => sqlValue("select name from membership_groups where groupID='{$_SESSION['memberGroupID']}'"),

				'<%%editingdate%%>' => ($rawData ? @date('Y-m-d') : @date('j/n/Y')),
				'<%%editingtime%%>' => ($rawData ? @date('H:i:s') : @date('h:i:s a')),
				'<%%editingdatetime%%>' => ($rawData ? @date('Y-m-d H:i:s') : @date('j/n/Y h:i:s a')),
				'<%%editingtimestamp%%>' => ($rawData ? @date('Y-m-d H:i:s') : @time())
			);
		}

		$pc=str_ireplace(array_keys($arrCodes), array_values($arrCodes), $code);

		return $pc;
	}
	#########################################################
	function addFilter($index, $filterAnd, $filterField, $filterOperator, $filterValue){
		// validate input
		if($index<1 || $index>80 || !is_int($index))   return false;
		if($filterAnd!='or')   $filterAnd='and';
		$filterField=intval($filterField);
		$filterOperator=strtolower($filterOperator);
		if(!in_array($filterOperator, array('<=>', '!=', '>', '>=', '<', '<=', 'like', 'not like', 'isEmpty', 'isNotEmpty')))
			$filterOperator='like';

		if(!$filterField){
			$filterOperator='';
			$filterValue='';
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$_POST['FilterAnd'][$index]=$filterAnd;
			$_POST['FilterField'][$index]=$filterField;
			$_POST['FilterOperator'][$index]=$filterOperator;
			$_POST['FilterValue'][$index]=$filterValue;
		}else{
			$_GET['FilterAnd'][$index]=$filterAnd;
			$_GET['FilterField'][$index]=$filterField;
			$_GET['FilterOperator'][$index]=$filterOperator;
			$_GET['FilterValue'][$index]=$filterValue;
		}

		return true;
	}
	#########################################################
	function clearFilters(){
		for($i=1; $i<=80; $i++){
			addFilter($i, '', 0, '', '');
		}
	}
	#########################################################
	function getMemberInfo($memberID=''){
		global $adminConfig;
		$mi=array();

		if(!$memberID){
			$memberID=getLoggedMemberID();
		}

		if($memberID){
			$res=sql("select * from membership_users where memberID='".addslashes($memberID)."'", $eo);
			if($row=mysql_fetch_assoc($res)){
				$mi['username']=$memberID;
				$mi['groupID']=$row['groupID'];
				$mi['group']=sqlValue("select name from membership_groups where groupID='".$row['groupID']."'");
				$mi['admin']=($adminConfig['adminUsername']==$memberID ? TRUE : FALSE);
				$mi['email']=$row['email'];
				$mi['custom'][0]=$row['custom1'];
				$mi['custom'][1]=$row['custom2'];
				$mi['custom'][2]=$row['custom3'];
				$mi['custom'][3]=$row['custom4'];
				$mi['banned']=($row['isBanned'] ? TRUE : FALSE);
				$mi['approved']=($row['isApproved'] ? TRUE : FALSE);
				$mi['signupDate']=@date('j/n/Y', @strtotime($row['signupDate']));
				$mi['comments']=$row['comments'];
				$mi['IP']=$_SERVER['REMOTE_ADDR'];
			}
		}

		return $mi;
	}
	#########################################################
	if(!function_exists('str_ireplace')){
		function str_ireplace($search, $replace, $subject){
			$ret=$subject;
			if(is_array($search)){
				for($i=0; $i<count($search); $i++){
					$ret=str_ireplace($search[$i], $replace[$i], $ret);
				}
			}else{
				$ret=preg_replace('/'.preg_quote($search, '/').'/i', $replace, $ret);
			}

			return $ret;
		} 
	} 

	#########################################################
	/**
	* Loads a given view from the templates folder, passing the given data to it
	* @param $view the name of a php file (without extension) to be loaded from the 'templates' folder
	* @param $the_data_to_pass_to_the_view (optional) associative array containing the data to pass to the view
	* @return the output of the parsed view as a string
	*/
	function loadView($view, $the_data_to_pass_to_the_view=false){
		global $Translation;

		$view = dirname(__FILE__)."/templates/$view.php";
		if(!is_file($view)) return false;

		if(is_array($the_data_to_pass_to_the_view)){
			foreach($the_data_to_pass_to_the_view as $k => $v)
				$$k = $v;
		}
		unset($the_data_to_pass_to_the_view, $k, $v);

		ob_start();
		@include($view);
		$out=ob_get_contents();
		ob_end_clean();

		return $out;
	}

	#########################################################
	function filterDropdownBy($filterable, $filterers, $parentFilterers, $parentPKField, $parentCaption, $parentTable, &$filterableCombo){
		$filterersArray = explode(',', $filterers);
		$parentFilterersArray = explode(',', $parentFilterers);
		$parentFiltererList = '`' . implode('`, `', $parentFilterersArray) . '`';
		$res=sql("SELECT `$parentPKField`, $parentCaption, $parentFiltererList FROM `$parentTable` ORDER BY 2", $eo);
		$filterableData = array();
		while($row=mysql_fetch_row($res)){
			$filterableData[$row[0]] = $row[1];
			$filtererIndex = 0;
			foreach($filterersArray as $filterer){
				$filterableDataByFilterer[$filterer][$row[$filtererIndex + 2]][$row[0]] = $row[1];
				$filtererIndex++;
			}
			$row[0] = addslashes($row[0]);
			$row[1] = addslashes($row[1]);
			$jsonFilterableData .= "\"{$row[0]}\":\"{$row[1]}\",";
		}
		$jsonFilterableData .= '}';
		$jsonFilterableData = '{'.str_replace(',}', '}', $jsonFilterableData);     
		$filterJS = "\nvar {$filterable}_data = $jsonFilterableData;";

		foreach($filterersArray as $filterer){
			if(is_array($filterableDataByFilterer[$filterer])) foreach($filterableDataByFilterer[$filterer] as $filtererItem => $filterableItem){
				$jsonFilterableDataByFilterer[$filterer] .= '"'.addslashes($filtererItem).'":{';
				foreach($filterableItem as $filterableItemID => $filterableItemData){
					$jsonFilterableDataByFilterer[$filterer] .= '"'.addslashes($filterableItemID).'":"'.addslashes($filterableItemData).'",';
				}
				$jsonFilterableDataByFilterer[$filterer] .= '},';
			}
			$jsonFilterableDataByFilterer[$filterer] .= '}';
			$jsonFilterableDataByFilterer[$filterer] = '{'.str_replace(',}', '}', $jsonFilterableDataByFilterer[$filterer]);

			$filterJS.="\n\n// code for filtering {$filterable} by {$filterer}\n";
			$filterJS.="\nvar {$filterable}_data_by_{$filterer} = {$jsonFilterableDataByFilterer[$filterer]}; ";
			$filterJS.="\nvar selected_{$filterable} = \$F('{$filterable}');";
			$filterJS.="\nvar {$filterable}_change_by_{$filterer} = function(){";
			$filterJS.="\n\t$('{$filterable}').options.length=0;";
			$filterJS.="\n\t$('{$filterable}').options[0] = new Option();";
			$filterJS.="\n\tif(\$F('{$filterer}')){";
			$filterJS.="\n\t\tfor({$filterable}_item in {$filterable}_data_by_{$filterer}[\$F('{$filterer}')]){";
			$filterJS.="\n\t\t\t$('{$filterable}').options[$('{$filterable}').options.length] = new Option(";
			$filterJS.="\n\t\t\t\t{$filterable}_data_by_{$filterer}[\$F('{$filterer}')][{$filterable}_item],";
			$filterJS.="\n\t\t\t\t{$filterable}_item,";
			$filterJS.="\n\t\t\t\t({$filterable}_item == selected_{$filterable} ? true : false),";
			$filterJS.="\n\t\t\t\t({$filterable}_item == selected_{$filterable} ? true : false)";
			$filterJS.="\n\t\t\t);";
			$filterJS.="\n\t\t}";
			$filterJS.="\n\t}else{";
			$filterJS.="\n\t\tfor({$filterable}_item in {$filterable}_data){";
			$filterJS.="\n\t\t\t$('{$filterable}').options[$('{$filterable}').options.length] = new Option(";
			$filterJS.="\n\t\t\t\t{$filterable}_data[{$filterable}_item],";
			$filterJS.="\n\t\t\t\t{$filterable}_item,";
			$filterJS.="\n\t\t\t\t({$filterable}_item == selected_{$filterable} ? true : false),";
			$filterJS.="\n\t\t\t\t({$filterable}_item == selected_{$filterable} ? true : false)";
			$filterJS.="\n\t\t\t);";
			$filterJS.="\n\t\t}";
			$filterJS.="\n\t}";
			$filterJS.="\n\t$('{$filterable}').highlight();";
			$filterJS.="\n};";
			$filterJS.="\n$('{$filterer}').observe('change', function(){ window.setTimeout({$filterable}_change_by_{$filterer}, 25); });";
			$filterJS.="\n";
		}

		$filterableCombo = new Combo;
		$filterableCombo->ListType = 0;
		$filterableCombo->ListItem = array_slice(array_values($filterableData), 0, 10);
		$filterableCombo->ListData = array_slice(array_keys($filterableData), 0, 10);
		$filterableCombo->SelectName = $filterable;
		$filterableCombo->AllowNull = true;

		return $filterJS;
	}
	#########################################################
	function br2nl($text){
		return  preg_replace('/\<br(\s*)?\/?\>/i', "\n", $text);
	}

