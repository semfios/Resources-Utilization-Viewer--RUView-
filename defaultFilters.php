<?php if(!isset($Translation)){ @header('Location: index.php'); exit; } ?>
<table border="0" align="center">
	<tr><td colspan="4" class="TableTitle"><span id="table-title-img"><img align="top" src="<?php echo $this->TableIcon; ?>" /></span> <?php echo $this->TableTitle . " " . $Translation['filters']; ?></td></tr>
	<tr>
		<td class="TableHeader"></td>
		<td class="TableHeader"><?php echo $Translation['filtered field']; ?></td>
		<td class="TableHeader"><?php echo $Translation['comparison operator']; ?></td>
		<td class="TableHeader"><?php echo $Translation['comparison value']; ?></td>
	</tr>
	<tr><td colspan="4" class="TableHeader"></td></tr>
	<?php
		for($i = 1; $i <= (3 * $FiltersPerGroup); $i++){ // Number of filters allowed
			$fields = '';
			$operators = '';

			if(($i % $FiltersPerGroup == 1) && $i != 1){
				$seland = new Combo;
				$seland->ListItem = array($Translation["or"], $Translation["and"]);
				$seland->ListData = array("or", "and");
				$seland->SelectName = "FilterAnd[$i]";
				$seland->SelectedData = $FilterAnd[$i];
				$seland->Render();
				?>
				<tr class="FilterSet<?php echo ($i - 1); ?>"><td colspan="4" class="TableHeader"></td></tr>
				<tr class="FilterSet<?php echo ($i - 1); ?>"><td colspan="4" align="center"><?php echo $seland->HTML; ?></td></tr>
				<tr class="FilterSet<?php echo $i; ?>"><td colspan="4" class="TableHeader"></td></tr>
			<?php } ?>

			<tr class="FilterSet<?php echo $i; ?>">
				<td class="TableHeader" style="text-align:left;">&nbsp;<?php echo $Translation["filter"] . sprintf(" %02d", $i); ?>
					<?php
						// And, Or select
						if($i % $FiltersPerGroup != 1){
							$seland = new Combo;
							$seland->ListItem = array($Translation["and"], $Translation["or"]);
							$seland->ListData = array("and", "or");
							$seland->SelectName = "FilterAnd[$i]";
							$seland->SelectedData = $FilterAnd[$i];
							$seland->Render();
							echo $seland->HTML;
						}
					?>
				</td>
				<?php
					// Fields list
					$selfields = new Combo;
					$selfields->SelectName = "FilterField[$i]";
					$selfields->SelectedData = $FilterField[$i];
					$selfields->ListItem = array_values($this->QueryFieldsFilters);
					$selfields->ListData = array_keys($this->QueryFieldsIndexed);
					$selfields->Render();
				?>
				<td><?php echo $selfields->HTML; ?></td>
				<?php
					// Operators list
					$selop = new Combo;
					$selop->ListItem = array($Translation["equal to"], $Translation["not equal to"], $Translation["greater than"], $Translation["greater than or equal to"], $Translation["less than"], $Translation["less than or equal to"] , $Translation["like"] , $Translation["not like"], $Translation["is empty"], $Translation["is not empty"]);
					$selop->ListData = array("<=>", "!=", ">", ">=", "<", "<=", "like", "not like", "isEmpty", "isNotEmpty");
					$selop->SelectName = "FilterOperator[$i]";
					$selop->SelectedData = $FilterOperator[$i];
					$selop->Render();
				?>
				<td><?php echo $selop->HTML; ?></td>
				<?php /* Comparison expression */ ?>
				<td><input size="25" name="FilterValue[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($FilterValue[$i], ENT_QUOTES); ?>" class="TextBox"></td>
			</tr>
			<?php if(!$i % $FiltersPerGroup){ ?>
				<tr class="FilterSet<?php echo $i; ?>"><td colspan="4" class="TableHeader"></td></tr>
			<?php } ?>
		<?php } ?>
	<tr class="FilterSet<?php echo (3 * $FiltersPerGroup); ?>"><td colspan="4" class="TableHeader"></td></tr>

	<tr><td colspan="4">&nbsp;</td></tr>
	<tr><td colspan="4" class="TableHeader"><?php echo $Translation['order by']; ?></td></tr>
	<?php
		// Fields list
		$sortFields = new Combo;
		$sortFields->ListItem = $this->ColCaption;
		$sortFields->ListData = $this->ColNumber;

		// sort direction
		$sortDirs = new Combo;
		$sortDirs->ListItem = array($Translation['ascending'], $Translation['descending']);
		$sortDirs->ListData = array('asc', 'desc');
	?>
	<?php for($i = 0; $i < min(maxSortBy, count($this->ColCaption)); $i++){ ?>
		<?php
			$sfi=$sd='';
			if(isset($orderBy[$i])) foreach($orderBy[$i] as $sfi => $sd);

			$sortFields->SelectName = "OrderByField$i";
			$sortFields->SelectID = "OrderByField$i";
			$sortFields->SelectedData = $sfi;
			$sortFields->SelectedText = '';
			$sortFields->Render();

			$sortDirs->SelectName = "OrderDir$i";
			$sortDirs->SelectID = "OrderDir$i";
			$sortDirs->SelectedData = $sd;
			$sortDirs->SelectedText = '';
			$sortDirs->Render();
		?>
		<tr>
			<td class="TableBodyNumeric"><b><?php echo ($i ? $Translation['then by'] : $Translation['order by']); ?></b></td>
			<td class="TableBody"><?php echo $sortFields->HTML; ?></td>
			<td class="TableBody" colspan="2"><?php echo $sortDirs->HTML; ?></td>
		</tr>
	<?php } ?>
	<tr><td colspan="4" class="TableHeader"></td></tr>
	<tr class="filterByOwnership"><td colspan="4">&nbsp;</td></tr>
	<tr class="filterByOwnership">
		<td colspan="4" class="TableHeader"><?php echo $Translation['Records to display']; ?></td>
	</tr>
	<tr class="filterByOwnership">
		<td colspan="4" class="TableBody">
			<input type="radio" name="DisplayRecords" id="DisplayRecordsUser" value="user"/>
				<label for="DisplayRecordsUser"><?php echo $Translation['Only your own records']; ?></label>
		</td>
	</tr>
	<tr class="filterByOwnership">
		<td colspan="4" class="TableBody">
			<input type="radio" name="DisplayRecords" id="DisplayRecordsGroup" value="group"/>
				<label for="DisplayRecordsGroup"><?php echo $Translation['All records owned by your group']; ?></label>
		</td>
	</tr>
	<tr class="filterByOwnership">
		<td colspan="4" class="TableBody">
			<input type="radio" name="DisplayRecords" id="DisplayRecordsAll" checked value="all"/>
				<label for="DisplayRecordsAll"><?php echo $Translation['All records']; ?></label>
		</td>
	</tr>
	<tr class="filterByOwnership"><td colspan="4" class="TableHeader"></td></tr>
	<tr>
		<td colspan="4" class="buttons" id="topButtons">
			<button type="submit" id="applyFilters" style="float: right;" class="positive"><img src="search.gif" /><?php echo $Translation['apply filters']; ?></button>
			<?php if($this->AllowSavingFilters){ ?>
				<button type="submit" class="positive" id="SaveFilter" name="SaveFilter_x" value="1" style="width: 185px; float: right;"><img src="update.gif" /><?php echo $Translation['save filters']; ?></button>
			<?php } ?>
			<button onclick="$$('form')[0].reset();" type="submit" id="cancelFilters" style="float: right;" class="negative"><img src="deselect.gif" /><?php echo $Translation['Cancel']; ?></button>
		</td>
	</tr>
</table>

<script>
	var FiltersPerGroup = <?php echo $FiltersPerGroup; ?>;

	function filterGroupDisplay(groupIndex, hide, animate){
		for(i = ((groupIndex - 1) * FiltersPerGroup + 1); i <= (groupIndex * FiltersPerGroup); i++){
			if(animate){
				$$('tr.FilterSet' + i).invoke((hide ? 'fade' : 'appear'), { duration: 1.0 });
			}else{
				$$('tr.FilterSet' + i).invoke(hide ? 'hide' : 'show');
			}
		}
		if(!hide){
			setTimeout(function(){
				$('FilterField[' + ((groupIndex - 1) * FiltersPerGroup + 1) + ']').focus();
			}, 1005);
		}
	}

	document.observe("dom:loaded", function(){
		for(i = (FiltersPerGroup + 1); i <= (3 * FiltersPerGroup); i++){
			$$('tr.FilterSet' + i).invoke('hide');
		}
		$('FilterAnd[' + (FiltersPerGroup + 1) + ']').observe('change', function(){
			filterGroupDisplay(2, (this.value ? false : true), true);
		});
		$('FilterAnd[' + (2 * FiltersPerGroup + 1) + ']').observe('change', function(){
			filterGroupDisplay(3, (this.value ? false : true), true);
		});

		if($F('FilterAnd[' + (    FiltersPerGroup + 1) + ']')){ filterGroupDisplay(2); }
		if($F('FilterAnd[' + (2 * FiltersPerGroup + 1) + ']')){ filterGroupDisplay(3); }

		var DisplayRecords = '<?php echo $_REQUEST['DisplayRecords']; ?>';
		var isAnonymous = <?php $mi = getMemberInfo(); echo ($mi['group'] == $adminConfig['anonymousGroup'] ? 'true' : 'false'); ?>;

		if(isAnonymous) $$('tr.filterByOwnership').invoke('hide');

		switch(DisplayRecords){
			case 'user':
				$('DisplayRecordsUser').checked=true;
				break;
			case 'group':
				$('DisplayRecordsGroup').checked=true;
				break;
			default:
				$('DisplayRecordsAll').checked=true;
		}
	});
</script>