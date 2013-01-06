<?php if(!isset($Translation)){ @header('Location: index.php'); exit; } ?>
<?php include("$currDir/header.php"); ?>
<?php @include("$currDir/hooks/links-home.php"); ?>

<?php
	/*
		Possible bg color classes, as defined in "metro.css":
			whitebg, blackbg, bluebg, brownbg, greenbg, limebg, magentabg, orangebg, pinkbg, purplebg, redbg, tealbg
		Possible text color classes, as defined in "metro.css":
			accent, white, black, blue, brown, green, lime, magenta, orange, pink, purple, red, teal, lightgray
	*/
	// class(es) of first block
	$classesFirstBlock = 'two-v orangebg';
	$classesFirstBlockText = 'white';
	// class(es) of other blocks
	$classesOtherBlock = 'one bluebg';
	$classesOtherBlockText = 'lightgray';
?>

<link rel="stylesheet" type="text/css" href="metro.css" />
<div  style="max-width: 94%; margin: 20px auto;">
	<div class="tiles">
	<?php
		if(is_array($arrTables)){
			$i=0;
			foreach($arrTables as $tn=>$tc){
				$tChkFF = array_search($tn, array());
				$tChkHL = array_search($tn, array());
				if($tChkHL !== false && $tChkHL !== null) continue;

				$searchFirst = (($tChkFF !== false && $tChkFF !== null) ? '?Filter_x=1' : '');
				?>
				<div onclick="window.location='<?php echo $tn; ?>_view.php<?php echo $searchFirst; ?>';" id="<?php echo $tn; ?>-tile" class="tile <?php echo (!$i ? $classesFirstBlock : $classesOtherBlock); ?>">
					<a class="<?php echo (!$i ? $classesFirstBlockText : $classesOtherBlockText); ?>" title="<?php echo preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", htmlspecialchars($tc[1])); ?>" href="<?php echo $tn; ?>_view.php<?php echo $searchFirst; ?>">
						<?php echo (!$i ? "<h1>{$tc[0]}</h1>" : "<h2>{$tc[0]}</h2>"); ?>
						<?php echo $tc[1]; ?>
					</a>
				</div>
				<?php
				$i++;
			}
		}else{
			?><div id="error-no-access" class="tile two-v two-h redbg"><?php echo $Translation['no table access']; ?>			<script language="javaScript">setTimeout("window.location='index.php?signOut=1'", 2000);</script>			</div><?php
		}

		// custom home links, as defined in "hooks/links-home.php"
		if(is_array($homeLinks)){
			$memberInfo = getMemberInfo();
			foreach($homeLinks as $link){
				if(!isset($link['url']) || !isset($link['title'])) continue;
				if($memberInfo['admin'] || @in_array($memberInfo['group'], $link['groups']) || @in_array('*', $link['groups'])){
					?>
					<div onclick="window.location='<?php echo $link['url']; ?>';" class="tile <?php echo ($link['tileClasses'] ? $link['tileClasses'] : $classesOtherBlock); ?>">
						<a class="<?php echo ($link['textClasses'] ? $link['textClasses'] : $classesOtherBlockText); ?>" title="<?php echo htmlspecialchars($link['description']); ?>" href="<?php echo $link['url']; ?>">
							<h2><?php echo $link['title']; ?></h2>
							<?php echo $link['description']; ?>
						</a>
					</div>
					<?php
				}
			}
		}

		if(getLoggedAdmin()){
			?><div onclick="window.location='admin/';" id="admin-link-tile" class="tile one magentabg"><a href="admin/"><h2 style="margin-top: 45px"><?php echo $Translation['admin area']; ?></h2></a></div><?php
		}
	?>
	</div>
</div>

	<div style="clear: both;"></div><br/><br/>
	<div class="TableFooter">
		<?php echo $Translation['powered by']; ?> <a href="http://bigprof.com/appgini/">AppGini 5.00</a>
	</div>

</div>

<?php include("$currDir/footer.php"); ?>