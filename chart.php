<?php
	// config
	$chart = array(
		'dayWidth' => 3,
		'resourceHeight' => 40,
		'left' => 150,
		'top' => 90,
		'resourceSeparator' => 2,
		'colors' => array(
			'BurlyWood', 'DarkBlue', 'DarkOrange', 'DarkCyan', 'Aqua', 'CadetBlue', 'Chartreuse', 'Coral', 'DarkKhaki', 'DarkViolet', 'DodgerBlue', 'Gainsboro', 'Gold', 'GreenYellow', 'LightSalmon', 'Olive', 'PaleTurquoise', 'SlateGrey'
		)
	);

	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/lib.php");
	
	$t1=microtime(true);
	
	// some initilization
	$project = $projectColor = $resource = $unavailableResource = $assignment = array();

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
	$res=sql("select Id, Name, Available from resources", $eo);
	while($row=mysql_fetch_row($res)){
		$resource[$row[0]] = $row[1];
		if(!$row[2])	$unavailableResource[$row[0]] = $row[1];
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
	
	<!-- refresh content every minute -->
	<META HTTP-EQUIV="Refresh" CONTENT="60;url=chart.php?year=<?php echo $year; ?>">
	
	<div id="years-nav">
		<a href="chart.php?year=<?php echo $prevYear; ?>"><?php echo $prevYear; ?></a>
		<?php echo $year; ?>
		<a href="chart.php?year=<?php echo $nextYear; ?>"><?php echo $nextYear; ?></a>
	</div>
	<?php
	
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
				left: <?php echo $prevLeft; ?>px;
				height: <?php echo ((count($resource) + 1) * ($chart['resourceHeight'] + $chart['resourceSeparator'])); ?>px;
				border-left: dotted 1px Silver;
				<?php if($m==12){ ?>border-right: dotted 1px Silver;<?php } ?>
				top: <?php echo ($chart['top'] + $chart['resourceHeight'] + $chart['resourceSeparator']); ?>px;
				text-align: center;
				font-family: Arial; font-size: 10px; font-weight: bold;
				width: <?php echo ($daysPerMonth * $chart['dayWidth']); ?>px;
			">
			<?php echo date('M Y', strtotime("$year-$m-01")); ?>
		</div>
		<?php
		
		// today line
		if($year==$thisYear && $m==$thisMonth){
			?>
			<div
				title="Today, <?php echo date('j/n/Y'); ?>"
				style="
					border-left: solid 2px DarkRed;
					position: absolute;
					top: <?php echo ($chart['top'] + ($chart['resourceHeight'] + $chart['resourceSeparator']) * 1.5); ?>px;
					left: <?php echo ($prevLeft + (date('j') - 1) * $chart['dayWidth']); ?>px;
					height: <?php echo ((count($resource) + 0.5) * ($chart['resourceHeight'] + $chart['resourceSeparator'])); ?>px;
					z-index: 2;
				"></div>
			<?php
		}
		
		$prevLeft += $daysPerMonth * $chart['dayWidth'];
	}
	
	
	// Display resource names
	foreach($resource as $ResourceId => $ResourceName){
		$available = (array_key_exists($ResourceId, $unavailableResource) ? false : true);
		?><div
			style="
				position: absolute;
				top: <?php echo ($chart['resourceHeight'] * ($ResourceId + 1) + $chart['top'] + $ResourceId * $chart['resourceSeparator']); ?>px;
				border-bottom: solid 1px Silver;
				width: 98%;
				height: <?php echo intval($chart['resourceHeight']); ?>px;
				font-family: Arial;
				font-size: 12px;
			">
				<a
					href="resources_view.php?SelectedID=<?php echo $ResourceId; ?>"
					style="
						text-decoration: <?php echo ($available ? 'none' : 'line-through'); ?>;
						color: <?php echo ($available ? 'DarkBlue' : 'Silver'); ?>;
					"><?php echo $ResourceName; ?></a>
		</div>
		<?php
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
				width: <?php echo intval(($chartEndTS - $chartStartTS + 86400) / 86400 * $chart['dayWidth']); ?>px;
				left: <?php echo intval(($chartStartTS - strtotime("$year-01-01")) / 86400 * $chart['dayWidth'] + $chart['left']); ?>px;
				height: <?php echo intval($chart['resourceHeight'] * $assDetails['Commitment']); ?>px;
				background-color: <?php echo $projectColor[$assDetails['ProjectId']]; ?>;
				top: <?php echo ($chart['resourceHeight'] * ($assDetails['ResourceId'] + 1) + $chart['top'] + $assDetails['ResourceId'] * $chart['resourceSeparator'] + ($assDetails['Commitment'] < 1 ? ($resourceProject[$assDetails['ResourceId']][$assDetails['ProjectId']] ? (1-$assDetails['Commitment']) * $chart['resourceHeight'] : 0) : 0)); ?>px;
				cursor: pointer;
				text-align: center;
				font-size: 10px;
				font-family: Arial;
				color: Black;
				font-weight: bold;
				opacity: 0.5;
				filter:alpha(opacity=50);
				white-space:nowrap;
				overflow:hidden;
			"
			title="<?php echo ($project[$assDetails['ProjectId']].': '.$resource[$assDetails['ResourceId']]); ?>. <?php echo ($assDetails['Commitment'] * 100); ?>% commitment from <?php echo date('j/n/Y', $assDetails['StartTS']); ?> to <?php echo date('j/n/Y', $assDetails['EndTS']); ?>"
			onclick="window.location='assignments_view.php?SelectedID=<?php echo $assDetails['Id']; ?>';">
				<?php echo $project[$assDetails['ProjectId']]; ?>
		</div>
		<?php
	}

	include("$d/footer.php");
