<?php
	// check this file's MD5 to make sure it wasn't called before
	$prevMD5=@implode('', @file(dirname(__FILE__).'/setup.md5'));
	$thisMD5=md5(@implode('', @file("./updateDB.php")));
	if($thisMD5==$prevMD5){
		$setupAlreadyRun=true;
	}else{
		// set up tables
		if(!isset($silent)){
			$silent=true;
		}

		// set up tables
		setupTable('resources', "create table if not exists `resources` ( `Id` INT unsigned not null auto_increment , primary key (`Id`), `Name` VARCHAR(40) , `Available` VARCHAR(40) default '1' ) CHARSET latin1", $silent);
		setupTable('projects', "create table if not exists `projects` ( `Id` INT unsigned not null auto_increment , primary key (`Id`), `Name` VARCHAR(40) , `StartDate` DATE , `EndDate` DATE ) CHARSET latin1", $silent);
		setupTable('assignments', "create table if not exists `assignments` ( `Id` INT unsigned not null auto_increment , primary key (`Id`), `ProjectId` INT unsigned , `ProjectDuration` INT unsigned , `ResourceId` INT unsigned , `Commitment` FLOAT(3,2) not null default '1.00' , `StartDate` DATE , `EndDate` DATE ) CHARSET latin1", $silent);
		setupIndexes('assignments', array('ProjectId','ResourceId'));


		// save MD5
		if($fp=@fopen(dirname(__FILE__).'/setup.md5', 'w')){
			fwrite($fp, $thisMD5);
			fclose($fp);
		}
	}


	function setupIndexes($tableName, $arrFields){
		if(!is_array($arrFields)){
			return false;
		}

		foreach($arrFields as $fieldName){
			if(!$res=@mysql_query("SHOW COLUMNS FROM `$tableName` like '$fieldName'")){
				continue;
			}
			if(!$row=@mysql_fetch_assoc($res)){
				continue;
			}
			if($row['Key']==''){
				@mysql_query("ALTER TABLE `$tableName` ADD INDEX `$fieldName` (`$fieldName`)");
			}
		}
	}


	function setupTable($tableName, $createSQL='', $silent=true, $arrAlter=''){
		global $Translation;
		ob_start();

		echo "<div style=\"padding: 5px; border-bottom:solid 1px silver; font-family: verdana, arial; font-size: 10px;\">";

		// is there a table rename query?
		if(is_array($arrAlter)){
			$matches=array();
			if(preg_match("/ALTER TABLE `(.*)` RENAME `$tableName`/", $arrAlter[0], $matches)){
				$oldTableName=$matches[1];
			}
		}

		if($res=@mysql_query("select count(1) from `$tableName`")){ // table already exists
			if($row=@mysql_fetch_array($res)){
				echo str_replace("<TableName>", $tableName, str_replace("<NumRecords>", $row[0],$Translation["table exists"]));
				if(is_array($arrAlter)){
					echo '<br />';
					foreach($arrAlter as $alter){
						if($alter!=''){
							echo "$alter ... ";
							if(!@mysql_query($alter)){
								echo "<font color=red>".$Translation["failed"]."</font><br />";
								echo "<font color=red>".$Translation["mysql said"]." ".mysql_error()."</font><br />";
							}else{
								echo "<font color=green>".$Translation["ok"]."</font><br />";
							}
						}
					}
				}else{
					echo $Translation["table uptodate"];
				}
			}else{
				echo str_replace("<TableName>", $tableName, $Translation["couldnt count"]);
			}
		}else{ // given tableName doesn't exist
		
			if($oldTableName!=''){ // if we have a table rename query
				if($ro=@mysql_query("select count(1) from `$oldTableName`")){ // if old table exists, rename it.
					$renameQuery=array_shift($arrAlter); // get and remove rename query

					echo "$renameQuery ... ";
					if(!@mysql_query($renameQuery)){
						echo "<font color=red>".$Translation["failed"]."</font><br />";
						echo "<font color=red>".$Translation["mysql said"]." ".mysql_error()."</font><br />";
					}else{
						echo "<font color=green>".$Translation["ok"]."</font><br />";
					}

					if(is_array($arrAlter)) setupTable($tableName, $createSQL, false, $arrAlter); // execute Alter queries on renamed table ...
				}else{ // if old tableName doesn't exist (nor the new one since we're here), then just create the table.
					setupTable($tableName, $createSQL, false); // no Alter queries passed ...
				}
			}else{ // tableName doesn't exist and no rename, so just create the table
				echo str_replace("<TableName>", $tableName, $Translation["creating table"]);
				if(!@mysql_query($createSQL)){
					echo "<font color=red>".$Translation["failed"]."</font><br />";
					echo "<font color=red>".$Translation["mysql said"].mysql_error()."</font>";
				}else{
					echo "<font color=green>".$Translation["ok"]."</font>";
				}
			}
		}

		echo "</div>";

		$out=ob_get_contents();
		ob_end_clean();
		if(!$silent){
			echo $out;
		}
	}
?>