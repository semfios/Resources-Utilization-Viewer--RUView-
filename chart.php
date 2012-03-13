<?php
	// config
	$chart = array(
		'dayWidth' => 3,
		'resourceHeight' => 40,
		'left' => 150,
		'top' => 90,
		'resourceSeparator' => 2,
		'colors' => array(
			'BurlyWood', 'DarkBlue', 'DarkOrange', 'DarkCyan', 'Aqua', 'Beige', 'Chartreuse', 'Coral', 'DarkKhaki', 'DarkViolet', 'DodgerBlue', 'Gainsboro', 'Gold', 'GreenYellow', 'LightSalmon', 'Olive', 'PaleTurquoise', 'SlateGrey'
		)
	);

	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/lib.php");
	
	$t1=microtime(true);

	// chart parameters
	$year=intval($_GET['year']);
	if(!$year)	$year=date('Y');
	$startDate="$year-01-01";
	$endDate="$year-12-31";
	
	// get projects
	$res=sql("select Id, Name from projects", $eo);
	$i=0;
	while($row=mysql_fetch_row($res)){
		$project[$row[0]] = $row[1];
		$projectColor[$row[0]] = $chart['colors'][$i++];
	}
	
	// get resources
	$res=sql("select Id, Name from resources", $eo);
	while($row=mysql_fetch_row($res)){
		$resource[$row[0]] = $row[1];
	}
	
	// get assignments for open projects for selected year
	$assignment = array();
	$res=sql("select * from assignments where StartDate<='$endDate' and EndDate>='$startDate'", $eo);
	while($row=mysql_fetch_assoc($res)){
		$assignment[] = array(
			'Id' => $row['Id'],
			'ProjectId' => $row['ProjectId'],
			'ResourceId' => $row['ResourceId'],
			'StartTS' => strtotime($row['StartDate']),
			'EndTS' => strtotime($row['EndDate']),
			'Commitment' => $row['Commitment']
		);
		$resourceProject[$row['ResourceId']][$row['ProjectId']] = count($resourceProject[$row['ResourceId']]);
	}
	
	$t2=microtime(true);
	
	
	
	/*******************************************************
				View code begins below
	*******************************************************/
	
	include("$d/header.php");
	
	// Years navigator
	$prevYear = $year - 1;
	$nextYear = $year + 1;
	?>
	<div id="years-nav">
		<a href="chart.php?year=<?php echo $prevYear; ?>"><?php echo $prevYear; ?></a>
		<?php echo $year; ?>
		<a href="chart.php?year=<?php echo $nextYear; ?>"><?php echo $nextYear; ?></a>
	</div>
	<?
	
	// Display month grid lines
	$prevLeft = $chart['left'];
	$thisMonth = date('n');
	$thisYear = date('Y');
	for($m=1; $m<=12; $m++){
		$daysPerMonth = date('t', strtotime("$year-$m-01"));
		?>
		<div
			style="
				position: absolute;
				left: <?=$prevLeft?>px;
				height: <?=((count($resource) + 1) * ($chart['resourceHeight'] + $chart['resourceSeparator']))?>px;
				border-left: dotted 1px Silver;
				<? if($m==12){ ?>border-right: dotted 1px Silver;<? } ?>
				top: <?=($chart['top'] + $chart['resourceHeight'] + $chart['resourceSeparator'])?>px;
				text-align: center;
				font-family: Arial; font-size: 10px; font-weight: bold;
				width: <?=($daysPerMonth * $chart['dayWidth'])?>px;
			">
			<?=date('M Y', strtotime("$year-$m-01"))?>
		</div>
		<?
		
		// today line
		if($year==$thisYear && $m==$thisMonth){
			?>
			<div
				title="Today, <?=date('j/n/Y')?>"
				style="
					border-left: solid 2px DarkRed;
					position: absolute;
					top: <?=($chart['top'] + ($chart['resourceHeight'] + $chart['resourceSeparator']) * 1.5)?>px;
					left: <?=($prevLeft + (date('j') - 1) * $chart['dayWidth'])?>px;
					height: <?=((count($resource) + 0.5) * ($chart['resourceHeight'] + $chart['resourceSeparator']))?>px;
					z-index: 2;
				"></div>
			<?
		}
		
		$prevLeft += $daysPerMonth * $chart['dayWidth'];
	}
	
	
	// Display resource names
	foreach($resource as $ResourceId => $ResourceName){
		?><div
			style="
				position: absolute;
				top: <?=($chart['resourceHeight'] * $ResourceId + $chart['top'] + $ResourceId * $chart['resourceSeparator'])?>px;
				border-bottom: solid 1px Silver;
				width: 98%;
				height: <?=intval($chart['resourceHeight'])?>px;
				font-family: Arial;
			">
				<a href="resources_view.php?SelectedID=<?=$ResourceId?>" style="text-decoration: none; color: DarkBlue;"><?=$ResourceName?></a>
		</div>
		<?
	}

	
	// Display project assignment bars
	$yearStartTS = strtotime(date("$year-01-01"));
	$yearEndTS = strtotime(date("$year-12-31 23:59:59"));
	foreach($assignment as $assDetails){
		$chartStartTS = max($assDetails['StartTS'], $yearStartTS);
		$chartEndTS = min($assDetails['EndTS'], $yearEndTS);
		?><div
			style="
				position: absolute;
				width: <?=intval(($chartEndTS - $chartStartTS + 86400) / 86400 * $chart['dayWidth'])?>px;
				left: <?=intval(($chartStartTS - strtotime("$year-01-01")) / 86400 * $chart['dayWidth'] + $chart['left'])?>px;
				height: <?=intval($chart['resourceHeight'] * $assDetails['Commitment'])?>px;
				background-color: <?=$projectColor[$assDetails['ProjectId']]?>;
				top: <?=($chart['resourceHeight'] * $assDetails['ResourceId'] + $chart['top'] + $assDetails['ResourceId'] * $chart['resourceSeparator'] + ($assDetails['Commitment'] < 1 ? ($resourceProject[$assDetails['ResourceId']][$assDetails['ProjectId']] ? (1-$assDetails['Commitment']) * $chart['resourceHeight'] : 0) : 0))?>px;
				cursor: pointer;
				text-align: center;
				font-size: 8px;
				font-family: Arial;
				color: white;
				font-weight: bold;
			"
			title="<?=($project[$assDetails['ProjectId']].': '.$resource[$assDetails['ResourceId']])?>. <?=($assDetails['Commitment'] * 100)?>% commitment from <?=date('j/n/Y', $assDetails['StartTS'])?> to <?=date('j/n/Y', $assDetails['EndTS'])?>"
			onclick="window.location='assignments_view.php?SelectedID=<?=$assDetails['Id']?>';">
				<?=$project[$assDetails['ProjectId']]?>
		</div>
		<?
	}

	include("$d/footer.php");
