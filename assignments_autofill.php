<?php
// This script and data application were generated by AppGini 5.41
// Download AppGini for free from http://bigprof.com/appgini/download/

	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");

	header("Content-type: text/javascript; charset=UTF-8");

	$mfk=$_GET['mfk'];
	$id=makeSafe($_GET['id']);
	$rnd1=intval($_GET['rnd1']); if(!$rnd1) $rnd1='';

	if(!$mfk){
		die('// no js code available!');
	}

	switch($mfk){

		case 'ProjectId':
			if(!$id){
				?>
				$('ProjectDuration<?php echo $rnd1; ?>').innerHTML='&nbsp;';
				<?php
				break;
			}
			$res = sql("SELECT `projects`.`Id` as 'Id', `projects`.`Name` as 'Name', if(`projects`.`StartDate`,date_format(`projects`.`StartDate`,'%d/%m/%Y'),'') as 'StartDate', if(`projects`.`EndDate`,date_format(`projects`.`EndDate`,'%d/%m/%Y'),'') as 'EndDate' FROM `projects`  WHERE `projects`.`Id`='$id' limit 1", $eo);
			$row = db_fetch_assoc($res);
			?>
			$j('#ProjectDuration<?php echo $rnd1; ?>').html('<?php echo addslashes(str_replace(array("\r", "\n"), '', nl2br($row['StartDate'].' <b>to</b> '.$row['EndDate']))); ?>&nbsp;');
			<?php
			break;


	}

?>