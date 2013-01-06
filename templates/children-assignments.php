<script>
	assignmentsGetChildrenRecordsList = function(command){
		var param = {
			ChildTable: "<?php echo $parameters['ChildTable']; ?>",
			ChildLookupField: "<?php echo $parameters['ChildLookupField']; ?>",
			SelectedID: "<?php echo addslashes($parameters['SelectedID']); ?>",
			Page: <?php echo addslashes($parameters['Page']); ?>,
			SortBy: <?php echo ($parameters['SortBy'] === false ? '""' : $parameters['SortBy']); ?>,
			SortDirection: "<?php echo $parameters['SortDirection']; ?>"
		};
		var panelID = "panel_<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}"; ?>";
		var mbWidth = window.innerWidth * 0.9;
		var mbHeight = window.innerHeight * 0.8;
		if(mbWidth > 1000){ mbWidth = 1000; }
		if(mbHeight > 800){ mbHeight = 800; }

		switch(command.Verb){
			case 'sort': /* order by given field index in 'SortBy' */
				post("parent-children.php", {
					ChildTable: param.ChildTable,
					ChildLookupField: param.ChildLookupField,
					SelectedID: param.SelectedID,
					Page: param.Page,
					SortBy: command.SortBy,
					SortDirection: command.SortDirection,
					Operation: "get-records"
				}, panelID, undefined, 'pc-loading');
				break;
			case 'page': /* next or previous page as provided by 'Page' */
				if(command.Page.toLowerCase() == 'next'){ command.Page = param.Page + 1; }
				else if(command.Page.toLowerCase() == 'previous'){ command.Page = param.Page - 1; }

				if(command.Page < 1 || command.Page > <?php echo ceil($totalMatches / $config['records-per-page']); ?>){ return; }
				post("parent-children.php", {
					ChildTable: param.ChildTable,
					ChildLookupField: param.ChildLookupField,
					SelectedID: param.SelectedID,
					Page: command.Page,
					SortBy: param.SortBy,
					SortDirection: param.SortDirection,
					Operation: "get-records"
				}, panelID, undefined, 'pc-loading');
				break;
			case 'new': /* new record */
				var url = $F(param.ChildTable + '_hclink') + '&addNew_x=1&Embedded=1';
				Modalbox.show('<iframe src="' + url + '" seamless="seamless" width="' + (mbWidth - 40) + '" height="' + (mbHeight - 50) + '" sandbox="allow-forms allow-scripts allow-same-origin"></iframe>', {
					loadingString: '<?php echo addslashes($Translation['Loading ...']); ?>',
					afterHide: function(){ assignmentsGetChildrenRecordsList({ Verb: 'reload' }); },
					width: mbWidth,
					height: mbHeight,
					title: '<?php echo addslashes("{$config['tab-label']}: {$Translation['Add New']}"); ?>'
				});
				break;
			case 'open': /* opens the detail view for given child record PK provided in 'ChildID' */
				var url = '<?php echo "{$parameters['ChildTable']}_view.php?Embedded=1&SelectedID="; ?>' + escape(command.ChildID);
				Modalbox.show('<iframe src="' + url + '" seamless="seamless" width="' + (mbWidth - 40) + '" height="' + (mbHeight - 50) + '" sandbox="allow-forms allow-scripts allow-same-origin"></iframe>', {
					loadingString: '<?php echo addslashes($Translation['Loading ...']); ?>',
					afterHide: function(){ assignmentsGetChildrenRecordsList({ Verb: 'reload' }); },
					width: mbWidth,
					height: mbHeight,
					title: '<?php echo addslashes($config['tab-label']); ?>'
				});
				break;
			case 'reload': /* just a way of refreshing children, retaining sorting and pagination & without reloading the whole page */
				post("parent-children.php", {
					ChildTable: param.ChildTable,
					ChildLookupField: param.ChildLookupField,
					SelectedID: param.SelectedID,
					Page: param.Page,
					SortBy: param.SortBy,
					SortDirection: param.SortDirection,
					Operation: "get-records"
				}, panelID, undefined, 'pc-loading');
				break;
		}
	};
</script>

<table cellpadding="0" border="0" cellspacing="1" width="100%">
	<tr>
		<td class="toolbar" colspan="<?php echo (count($config['display-fields']) + ($config['open-detail-view-on-click'] ? 1 : 0)); ?>">
			<?php if($config['display-add-new']){ ?><div title="<?php echo addslashes($Translation['Add New']); ?>" onclick="assignmentsGetChildrenRecordsList({ Verb: 'new' });"><img src="addNew.gif" /></div><?php } ?>
			<?php if($config['display-refresh']){ ?><div onclick="assignmentsGetChildrenRecordsList({ Verb: 'reload' });"><img src="arrow_refresh.png" /></div><?php } ?>
		</td>
	</tr>
	<tr>
		<?php if(is_array($config['display-fields'])) foreach($config['display-fields'] as $fieldIndex => $fieldLabel){ ?>
			<td 
				class="TableHeader"
			<?php if($config['sortable-fields'][$fieldIndex]){ ?>
				onclick="assignmentsGetChildrenRecordsList({
					Verb: 'sort', 
					SortBy: <?php echo $fieldIndex; ?>, 
					SortDirection: '<?php echo ($parameters['SortBy'] == $fieldIndex && $parameters['SortDirection'] == 'asc' ? 'desc' : 'asc'); ?>'
				});"
				style="cursor: pointer;"
			<?php } ?>
				>
				<?php if($parameters['SortBy'] == $fieldIndex && $parameters['SortDirection'] == 'desc'){ ?>
					<img src="asc.gif" style="margin: 1px;" width="12" />
				<?php }elseif($parameters['SortBy'] == $fieldIndex && $parameters['SortDirection'] == 'asc'){ ?>
					<img src="desc.gif" style="margin: 1px;" width="12"  />
				<?php } ?>
				<?php echo $fieldLabel; ?>
			</td>
		<?php } ?>
		<?php if($config['open-detail-view-on-click']){ ?>
			<td class="TableHeader">&nbsp;</td>
		<?php } ?>
	</tr>

	<?php if(is_array($records)) foreach($records as $pkValue => $record){ $i = 1 - $i; ?>
	<tr class="colorize">
		<td class="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}"; ?> <?php echo ($i ? 'TableBody' : 'TableBodySelected'); ?>" id="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}-" . addslashes($record[$config['child-primary-key-index']]); ?>" valign="top"><?php echo $record[1]; ?></td>
		<td class="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}"; ?> <?php echo ($i ? 'TableBody' : 'TableBodySelected'); ?>" id="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}-" . addslashes($record[$config['child-primary-key-index']]); ?>" valign="top"><?php echo $record[3]; ?></td>
		<td class="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}"; ?> <?php echo ($i ? 'TableBodyNumeric' : 'TableBodySelectedNumeric'); ?>" id="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}-" . addslashes($record[$config['child-primary-key-index']]); ?>" valign="top"><?php echo $record[4]; ?></td>
		<td class="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}"; ?> <?php echo ($i ? 'TableBody' : 'TableBodySelected'); ?>" id="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}-" . addslashes($record[$config['child-primary-key-index']]); ?>" valign="top"><?php echo $record[5]; ?></td>
		<td class="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}"; ?> <?php echo ($i ? 'TableBody' : 'TableBodySelected'); ?>" id="<?php echo "{$parameters['ChildTable']}-{$parameters['ChildLookupField']}-" . addslashes($record[$config['child-primary-key-index']]); ?>" valign="top"><?php echo $record[6]; ?></td>
		<?php if($config['open-detail-view-on-click']){ ?>
			<td onclick="assignmentsGetChildrenRecordsList({ Verb: 'open', ChildID: '<?php echo addslashes($record[$config['child-primary-key-index']]); ?>'});" class="<?php echo ($i ? 'TableBody' : 'TableBodySelected'); ?> view-on-click"><img src="view.gif" /></td>
		<?php } ?>
	</tr>
	<?php } ?>

	<tr>
		<td class="TableFooter" colspan="<?php echo (count($config['display-fields']) + ($config['open-detail-view-on-click'] ? 1 : 0)); ?>">
			<div style="float: left;">
				<?php if($totalMatches){ ?>
					<img align="left" src="deselect.gif" style="cursor: pointer; margin-top: 3px;" onclick="assignmentsGetChildrenRecordsList({ Verb: 'page', Page: 'previous' });" />
					<img align="left" src="nextPage.gif" style="cursor: pointer; margin-top: 3px;" onclick="assignmentsGetChildrenRecordsList({ Verb: 'page', Page: 'next' });" />
					<?php if($config['show-page-progress']){ ?>
						<span style="float: right; margin: 10px;">
							<?php $firstRecord = ($parameters['Page'] - 1) * $config['records-per-page'] + 1; ?>
							<?php echo str_replace(array('<FirstRecord>', '<LastRecord>', '<RecordCount>'), array($firstRecord, $firstRecord + count($records) - 1, $totalMatches), $Translation['records x to y of z']); ?>
						</span>
					<?php } ?>
				<?php }else{ ?>
					<?php echo $Translation['No matches found!']; ?>
				<?php } ?>
			</div>
		</td>
	</tr>
</table>

<script>colorize();</script>
