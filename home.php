<?php if(!isset($Translation)){ @header('Location: index.php'); exit; } ?>
<?php include("$d/header.php"); ?>

<div align="center">
	<table cellpadding="8">
<?php
	if(is_array($arrTables)){
		if(getLoggedAdmin()){
			?><tr><td colspan="<?php echo ($tablesPerRow*3-1); ?>" class="TableTitle" style="text-align: center;"><a href="admin/"><img src=table.gif border=0 align="top"></a> <a href="admin/" class="TableTitle" style="color: red;"><?php echo $Translation['admin area']; ?></a><br /><br /></td></tr><?php
		}
		$i=0;
		foreach($arrTables as $tn=>$tc){
			$tChk=array_search($tn, array());
			if($tChk!==false && $tChk!==null){
				$searchFirst='?Filter_x=1';
			}else{
				$searchFirst='';
			}
			if(!$i % $tablesPerRow){ echo '<tr>'; }
			?><td valign="top"><a href=<?php echo $tn; ?>_view.php<?php echo $searchFirst; ?>><img src=<?php echo $tc[2];?> border=0></a></td><td valign="top" align="left"><a href=<?php echo $tn; ?>_view.php<?php echo $searchFirst; ?> class="TableTitle"><?php echo $tc[0]; ?></a><br /><?php echo $tc[1]; ?></td><?php
			if($i % $tablesPerRow == ($tablesPerRow - 1)){ echo '</tr>'; }else{ echo '<td width="50">&nbsp;</td>'; }
			$i++;
		}
	}else{
		?><tr><td><div class="Error"><?php echo $Translation['no table access']; ?><script language="javaScript">setInterval("window.location='index.php?signOut=1'", 2000);</script></div></td></tr><?php
	}
?>
	</table>
	<br /><br />
	
	<div class="TableFooter">
		<b><a href=http://bigprof.com/appgini/>BigProf Software</a> - <?php echo $Translation['powered by']; ?> AppGini 4.70</b>
	</div>

</div>

<?php include("$d/footer.php"); ?>