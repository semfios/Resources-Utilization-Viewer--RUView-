<?php

class DataList{
	// this class generates the data table ...

	var $QueryFieldsTV,
		$QueryFieldsCSV,
		$QueryFieldsFilters,
		$QueryFieldsQS,
		$QueryFrom,
		$QueryWhere,
		$QueryOrder,

		$ColWidth,                      // array of field widths
		$DataHeight,
		$TableName,

		$AllowSelection,
		$AllowDelete,
		$AllowDeleteOfParents,
		$AllowInsert,
		$AllowUpdate,
		$SeparateDV,
		$Permissions,
		$AllowFilters,
		$AllowSavingFilters,
		$AllowSorting,
		$AllowNavigation,
		$AllowPrinting,
		$AllowPrintingMultiSelection,
		$HideTableView,
		$AllowCSV,
		$CSVSeparator,

		$QuickSearch,     // 0 to 3

		$RecordsPerPage,
		$ScriptFileName,
		$RedirectAfterInsert,
		$TableTitle,
		$PrimaryKey,
		$DefaultSortField,
		$DefaultSortDirection,

		// Templates variables
		$Template,
		$SelectedTemplate,
		$ShowTableHeader, // 1 = show standard table headers
		$ShowRecordSlots, // 1 = show empty record slots in table view
		// End of templates variables

		$ContentType,    // set by DataList to 'tableview', 'detailview', 'tableview+detailview', 'print-tableview', 'print-detailview' or 'filters'
		$HTML;           // generated html after calling Render()

	function DataList(){     // Constructor function
		$this->DataHeight = 150;

		$this->AllowSelection = 1;
		$this->AllowDelete = 1;
		$this->AllowInsert = 1;
		$this->AllowUpdate = 1;
		$this->AllowFilters = 1;
		$this->AllowNavigation = 1;
		$this->AllowPrinting = 1;
		$this->HideTableView = 0;
		$this->QuickSearch = 0;
		$this->AllowCSV = 0;
		$this->CSVSeparator = ",";
		$this->HighlightColor = '#FFF0C2';  // default highlight color

		$this->RecordsPerPage = 10;
		$this->Template = '';
		$this->HTML = '';
	}

	function showTV(){
		if($this->SeparateDV){
			$this->HideTableView = ($this->Permissions[2]==0 ? 1 : 0);
		}
	}

	function hideTV(){
		if($this->SeparateDV){
			$this->HideTableView = 1;
		}
	}

	function Render(){
	// get post and get variables
		global $Translation, $adminConfig;

		$FiltersPerGroup = 4;
		$buttonWholeWidth = 136;

		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			$SortField = $_GET["SortField"];
			$SortDirection = $_GET["SortDirection"];
			$FirstRecord = $_GET["FirstRecord"];
			$ScrollUp_y = $_GET["ScrollUp_y"];
			$ScrollDn_y = $_GET["ScrollDn_y"];
			$Previous_x = $_GET["Previous_x"];
			$Next_x = $_GET["Next_x"];
			$Filter_x = $_GET["Filter_x"];
			$SaveFilter_x = $_GET["SaveFilter_x"];
			$NoFilter_x = $_GET["NoFilter_x"];
			$CancelFilter = $_GET["CancelFilter"];
			$ApplyFilter = $_GET["ApplyFilter"];
			$Search_x = $_GET["Search_x"];
			$SearchString = (get_magic_quotes_gpc() ? stripslashes($_GET['SearchString']) : $_GET['SearchString']);
			$CSV_x = $_GET["CSV_x"];

			$FilterAnd = $_GET["FilterAnd"];
			$FilterField = $_GET["FilterField"];
			$FilterOperator = $_GET["FilterOperator"];
			if(is_array($_GET['FilterValue'])){
				foreach($_GET['FilterValue'] as $fvi=>$fv){
					$FilterValue[$fvi]=(get_magic_quotes_gpc() ? stripslashes($fv) : $fv);
				}
			}

			$Print_x = $_GET['Print_x'];
			$PrintTV = $_GET['PrintTV'];
			$PrintDV = $_GET['PrintDV'];
			$SelectedID = (get_magic_quotes_gpc() ? stripslashes($_GET['SelectedID']) : $_GET['SelectedID']);
			$insert_x = $_GET['insert_x'];
			$update_x = $_GET['update_x'];
			$delete_x = $_GET['delete_x'];
			$SkipChecks = $_GET['confirmed'];
			$deselect_x = $_GET['deselect_x'];
			$addNew_x = $_GET['addNew_x'];
			$dvprint_x = $_GET['dvprint_x'];
			$DisplayRecords = (in_array($_GET['DisplayRecords'], array('user', 'group')) ? $_GET['DisplayRecords'] : 'all');
		}else{
			$SortField = $_POST['SortField'];
			$SortDirection = $_POST['SortDirection'];
			$FirstRecord = $_POST['FirstRecord'];
			$ScrollUp_y = $_POST['ScrollUp_y'];
			$ScrollDn_y = $_POST['ScrollDn_y'];
			$Previous_x = $_POST['Previous_x'];
			$Next_x = $_POST['Next_x'];
			$Filter_x = $_POST['Filter_x'];
			$SaveFilter_x = $_POST['SaveFilter_x'];
			$NoFilter_x = $_POST['NoFilter_x'];
			$CancelFilter = $_POST['CancelFilter'];
			$ApplyFilter = $_POST['ApplyFilter'];
			$Search_x = $_POST['Search_x'];
			$SearchString = (get_magic_quotes_gpc() ? stripslashes($_POST['SearchString']) : $_POST['SearchString']);
			$CSV_x = $_POST['CSV_x'];

			$FilterAnd = $_POST['FilterAnd'];
			$FilterField = $_POST['FilterField'];
			$FilterOperator = $_POST['FilterOperator'];
			if(is_array($_POST['FilterValue'])){
				foreach($_POST['FilterValue'] as $fvi=>$fv){
					$FilterValue[$fvi]=(get_magic_quotes_gpc() ? stripslashes($fv) : $fv);
				}
			}

			$Print_x = $_POST['Print_x'];
			$PrintTV = $_POST['PrintTV'];
			$PrintDV = $_POST['PrintDV'];
			$SelectedID = (get_magic_quotes_gpc() ? stripslashes($_POST['SelectedID']) : $_POST['SelectedID']);
			$insert_x = $_POST['insert_x'];
			$update_x = $_POST['update_x'];
			$delete_x = $_POST['delete_x'];
			$SkipChecks = $_POST['confirmed'];
			$deselect_x = $_POST['deselect_x'];
			$addNew_x = $_POST['addNew_x'];
			$dvprint_x = $_POST['dvprint_x'];
			$DisplayRecords = (in_array($_POST['DisplayRecords'], array('user', 'group')) ? $_POST['DisplayRecords'] : 'all');
		}

	// insure authenticity of user inputs:
		if(!$this->AllowDelete){
			$delete_x = '';
		}
		if(!$this->AllowDeleteOfParents){
			$SkipChecks = '';
		}
		if(!$this->AllowInsert){
			$insert_x = '';
			$addNew_x = '';
		}
		if(!$this->AllowUpdate){
			$update_x = '';
		}
		if(!$this->AllowFilters){
			$Filter_x = '';
		}
		if(!$this->AllowPrinting){
			$Print_x = '';
			$PrintDV = '';
			$PrintTV = '';
		}
		if(!$this->AllowPrintingMultiSelection){
			$PrintDV = '';
			$PrintTV = '';
		}
		if(!$this->QuickSearch){
			$SearchString = '';
		}
		if(!$this->AllowCSV){
			$CSV_x = '';
		}

	// enforce record selection if user has edit/delete permissions on the current table
		$AllowPrintDV=1;
		$this->Permissions=getTablePermissions($this->TableName);
		if($this->Permissions[3] || $this->Permissions[4]){ // current user can edit or delete?
			$this->AllowSelection = 1;
		}elseif(!$this->AllowSelection){
			$SelectedID='';
			$AllowPrintDV=0;
			$PrintDV='';
		}

		if(!$this->AllowSelection || !$SelectedID){ $dvprint_x=''; }

		$this->QueryFieldsIndexed=reIndex($this->QueryFieldsFilters);


		$this->HTML .= '<form method="post" name="myform" action="'.$this->ScriptFileName.'">';
		$this->HTML .= '<script>';
		$this->HTML .= 'function enterAction(){';
		$this->HTML .= '   if($$("input[name=SearchString]:focus")[0] != undefined){ $("Search").click(); }';
		$this->HTML .= '   return false;';
		$this->HTML .= '}';
		$this->HTML .= '</script>';
		$this->HTML .= '<input id="EnterAction" type="submit" style="position: absolute; left: 0px; top: -100px;" onclick="return enterAction();">';

		$this->ContentType='tableview'; // default content type

	// handle user commands ...
		if($PrintTV != ''){
			$Print_x=1;
			$_POST['Print_x']=1;
		}

		if($deselect_x != ''){
			$SelectedID = '';
			$this->showTV();
		}

		elseif($insert_x != ''){
			$SelectedID = call_user_func($this->TableName.'_insert');

			// redirect to a safe url to avoid refreshing and thus
			// insertion of duplicate records.

			// compose filters and sorting
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				if($FilterField[$i] != '' && $FilterOperator[$i] != '' && ($FilterValue[$i] != '' || strstr($FilterOperator[$i], 'Empty'))){
					$filtersGET .= "&FilterAnd[$i]=$FilterAnd[$i]&FilterField[$i]=$FilterField[$i]&FilterOperator[$i]=$FilterOperator[$i]&FilterValue[$i]=".urlencode($FilterValue[$i]);
				}
			}
			$filtersGET .= "&SortField=$SortField&SortDirection=$SortDirection&FirstRecord=$FirstRecord";
			$filtersGET .= "&DisplayRecords=$DisplayRecords";
			$filtersGET = substr($filtersGET, 1); // remove initial &

			if($this->RedirectAfterInsert != ''){
				if(strpos($this->RedirectAfterInsert, '?')){ $this->RedirectAfterInsert.='&record-added-ok='.rand(); }else{ $this->RedirectAfterInsert.='?record-added-ok='.rand(); }
				if(strpos($this->RedirectAfterInsert, $this->ScriptFileName)!==false){ $this->RedirectAfterInsert.='&'.$filtersGET; }
				@header('Location: ' . str_replace("#ID#", urlencode($SelectedID), $this->RedirectAfterInsert));
				$this->HTML .= "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;url=" . str_replace("#ID#", urlencode($SelectedID), $this->RedirectAfterInsert) ."\">";
				return;
			}else{
				$this->HTML .= '<META HTTP-EQUIV="Refresh" CONTENT="0;url='.$this->ScriptFileName.'?SelectedID='.urlencode($SelectedID).'&'.$filtersGET.'">';
				return;
			}
		}

		elseif($delete_x != ''){
			$d = call_user_func($this->TableName.'_delete', $SelectedID, $this->AllowDeleteOfParents, $SkipChecks);
			if($d){
				$this->HTML .= "<div class=Error>".$Translation['error:']." $d</div>";
			}else{
				$SelectedID = '';
				$this->showTV();
			}
		}

		elseif($update_x != ''){
			call_user_func($this->TableName.'_update', $SelectedID);

			// compose filters and sorting
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				if($FilterField[$i] != '' && $FilterOperator[$i] != '' && ($FilterValue[$i] != '' || strstr($FilterOperator[$i], 'Empty'))){
					$filtersGET .= "&FilterAnd[$i]=$FilterAnd[$i]&FilterField[$i]=$FilterField[$i]&FilterOperator[$i]=$FilterOperator[$i]&FilterValue[$i]=".urlencode($FilterValue[$i]);
				}
			}
			$filtersGET .= "&SortField=$SortField&SortDirection=$SortDirection&FirstRecord=$FirstRecord";
			$filtersGET .= "&DisplayRecords=$DisplayRecords";
			$filtersGET = substr($filtersGET, 1); // remove initial &

			$redirectUrl = $this->ScriptFileName.'?SelectedID='.urlencode($SelectedID).'&'.$filtersGET.'&record-updated-ok='.rand();
			@header("Location: $redirectUrl");
			$this->HTML .= '<META HTTP-EQUIV="Refresh" CONTENT="0;url='.$redirectUrl.'">';
			return;
		}

		elseif($addNew_x != ''){
			$SelectedID='';
			$this->hideTV();
		}

		elseif($Print_x != ''){
			// print code here ....
			$this->AllowNavigation = 0;
			$this->AllowSelection = 0;
		}

		elseif($SaveFilter_x != '' && $this->AllowSavingFilters){
			$this->HTML .= "<table width=550 border=0 align=center><tr><td class=TableTitle>" . $Translation["saved filters title"] . "</td></tr>";
			$this->HTML .= "\n\t<tr><td class=TableHeader>" . $Translation["saved filters instructions"] . "</td></tr>";
			$this->HTML .= "\n\t<tr><td class=TableHeader><textarea cols=60 rows=12 wrap=off>";

			$SourceCode  = "<html><body>\n";
			$SourceCode .= '<form method="post" action="' . $_SERVER['HTTP_REFERER'] . '">'."\n";
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				if($i%$FiltersPerGroup == 1 && $i != 1 && $FilterAnd[$i] != ''){
					$SourceCode .= "\t<input name=FilterAnd[$i] value='$FilterAnd[$i]' type=hidden>\n";
				}
				if($FilterField[$i] != '' && $FilterOperator[$i] != '' && ($FilterValue[$i] != '' || strstr($FilterOperator[$i], 'Empty'))){
					if(!strstr($SourceCode, "\t<input name=FilterAnd[$i] value=")){
						$SourceCode .= "\t<input name=FilterAnd[$i] value='$FilterAnd[$i]' type=hidden>\n";
					}
					$SourceCode .= "\t<input name=FilterField[$i] value='$FilterField[$i]' type=hidden>\n";
					$SourceCode .= "\t<input name=FilterOperator[$i] value='$FilterOperator[$i]' type=hidden>\n";
					$SourceCode .= "\t<input name=FilterValue[$i] value='" . htmlspecialchars($FilterValue[$i], ENT_QUOTES) . "' type=hidden>\n\n";
				}
			}
			$SourceCode .= "\n\t<input type=submit value=\"Show Filtered Data\">\n";
			$SourceCode .= "</form>\n</body></html>";
			$this->HTML .= $SourceCode;

			$this->HTML .= '</textarea>';
			$this->HTML .= '<br /><input type="submit" value="' . $Translation['hide code'] . '">';
			$this->HTML .= "\n\t</table>\n\n";
		}

		elseif($Filter_x != ''){
			$orderBy = array();
			if($SortField){
				$sortFields = explode(',', $SortField);
				$i=0;
				foreach($sortFields as $sf){
					$tob = preg_split('/\s+/', $sf, 2);
					$orderBy[] = array(trim($tob[0]) => (strtolower(trim($tob[1]))=='desc' ? 'desc' : 'asc'));
					$i++;
				}
				$orderBy[$i-1][$tob[0]] = (strtolower(trim($SortDirection))=='desc' ? 'desc' : 'asc');
			}

			$currDir=dirname(__FILE__).'/hooks'; // path to hooks folder
			$mi=getMemberInfo();
			$uff="{$currDir}/{$this->TableName}.filters.{$mi['username']}.php"; // user-specific filter file
			$gff="{$currDir}/{$this->TableName}.filters.{$mi['group']}.php"; // group-specific filter file
			$tff="{$currDir}/{$this->TableName}.filters.php"; // table-specific filter file
			
			/*
				if no explicit filter file exists, look for filter files in the hooks folder in this order:
					1. tablename.filters.username.php ($uff)
					2. tablename.filters.groupname.php ($gff)
					3. tablename.filters.php ($tff)
			*/
			if(!is_file($this->FilterPage)){
				$this->FilterPage='defaultFilters.php';
				if(is_file($uff)){
					$this->FilterPage=$uff;
				}elseif(is_file($gff)){
					$this->FilterPage=$gff;
				}elseif(is_file($tff)){
					$this->FilterPage=$tff;
				}
			}

			if($this->FilterPage!=''){
				ob_start();
				@include($this->FilterPage);
				$out=ob_get_contents();
				ob_end_clean();
				$this->HTML .= $out;
			}
			// hidden variables ....
				$this->HTML .= '<input name="SortField" value="'.$SortField.'" type="hidden" />';
				$this->HTML .= '<input name="SortDirection" type="hidden" value="'.$SortDirection.'" />';
				$this->HTML .= '<input name="FirstRecord" type="hidden" value="1" />';

				$this->ContentType='filters';
			return;
		}

		elseif($NoFilter_x != ''){
			// clear all filters ...
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				$FilterField[$i] = '';
				$FilterOperator[$i] = '';
				$FilterValue[$i] = '';
			}
			$DisplayRecords = 'all';
			$SearchString = '';
			$FirstRecord = 1;
		}

		elseif($SelectedID){
			$this->hideTV();
		}

		if($SearchString != ''){
			if($Search_x!=''){ $FirstRecord=1; }

			if($this->QueryWhere=='')
				$this->QueryWhere = "where ";
			else
				$this->QueryWhere .= " and ";

			foreach($this->QueryFieldsQS as $fName => $fCaption){
				if(strpos($fName, '<img')===False){
					$this->QuerySearchableFields[$fName]=$fCaption;
				}
			}

			$this->QueryWhere.='('.implode(" LIKE '%".makeSafe($SearchString)."%' or ", array_keys($this->QuerySearchableFields))." LIKE '%".makeSafe($SearchString)."%')";
		}


	// set query filters
		$QueryHasWhere = 0;
		if(strpos($this->QueryWhere, 'where ')!==FALSE)
			$QueryHasWhere = 1;

		$WhereNeedsClosing = 0;
		for($i = 1; $i <= (20 * $FiltersPerGroup); $i+=$FiltersPerGroup){ // Number of filters allowed
			// test current filter group
			$GroupHasFilters = 0;
			for($j = 0; $j < $FiltersPerGroup; $j++){
				if($FilterField[$i+$j] != '' && $this->QueryFieldsIndexed[($FilterField[$i+$j])] != '' && $FilterOperator[$i+$j] != '' && ($FilterValue[$i+$j] != '' || strstr($FilterOperator[$i+$j], 'Empty'))){
					$GroupHasFilters = 1;
					break;
				}
			}

			if($GroupHasFilters){
				if(!stristr($this->QueryWhere, "where "))
					$this->QueryWhere = "where (";
				elseif($QueryHasWhere){
					$this->QueryWhere .= " and (";
					$QueryHasWhere = 0;
				}

				$this->QueryWhere .= " <FilterGroup> " . $FilterAnd[$i] . " (";

				for($j = 0; $j < $FiltersPerGroup; $j++){
					if($FilterField[$i+$j] != '' && $this->QueryFieldsIndexed[($FilterField[$i+$j])] != '' && $FilterOperator[$i+$j] != '' && ($FilterValue[$i+$j] != '' || strstr($FilterOperator[$i+$j], 'Empty'))){
						if($FilterAnd[$i+$j]==''){
							$FilterAnd[$i+$j]='and';
						}
						// test for date/time fields
						$tries=0; $isDateTime=FALSE; $isDate=FALSE;
						$fieldName=str_replace('`', '', $this->QueryFieldsIndexed[($FilterField[$i+$j])]);
						list($tn, $fn)=explode('.', $fieldName);
						while(!($res=sql("show columns from `$tn` like '$fn'", $eo)) && $tries<2){
							$tn=substr($tn, 0, -1);
							$tries++;
						}
						if($row=@mysql_fetch_array($res)){
							if($row['Type']=='date' || $row['Type']=='time'){
								$isDateTime=TRUE;
								if($row['Type']=='date'){
									$isDate=True;
								}
							}
						}
						// end of test
						if($FilterOperator[$i+$j]=='isEmpty' && !$isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " (" . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "='' or " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " is NULL) </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='isNotEmpty' && !$isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "!='' </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='isEmpty' && $isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " (" . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "=0 or " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " is NULL) </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='isNotEmpty' && $isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "!=0 </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='like' && !strstr($FilterValue[$i+$j], "%") && !strstr($FilterValue[$i+$j], "_")){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '%" . makeSafe($FilterValue[$i+$j]) . "%' </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='not like' && !strstr($FilterValue[$i+$j], "%") && !strstr($FilterValue[$i+$j], "_")){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '%" . makeSafe($FilterValue[$i+$j]) . "%' </FilterItem>";
						}elseif($isDate){
							$dateValue = toMySQLDate($FilterValue[$i+$j]);
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '$dateValue' </FilterItem>";
						}else{
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '" . makeSafe($FilterValue[$i+$j]) . "' </FilterItem>";
						}
					}
				}

				$this->QueryWhere .= ") </FilterGroup>";
				$WhereNeedsClosing = 1;
			}
		}

		if($WhereNeedsClosing)
			$this->QueryWhere .= ")";

	// set query sort
		if(!stristr($this->QueryOrder, "order by ") && $SortField != '' && $this->AllowSorting){
			$actualSortField = $SortField;
			foreach($this->SortFields as $fieldNum => $fieldSort){
				$actualSortField = str_replace(" $fieldNum ", " $fieldSort ", " $actualSortField ");
				$actualSortField = str_replace(",$fieldNum ", ",$fieldSort ", " $actualSortField ");
			}
			$this->QueryOrder = "order by $actualSortField $SortDirection";
		}

	// clean up query
		$this->QueryWhere = str_replace('( <FilterGroup> and ', '( ', $this->QueryWhere);
		$this->QueryWhere = str_replace('( <FilterGroup> or ', '( ', $this->QueryWhere);
		$this->QueryWhere = str_replace('( <FilterItem> and ', '( ', $this->QueryWhere);
		$this->QueryWhere = str_replace('( <FilterItem> or ', '( ', $this->QueryWhere);
		$this->QueryWhere = str_replace('<FilterGroup>', '', $this->QueryWhere);
		$this->QueryWhere = str_replace('</FilterGroup>', '', $this->QueryWhere);
		$this->QueryWhere = str_replace('<FilterItem>', '', $this->QueryWhere);
		$this->QueryWhere = str_replace('</FilterItem>', '', $this->QueryWhere);

	// if no 'order by' clause found, apply default sorting if specified
		if($this->DefaultSortField != '' && $this->QueryOrder == ''){
			$this->QueryOrder="order by ".$this->DefaultSortField." ".$this->DefaultSortDirection;
		}

	// get count of matching records ...
		$TempQuery = 'SELECT count(1) from '.$this->QueryFrom.' '.$this->QueryWhere;
		$RecordCount = sqlValue($TempQuery);
		$FieldCountTV = count($this->QueryFieldsTV);
		$FieldCountCSV = count($this->QueryFieldsCSV);
		$FieldCountFilters = count($this->QueryFieldsFilters);
		if(!$RecordCount){
			$FirstRecord=1;
		}

	// disable multi-selection if too many records to avoid browser performance issues
		if($RecordCount > 1000) $this->AllowPrintingMultiSelection=0;

	// Output CSV on request
		if($CSV_x != ''){
			$this->HTML = '';

		// execute query for CSV output
			$fieldList='';
			foreach($this->QueryFieldsCSV as $fn=>$fc)
				$fieldList.="$fn as `$fc`, ";
			$fieldList=substr($fieldList, 0, -2);
			$csvQuery = 'SELECT '.$fieldList.' from '.$this->QueryFrom.' '.$this->QueryWhere.' '.$this->QueryOrder;

			// hook: table_csv
			if(function_exists($this->TableName.'_csv')){
				$args=array();
				$mq=call_user_func($this->TableName.'_csv', $csvQuery, getMemberInfo(), $args);
				$csvQuery=($mq ? $mq : $csvQuery);
			}

			$result = sql($csvQuery, $eo);

		// output CSV field names
			for($i = 0; $i < $FieldCountCSV; $i++)
				$this->HTML .= "\"" . mysql_field_name($result, $i) . "\"" . $this->CSVSeparator;
			$this->HTML .= "\n\n";

		// output CSV data
			while($row = mysql_fetch_row($result)){
				for($i = 0; $i < $FieldCountCSV; $i++)
					$this->HTML .= "\"" . str_replace(array("\r\n", "\r", "\n", '"'), array(' ', ' ', ' ', '""'), $row[$i]) . "\"" . $this->CSVSeparator;
				$this->HTML .= "\n\n";
			}
			$this->HTML = str_replace($this->CSVSeparator . "\n\n", "\n", $this->HTML);
			$this->HTML = substr($this->HTML, 0, strlen($this->HTML) - 1);

		// clean any output buffers
			while(@ob_end_clean());

		// output CSV HTTP headers ...
			header('HTTP/1.1 200 OK');
			header('Date: ' . @date("D M j G:i:s T Y"));
			header('Last-Modified: ' . @date("D M j G:i:s T Y"));
			header("Content-Type: application/force-download");
			header("Content-Length: " . (string)(strlen($this->HTML)));
			header("Content-Transfer-Encoding: Binary");
			header("Content-Disposition: attachment; filename=$this->TableName.csv");

		// send output and quit script
			echo $this->HTML;
			exit;
		}
		$t = time(); // just a random number for any purpose ...

	// should SelectedID be reset on clicking TV buttons?
		$resetSelection=($this->SeparateDV ? "document.myform.SelectedID.value='';" : "document.myform.writeAttribute('novalidate', 'novalidate'); return true;");

	// begin table and display table title
		$this->HTML .= '<table align="center" cellspacing="1" cellpadding="0" border="0"><tr>'."\n";
		$this->HTML .= '<td colspan="' . (count($this->ColCaption) + 1) . '">';
		$sum_width = 0;
		for($i = 0; $i < count($this->ColWidth); $i++)
			$sum_width += $this->ColWidth[$i];
		$this->HTML .= '<table' . ($this->HideTableView ? '' : ' width="100%"') . ' cellspacing="0" cellpadding="0" border="0">'.(($dvprint_x && $this->AllowSelection && $SelectedID) ? '' : "<tr><td align=\"left\"><div class=\"TableTitle\"><span id=\"table-title-img\"><img align=\"top\" src=\"$this->TableIcon\" /></span> $this->TableTitle</div></td></tr>");

		if(!$this->HideTableView && !($dvprint_x && $this->AllowSelection && $SelectedID) && !$PrintDV){
			$this->HTML .= '<tr>';
			// display tables navigator menu
			if($Print_x){
				$this->HTML .= "\n<style type=\"text/css\">@media print{.displayOnly {display: none;}}</style>\n";
				if($this->AllowPrintingMultiSelection){
					$withSelected=''.
						'<input class="print-button" type="button" id="selectAll" value="'.$Translation['Select all records'].'" onClick="$(\'toggleAll\').checked=!$(\'toggleAll\').checked; toggleAllRecords();">'.
						'<span id="withSelected">'.
							'<input class="print-button" type="submit" name="PrintTV" value="'.$Translation['Print Preview Table View'].'">'.
							($AllowPrintDV ? '<input id="PrintDV" class="print-button" type="submit" name="PrintDV" value="'.$Translation['Print Preview Detail View'].'">' : '').
							'<input class="print-button" type="submit" name="Print_x" value="'.$Translation['Cancel Selection'].'">'.
						' &nbsp;</span>'.
						'<script>'.
							'var countSelected=0; '.
							'document.observe(\'dom:loaded\', function(){ '.
								'setInterval("'.
									'$(\'withSelected\').style.display=(countSelected ? \'inline\' : \'none\');'.
								'", 500); '.
							'});'.
						'</script>';
				}

				$this->HTML .= "\n".'<td colspan="2" class="displayOnly" style="min-width: 65em;"><div>'.
										'<input class="print-button" type="submit" value="'.$Translation['Cancel Printing'].'">'.
										'<input class="print-button" type="button" id="sendToPrinter" value="'.$Translation['Print'].'" onClick="window.print();">'.
										$withSelected.
									'</div></td>'."\n";
			}

			// display quick search box
			if($this->QuickSearch > 0 && $this->QuickSearch < 4 && $Print_x==''){
				if($this->QuickSearch==1 || $this->QuickSearch==2){
					$this->HTML .= '</tr><tr>';
				}
				$this->HTML .= '<td><div id="quick-search" class="TableBodySelected buttons" style="width: 350px; '.($this->QuickSearch==3 ? 'float: right' : ($this->QuickSearch==2 ? 'margin: 0 auto' : 'float: left')).'; line-height: 25px;">';
				$this->HTML .= '<button tabindex="2" name="Search_x" value="1" id="Search" type="submit" onClick="'.$resetSelection.' document.myform.NoDV.value=1;" style="float: right; padding: 1px; padding-left: 7px; width: 100px;"><img src="print.gif" /> ' . $Translation['Find It'] . '</button>';
				$this->HTML .= '<b style="float: right;">' . $this->QuickSearchText . ' <input tabindex="1" type="text" name="SearchString" value="' . htmlspecialchars($SearchString, ENT_QUOTES) . '" size="15" class="TextBox" style="margin-top: 3px;"></b>';
				$this->HTML .= '</div></td>';
				$this->HTML .= ($this->QuickSearch<=2 ? '<td>&nbsp;</td>' : '');
			}
			$this->HTML .= '</tr>';
			$this->HTML .= '<tr><td colspan="2" class="TableBody" style="border-radius: 10px 10px 0 0;"><div class="buttons" id="topButtons" style="margin: 0 auto;">';

			// display 'Add New' icon
			if($this->Permissions[1] && $this->SeparateDV && $Print_x==''){
				$this->HTML .= '<button type="submit" class="positive" id="addNew" name="addNew_x" value="1"><img src="addNew.gif" /> ' . $Translation['Add New'] . '</button>';
				$buttonsCount++;
			}

			// display Print icon
			if($this->AllowPrinting && $Print_x==''){
				$this->HTML .= '<button onClick="document.myform.NoDV.value=1; '.$resetSelection.'" type="submit" name="Print_x" id="Print" value="1"><img src="print.gif" /> ' . $Translation['Print Preview'] . '</button>';
				$buttonsCount++;
			}

			// display CSV icon
			if($this->AllowCSV && $Print_x==''){
				$this->HTML .= '<button onClick="document.myform.NoDV.value=1; '.$resetSelection.'" type="submit" name="CSV_x" id="CSV" value="1"><img src="csv.gif" /> ' . $Translation['CSV'] . '</button>';
				$buttonsCount++;
			}

			// display Filter icon
			if($this->AllowFilters && $Print_x==''){
				$this->HTML .= '<button onClick="document.myform.NoDV.value=1; '.$resetSelection.'" type="submit" name="Filter_x" id="Filter" value="1"><img src="search.gif" /> ' . $Translation['filter'] . '</button>';
				$buttonsCount++;
			}
			// display Show All icon
			if(($this->AllowFilters || ($this->QuickSearch>=1 &&  $this->QuickSearch<=3)) && $Print_x==''){
				$this->HTML .= '<button onClick="document.myform.NoDV.value=1; '.$resetSelection.'" type="submit" name="NoFilter_x" id="NoFilter" value="1"><img src="cancel_search.gif" /> ' . $Translation['Reset Filters'] . '</button>';
				$buttonsCount++;
			}
			// script for adjusting top bar width, and focusing into the search box on loading the page
			$this->HTML .= '</div><script>document.observe("dom:loaded", function() { $("topButtons").style.width="'.($buttonsCount * $buttonWholeWidth).'px"; if($$("input[name=SearchString]")){ $$("input[name=SearchString]")[0].focus(); } });</script></td></tr>';

			$this->HTML .= "</table></td></tr>";
			if($Print_x == '' || !$this->AllowPrintingMultiSelection){ $this->HTML .= '<tr><td width="18" class="TableHeader">'.($this->AllowSelection ? '&nbsp;' : '')."</td>"; }
			if($this->AllowPrintingMultiSelection && $Print_x!='') $this->HTML .= '<td width="18" class="TableHeader displayOnly" align="left"><input type="checkbox" title="'.$Translation['Select all records'].'" id="toggleAll" onclick="toggleAllRecords();"></td>';
		// Templates
			if($this->Template!=''){
				$rowTemplate = @implode('', @file('./'.$this->Template));
				if(!$rowTemplate){
					$rowTemplate='';
					$selrowTemplate = '';
				}else{
					if($this->SelectedTemplate!=''){
						$selrowTemplate = @implode('', @file('./'.$this->SelectedTemplate));
						if(!$selrowTemplate){
							$selrowTemplate='';
						}
					}else{
						$selrowTemplate = '';
					}
				}
			}else{
				$rowTemplate = '';
				$selrowTemplate = '';
			}

			// process translations
			if($rowTemplate){
				foreach($Translation as $symbol=>$trans){
					$rowTemplate=str_replace("<%%TRANSLATION($symbol)%%>", $trans, $rowTemplate);
				}
			}
			if($selrowTemplate){
				foreach($Translation as $symbol=>$trans){
					$selrowTemplate=str_replace("<%%TRANSLATION($symbol)%%>", $trans, $selrowTemplate);
				}
			}
		// End of templates

		// $this->ccffv: map $FilterField values to field captions as stored in ColCaption
			$this->ccffv = array();
			foreach($this->ColCaption as $captionIndex => $caption){
				$ffv = 1;
				foreach($this->QueryFieldsFilters as $uselessKey => $filterCaption){
					if($caption == $filterCaption){
						$this->ccffv[$captionIndex] = $ffv;
					}
					$ffv++;
				}
			}
		// display table headers
			$totalColWidth = array_sum($this->ColWidth);
			$forceHeaderWidth = ($totalColWidth > ($buttonsCount * $buttonWholeWidth + 18) ? true : false);
			if($rowTemplate=='' || $this->ShowTableHeader==1){
				for($i = 0; $i < count($this->ColCaption); $i++){
					/* Sorting icon and link */
					if($this->AllowSorting == 1){
						$sort1 = "<a href=\"{$this->ScriptFileName}?SortDirection=asc&SortField=".($this->ColNumber[$i])."\" onClick=\"$resetSelection document.myform.NoDV.value=1; document.myform.SortDirection.value='asc'; document.myform.SortField.value = '".($this->ColNumber[$i])."'; document.myform.submit(); return false;\" class=\"TableHeader\">";
						$sort2 = "</a>";
						if($this->ColNumber[$i] == $SortField){
							$SortDirection = ($SortDirection == "asc" ? "desc" : "asc");
							$sort1 = "<a href=\"{$this->ScriptFileName}?SortDirection=$SortDirection&SortField=".($this->ColNumber[$i])."\" onClick=\"$resetSelection document.myform.NoDV.value=1; document.myform.SortDirection.value='$SortDirection'; document.myform.SortField.value = ".($this->ColNumber[$i])."; document.myform.submit(); return false;\" class=\"TableHeader\"><img src=\"$SortDirection.gif\" border=\"0\" hspace=\"3\">";
							$SortDirection = ($SortDirection == "asc" ? "desc" : "asc");
						}
					}else{
						$sort1 = '';
						$sort2 = '';
					}

					/* Filtering icon and hint */
					$filterHint = '';
					if($this->AllowFilters && is_array($FilterField)){
						// check to see if there is any filter applied on the current field
						if(in_array($this->ccffv[$i], $FilterField)){
							// render filter icon
							$filterHint = '&nbsp;<input width="12" type="image" src="search.gif" name="Filter" title="'.htmlspecialchars($Translation['filtered field']).'" />';
						}
					}

					$this->HTML .= "\t<td valign=\"top\" nowrap=\"nowrap\"" . ($forceHeaderWidth ? ' width="' . ($this->ColWidth[$i] ? $this->ColWidth[$i] : 100) . '"' : '') . " class=\"TableHeader\"><div class=\"TableHeader\">$sort1" . $this->ColCaption[$i] . "$sort2{$filterHint}</div></td>\n";
				}
			}else{
				// Display a Sort by drop down
				$this->HTML .= "\t<td valign=top class=TableHeader colspan=".(count($this->ColCaption)+1)."><div class=TableHeader>";

				if($this->AllowSorting == 1){
					$sortCombo = new Combo;
					//$sortCombo->ListItem[] = '';
					//$sortCombo->ListData[] = '';
					for($i=0; $i < count($this->ColCaption); $i++){
						$sortCombo->ListItem[] = $this->ColCaption[$i];
						$sortCombo->ListData[] = $this->ColNumber[$i];
					}
					$sortCombo->SelectName = "FieldsList";
					$sortCombo->SelectedData = $SortField;
					$sortCombo->Class = 'TableBody';
					$sortCombo->SelectedClass = 'TableBodySelected';
					$sortCombo->Render();
					$d = $sortCombo->HTML;
					$d = str_replace('<select ', "<select onChange=\"document.myform.SortDirection.value='$SortDirection'; document.myform.SortField.value=document.myform.FieldsList.value; document.myform.NoDV.value=1; document.myform.submit();\" ", $d);
					if($SortField){
						$SortDirection = ($SortDirection == "desc" ? "asc" : "desc");
						$sort = "<a href=\"javascript: document.myform.NoDV.value=1; document.myform.SortDirection.value='$SortDirection'; document.myform.SortField.value='$SortField'; document.myform.submit();\" class=TableHeader><img src=$SortDirection.gif border=0 width=11 height=11 hspace=3></a>";
						$SortDirection = ($SortDirection == "desc" ? "asc" : "desc");                  
					}else{
						$sort='';
					}

					$this->HTML .= $Translation['order by']." $d $sort";
				}
				$this->HTML .= "</div></td>\n";
			}

		// table view navigation code ...
			if($RecordCount && $this->AllowNavigation && $RecordCount>$this->RecordsPerPage){
				while($FirstRecord > $RecordCount)
					$FirstRecord -= $this->RecordsPerPage;

				if($FirstRecord == '' || $FirstRecord < 1)
					$FirstRecord = 1;

				if($Previous_x != ''){
					$FirstRecord -= $this->RecordsPerPage;
					if($FirstRecord <= 0)
						$FirstRecord = 1;
				}elseif($Next_x != ''){
					$FirstRecord += $this->RecordsPerPage;
					if($FirstRecord > $RecordCount)
						$FirstRecord = $RecordCount - ($RecordCount % $this->RecordsPerPage) + 1;
					if($FirstRecord > $RecordCount)
						$FirstRecord = $RecordCount - $this->RecordsPerPage + 1;
					if($FirstRecord <= 0)
						$FirstRecord = 1;
				}else{
					// no scrolling action took place :)
				}

			}elseif($RecordCount){
				$FirstRecord = 1;
				$this->RecordsPerPage = 2000; // a limit on max records in print preview to avoid performance drops
			}
		// end of table view navigation code

			$this->HTML .= "\n\t</tr>\n";
			$this->HTML .= '<!-- tv data below -->';

			$i = 0;
			$hc=new CI_Input();
			$hc->charset='iso-8859-1';
			if($RecordCount){
				$i = $FirstRecord;
			// execute query for table view
				$fieldList='';
				foreach($this->QueryFieldsTV as $fn=>$fc)
					$fieldList.="$fn as `$fc`, ";
				$fieldList=substr($fieldList, 0, -2);
				if($this->PrimaryKey)
					$fieldList.=", $this->PrimaryKey as '".str_replace('`', '', $this->PrimaryKey)."'";
				$tvQuery = 'SELECT '.$fieldList.' from '.$this->QueryFrom.' '.$this->QueryWhere.' '.$this->QueryOrder;
				$result = sql($tvQuery . " limit " . ($i-1) . ",$this->RecordsPerPage", $eo);
				while(($row = mysql_fetch_array($result)) && ($i < ($FirstRecord + $this->RecordsPerPage))){
					$alt=(($i-$FirstRecord)%2);
					if($PrintTV && $_POST["select_{$row[$FieldCountTV]}"]!=1)    continue;					$class = "TableBody".($alt ? 'Selected' : '').($fNumeric ? 'Numeric' : '');
					$this->HTML .= "\t<tr class=\"colorize\">";
					if($Print_x == '' || !$this->AllowPrintingMultiSelection){ $this->HTML .= "<td class=\"$class\" valign=\"top\" align=\"right\">".($SelectedID == $row[$FieldCountTV] ? '<img src="view.gif" width="12">' : '&nbsp;').'</td>'; }
					if($this->AllowPrintingMultiSelection && $Print_x!=''){
						$this->HTML .= "<td class=\"$class displayOnly\" valign=\"top\" align=\"left\"><input type=\"checkbox\" id=\"select_{$row[$FieldCountTV]}\" name=\"select_{$row[$FieldCountTV]}\" value=\"1\" onclick=\"if(\$('select_{$row[$FieldCountTV]}').checked) countSelected++; else countSelected--;\"></td>";
						$toggleAllScript.="\$('select_{$row[$FieldCountTV]}').checked=s;";
					}
					// templates
					if($rowTemplate!=''){
						if($this->AllowSelection == 1 && $SelectedID == $row[$FieldCountTV] && $selrowTemplate != ''){
							$rowTemp=$selrowTemplate;
						}else{
							$rowTemp = $rowTemplate;
						}

						if($this->AllowSelection == 1 && $SelectedID != $row[$FieldCountTV]){
							$rowTemp = str_replace('<%%SELECT%%>',"<a onclick=\"document.myform.SelectedField.value=this.parentNode.cellIndex; document.myform.SelectedID.value='" . addslashes($row[$FieldCountTV]) . "'; document.myform.submit(); return false;\" href=\"{$this->ScriptFileName}?SelectedID=" . htmlspecialchars($row[$FieldCountTV], ENT_QUOTES) . "\" class=\"$class\" style=\"display: block; padding:0px;\">",$rowTemp);
							$rowTemp = str_replace('<%%ENDSELECT%%>','</a>',$rowTemp);
						}else{
							$rowTemp = str_replace('<%%SELECT%%>', '', $rowTemp);
							$rowTemp = str_replace('<%%ENDSELECT%%>', '', $rowTemp);
						}

						for($j = 0; $j < $FieldCountTV; $j++){
							$fieldTVCaption=current(array_slice($this->QueryFieldsTV, $j, 1));

							$fd=$hc->xss_clean(nl2br($row[$j])); /* Sanitize output against XSS attacks */
							/*
								the TV template could contain field placeholders in the format 
								<%%FIELD_n%%> or <%%VALUE(Field name)%%> 
							*/
							$rowTemp = str_replace("<%%FIELD_$j%%>", thisOr($fd), $rowTemp);
							$rowTemp = str_replace("<%%VALUE($fieldTVCaption)%%>", thisOr($fd), $rowTemp);
							if(thisOr($fd)=='&nbsp;' && preg_match('/<a href=".*?&nbsp;.*?<\/a>/i', $rowTemp, $m)){
								$rowTemp=str_replace($m[0], '', $rowTemp);
							}
						}

						if($alt && $SelectedID != $row[$FieldCountTV]){
							$rowTemp = str_replace("TableBody", "TableBodySelected", $rowTemp);
							$rowTemp = str_replace("TableBodyNumeric", "TableBodySelectedNumeric", $rowTemp);
							$rowTemp = str_replace("SelectedSelected", "Selected", $rowTemp);
						}

						if($SearchString!='') $rowTemp=highlight($SearchString, $rowTemp);
						$this->HTML .= $rowTemp;
						$rowTemp = '';

					}else{
					// end of templates
						for($j = 0; $j < $FieldCountTV; $j++){
							$fType=mysql_field_type($result, $j);
							$fNumeric=(stristr($fType,'int') || stristr($fType,'float') || stristr($fType,'decimal') || stristr($fType,'numeric') || stristr($fType,'real') || stristr($fType,'double')) ? true : false;
							if($this->AllowSelection == 1){
								$sel1 = "<a href=\"{$this->ScriptFileName}?SelectedID=" . htmlspecialchars($row[$FieldCountTV], ENT_QUOTES) . "\" onclick=\"document.myform.SelectedID.value='" . addslashes($row[$FieldCountTV]) . "'; document.myform.submit(); return false;\" class=\"$class\" style=\"padding:0px;\">";
								$sel2 = "</a>";
							}else{
								$sel1 = '';
								$sel2 = '';
							}

							$this->HTML .= "<td valign=top class=$class><div class=$class>&nbsp;$sel1" . $row[$j] . "$sel2&nbsp;</div></td>";
						}
					}
					$this->HTML .= "</tr>\n";
					$i++;
				}
				$i--;
			}

			$this->HTML = preg_replace("/<a href=\"(mailto:)?&nbsp;[^\n]*title=\"&nbsp;\"><\/a>/", '&nbsp;', $this->HTML);
			$this->HTML = preg_replace("/<a [^>]*>(&nbsp;)*<\/a>/", '&nbsp;', $this->HTML);
			$this->HTML = preg_replace("/<%%.*%%>/U", '&nbsp;', $this->HTML);

			if($this->ShowRecordSlots){
				for($j = $i + 1; $j < ($FirstRecord + $this->RecordsPerPage); $j++)
					$this->HTML .= "\n\t<tr><td colspan=".(count($this->ColCaption)+1)."><div class=TableBody>&nbsp;</div></td></tr>";
			}
		// end of data
			$this->HTML.='<!-- tv data above -->';

			if($Print_x == ''){
				$pagesMenu='';
				if($RecordCount > $this->RecordsPerPage){
					$pagesMenu='<td align="center" class="TableFooter">'.$Translation['go to page']." <select onChange=\"$resetSelection document.myform.NoDV.value=1; document.myform.FirstRecord.value=(this.value*".$this->RecordsPerPage."+1); document.myform.submit();\">";
					for($page=0; $page<ceil($RecordCount/$this->RecordsPerPage); $page++){
						$pagesMenu.="<option value=\"$page\" ".($FirstRecord==($page*$this->RecordsPerPage+1)?'selected':'').">".($page+1)."</option>";
					}
					$pagesMenu.='</select></td>';
				}
				$this->HTML .= "\n\t<tr><td colspan=".(count($this->ColCaption)+1).'><table id="tvFooter" '.(!$this->TablePaginationAlignment ? 'width="100%"' : ($this->TablePaginationAlignment == 2 ? 'align="right"' : 'align="left"')).' cellspacing="0"><tr>';
				$this->HTML .= '<td class="TableFooter buttons" style="border-radius: 0 0 0 10px; padding-left: 10px;" align="left"><button onClick="'.$resetSelection.' document.myform.NoDV.value=1;" type="submit" name="Previous_x" id="Previous" value="1" ><img src="previousPage.gif" /> ' . $Translation['Previous'] . '</button></td>';
				$this->HTML .= "<td align=center class=TableFooter>" . $Translation["records x to y of z"] . "</td>";
				$this->HTML .= $pagesMenu;
				$this->HTML .= '<td class="TableFooter buttons" style="border-radius: 0 0 10px 0; padding-right: 10px;"><button onClick="'.$resetSelection.' document.myform.NoDV.value=1;" type="submit" name="Next_x" id="Next" value="1" style="float: right;"><img src="nextPage.gif" /> ' . $Translation['Next'] . '</button></td>';
				$this->HTML .= "</tr></table></td></tr>";
			}else{
				$this->HTML .= "\n\t<tr><td colspan=".(count($this->ColCaption) + 1)." class=TableFooter><nobr>" . $Translation['records x to y of z'] . '</nobr></td></tr>';
			}
			$this->HTML = str_replace("<FirstRecord>", $FirstRecord, $this->HTML);
			$this->HTML = str_replace("<LastRecord>", $i, $this->HTML);
			$this->HTML = str_replace("<RecordCount>", $RecordCount, $this->HTML);
			$tvShown=true;
		}

	// hidden variables ....
		$this->HTML .= "<input name=SortField value='$SortField' type=hidden>";
		$this->HTML .= "<input name=SelectedID value=\"$SelectedID\" type=hidden>";
		$this->HTML .= "<input name=SelectedField value=\"\" type=hidden>";
		$this->HTML .= "<input name=SortDirection type=hidden value='$SortDirection'>";
		$this->HTML .= "<input name=FirstRecord type=hidden value='$FirstRecord'>";
		$this->HTML .= "<input name=NoDV type=hidden value=''>";
		if($this->QuickSearch && !strpos($this->HTML, 'SearchString')) $this->HTML .= '<input name="SearchString" type="hidden" value="'.htmlspecialchars($SearchString, ENT_QUOTES).'">';
	// hidden variables: filters ...
		$FiltersCode = '';
		for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
			if($i%$FiltersPerGroup == 1 && $i != 1 && $FilterAnd[$i] != ''){
				$FiltersCode .= "<input name=\"FilterAnd[$i]\" value=\"$FilterAnd[$i]\" type=\"hidden\">\n";
			}
			if($FilterField[$i] != '' && $FilterOperator[$i] != '' && ($FilterValue[$i] != '' || strstr($FilterOperator[$i], 'Empty'))){
				if(!strstr($FiltersCode, "<input name=\"FilterAnd[$i]\" value="))
					$FiltersCode .= "<input name=\"FilterAnd[$i]\" value=\"$FilterAnd[$i]\" type=\"hidden\">\n";
				$FiltersCode .= "<input name=\"FilterField[$i]\" value=\"$FilterField[$i]\" type=\"hidden\">\n";
				$FiltersCode .= "<input name=\"FilterOperator[$i]\" value=\"$FilterOperator[$i]\" type=\"hidden\">\n";
				$FiltersCode .= "<input name=\"FilterValue[$i]\" value=\"" . htmlspecialchars($FilterValue[$i], ENT_QUOTES) . "\" type=\"hidden\">\n";
			}
		}
		$FiltersCode .= "<input name=\"DisplayRecords\" value=\"$DisplayRecords\" type=\"hidden\" />";
		$this->HTML .= $FiltersCode;

	// display details form ...
		if(($this->AllowSelection || $this->AllowInsert || $this->AllowUpdate || $this->AllowDelete) && $Print_x=='' && !$PrintDV){
			if(($this->SeparateDV && $this->HideTableView) || !$this->SeparateDV){
				$dvCode=call_user_func($this->TableName.'_form', $SelectedID, $this->AllowUpdate, (($this->HideTableView && $SelectedID) ? 0 : $this->AllowInsert), $this->AllowDelete, $this->SeparateDV);
				$this->HTML .= "\n\t<tr><td colspan=".(count($this->ColCaption) + 1).">$dvCode</td></tr>";
				$this->HTML .= ($this->SeparateDV ? "<input name=SearchString value='".htmlspecialchars($SearchString, ENT_QUOTES)."' type=hidden>" : '');
				if($dvCode){
					$this->ContentType='detailview';
					$dvShown=true;
				}
			}
		}

	// display multiple printable detail views
		if($PrintDV){
			$dvCode='';
			$_POST['dvprint_x']=1;

			// hidden vars
			$this->HTML .= '<input type="hidden" name="Print_x" value="1">'."\n";
			$this->HTML .= '<input type="hidden" name="PrintTV" value="1">'."\n";
			
			// count selected records
			$selectedRecords=0;
			foreach($_POST as $n => $v){
				if(strpos($n, 'select_')===0){
					$id=str_replace('select_', '', $n);
					$selectedRecords++;
					$this->HTML.='<input type="hidden" name="select_'.$id.'" value="1">'."\n";
				}
			}

			if($selectedRecords <= 100){ // if records selected > 100 don't show DV preview to avoid db performance issues.
				foreach($_POST as $n => $v){
					if(strpos($n, 'select_')===0){
						$id=str_replace('select_', '', $n);
						$dvCode.=call_user_func($this->TableName.'_form', $id, 0, 0, 0, 1);
					}
				}
				if($dvCode!=''){
					$dvCode = preg_replace('/<input .*?type="?image"?.*?>/', '', $dvCode);
					$this->HTML .= "\n".'<div class="TableBodySelected displayOnly">'.
										   '<input class="print-button" type="submit" value="'.$Translation['Cancel Printing'].'">'.
										   '<input class="print-button" type="button" id="sendToPrinter" value="'.$Translation['Print'].'" onClick="window.print();">'.
										'</div>'."\n";
					$this->HTML .= $dvCode;
				}
			}else{
				$this->HTML .= '<div class="Error">'.$Translation['Maximum records allowed to enable this feature is'].' 100.</div>';
				$this->HTML .= '<input type="submit" class="print-button" value="'.$Translation['Print Preview Table View'].'">';
			}
		}

		$this->HTML .= "</table>\n";
		if($this->AllowPrintingMultiSelection && $Print_x!='') $this->HTML .= "<script>function toggleAllRecords(){ var s=\$('toggleAll').checked; $toggleAllScript if(s) countSelected=$RecordCount; else countSelected=0; }</script>\n";
		$this->HTML .= "</form></center>";

		// $this->HTML .= '<font face="garamond">'.htmlspecialchars($tvQuery).'</font>';  // uncomment this line for debugging the table view query

		if($dvShown && $tvShown) $this->ContentType='tableview+detailview';
		if($dvprint_x!='') $this->ContentType='print-detailview';
		if($Print_x!='') $this->ContentType='print-tableview';
		if($PrintDV!='') $this->ContentType='print-detailview';

		// call detail view javascript hook file if found
		$dvJSHooksFile=dirname(__FILE__).'/hooks/'.$this->TableName.'-dv.js';
		if(is_file($dvJSHooksFile) && ($this->ContentType=='detailview' || $this->ContentType=='tableview+detailview')){
			$this->HTML.="\n<script src=\"hooks/{$this->TableName}-dv.js\"></script>\n";
		}

		//mysql_close();
	// Das ist Alles!
	}
}


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////


class DataCombo{
	var $Query, // Only the first two fields of the query are used.
				// The first field is treated as the primary key (data values),
				// and the second field is the displayed data items.
		$Class,
		$Style,
		$SelectName,
		$FirstItem,     // if not empty, the first item in the combo with value of ''
		$SelectedData,  // a value compared to first field value of the query to select
						// an item from the combo.
		$SelectedText,

		$ListType, // 0: drop down combo, 1: list box, 2: radio buttons
		$ListBoxHeight, // if ListType=1, this is the height of the list box
		$RadiosPerLine, // if ListType=2, this is the number of options per line
		$AllowNull,

		$ItemCount, // this is returned. It indicates the number of items in the combo.
		$HTML,      // this is returned. The combo html source after calling Render().
		$MatchText; // will store the parent caption value of the matching item.

	function DataCombo(){ // Constructor function
		$this->FirstItem = '';
		$this->HTML = '';
		$this->Class = 'Option Lookup';
		$this->MatchText = '';
		$this->ListType = 0;
		$this->ListBoxHeight=10;
		$this->RadiosPerLine=1;
		$this->AllowNull=1;
	}

	function Render(){
		global $Translation;

		$cQuery=preg_replace('/select .*? from /i', 'select count(1) from ', $this->Query);
		$cQuery=preg_replace('/ order by .+/i', '', $cQuery);
		$num=sqlValue($cQuery);
		if($num > 10000){
			/* to increase server and page performance, we'll avoid returning large result sets and will
			   use ajax auto-complete instead. */
			// get SelectedText
			if($this->SelectedData!=''){
				$vQuery=preg_replace('/ order by .*/i', '', $this->Query);
				$vQuery=preg_replace('/ where .*/i', '', $vQuery);
				preg_match('/select (.*?),/i', $vQuery, $m); // get first field
				$vQuery=str_ireplace("select $m[1],", 'select ', $vQuery);
				$m[1]=str_ireplace('distinct ', '', $m[1]);
				$this->SelectedText=sqlValue($vQuery." where $m[1]='".makeSafe($this->SelectedData)."'");
				$this->MatchText=$this->SelectedText;
			}
			ob_start();
			?>
			<input tabindex="1" name="<?php echo $this->SelectName; ?>" id="<?php echo $this->SelectName; ?>" value="<?php echo htmlspecialchars($this->SelectedData); ?>" type="hidden" />
			<input tabindex="1" style="background:url('asc.gif') no-repeat 2px; padding-left: 22px;" title="<?php echo $Translation['Start typing to get suggestions']; ?>" type="text" name="<?php echo $this->SelectName; ?>_caption" id="<?php echo $this->SelectName; ?>_caption" size="40" class="TextBox" value="<?php echo htmlspecialchars($this->SelectedText); ?>" />
			<div id="<?php echo $this->SelectName; ?>_autocomplete" class="AutoComplete"></div>
			<script>
				document.observe("dom:loaded", function(){
					new Ajax.Autocompleter(
						'<?php echo $this->SelectName; ?>_caption',
						'<?php echo $this->SelectName; ?>_autocomplete',
						'auto-complete.php',                       
						{
							afterUpdateElement: function(e, se){
								$('<?php echo $this->SelectName; ?>').value=se.id;
								<?php echo $this->SelectName; ?>Changed();
							},
							paramName: 'val',
							parameters: 't=<?php echo urlencode(str_replace('_view.php', '', basename($_SERVER['PHP_SELF']))); ?>&f=<?php echo $this->SelectName; ?>'
						}
					);

					$('<?php echo $this->SelectName; ?>_caption').observe('change', function() {
						if($F('<?php echo $this->SelectName; ?>_caption') == '')
							$('<?php echo $this->SelectName; ?>').value='';
							<?php echo $this->SelectName; ?>Changed();
					});
				});
			</script>
			<?php
			$this->HTML=ob_get_contents();
			ob_end_clean();
			return;
		}

		$eo['silentErrors']=true;
		$result = sql($this->Query, $eo);
		if($eo['error']!=''){
			$this->HTML='<div class="Error"><b>'.$Translation['error:'].'</b> '.htmlspecialchars($eo['error'])."</div>\n\n<!--\n{$Translation['query:']}\n {$this->Query}\n-->\n\n";
			return;
		}

		$this->ItemCount = mysql_num_rows($result);
		
		$combo=new Combo();
		$combo->Class=$this->Class;
		$combo->Style=$this->Style;
		$combo->SelectName=$this->SelectName;
		$combo->SelectedData=$this->SelectedData;
		$combo->SelectedText=$this->SelectedText;
		$combo->SelectedClass="SelectedOption";
		$combo->ListType=$this->ListType;
		$combo->ListBoxHeight=$this->ListBoxHeight;
		$combo->RadiosPerLine=$this->RadiosPerLine;
		$combo->AllowNull=($this->ListType==2 ? 0 : $this->AllowNull);

		while($row = mysql_fetch_row($result)){
			$combo->ListData[]=htmlspecialchars($row[0], ENT_QUOTES);
			$combo->ListItem[]=$row[1];
		}
		$combo->Render();
		$this->MatchText=$combo->MatchText;
		$this->SelectedText=$combo->SelectedText;
		$this->SelectedData=$combo->SelectedData;
		if($this->ListType==2){
			$this->HTML=str_replace($this->MatchText, $this->MatchText." <%%PLINK($this->SelectName)%%>", $combo->HTML);
		}else{
			$this->HTML=$combo->HTML;
		}
	}
}


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////


class Combo{
	// The Combo class renders a drop down combo
	// filled with elements in an array ListItem[]
	// and associates each element with data from
	// an array ListData[], and optionally selects 
	// one of the items.

	var $ListItem, // array of items in the combo
		$ListData, // array of items data values
		$Class,
		$SelectedClass,
		$Style,
		$SelectName,
		$SelectID,
		$SelectedData,
		$SelectedText,
		$MatchText, // will store the text value of the matching item.

		$ListType, // 0: drop down combo, 1: list box, 2: radio buttons, 3: multi-selection list box
		$ListBoxHeight, // if ListType=1, this is the height of the list box
		$MultipleSeparator, // if ListType=3, specify the list separator here (default ,)
		$RadiosPerLine, // if ListType=2, this is the number of options per line

		$AllowNull,


		$HTML; // the resulting output HTML code to use

	function Combo(){ // Constructor function
		$this->Class = 'Option';
		$this->SelectedClass = 'SelectedOption';
		$this->HTML = '';
		$this->ListType = 0;
		$this->ListBoxHeight = 10;
		$this->MultipleSeparator = ', ';
		$this->RadiosPerLine = 1;
		$this->AllowNull = true;
	}

	function Render(){
		global $Translation;
		$this->HTML = '';
		$ArrayCount = count($this->ListItem);

		if($ArrayCount > count($this->ListData)){
			$this->HTML .= 'Invalid Class Definition';
			return 0;
		}

		if(!$this->SelectID)    $this->SelectID=$this->SelectName;

		if($this->ListType!=2){
			$this->HTML .= "<select tabindex=\"1\" name=\"$this->SelectName".($this->ListType==3 ? '[]' : '')."\" id=\"$this->SelectID\" class=\"$this->Class\" style=\"$this->Style\"".($this->ListType==1 ? ' size="'.($this->ListBoxHeight < $ArrayCount ? $this->ListBoxHeight : ($ArrayCount + ($this->AllowNull ? 1 : 0))).'"' : '').($this->ListType==3 ? ' multiple' : '').'>';
			$this->HTML .= ($this->AllowNull ? "\n\t<option value=\"\">&nbsp;</option>" : '');

			if($this->ListType==3) $arrSelectedData=explode($this->MultipleSeparator, $this->SelectedData);
			if($this->ListType==3) $arrSelectedText=explode($this->MultipleSeparator, $this->SelectedText);
			for($i = 0; $i < $ArrayCount; $i++){
				if($this->ListType==3){
					if(in_array($this->ListData[$i], $arrSelectedData)){
						$sel = "selected class=\"$this->SelectedClass\"";
						$this->MatchText.=$this->ListItem[$i].$this->MultipleSeparator;
					}else{
						$sel = "class=\"$this->Class\"";
					}
				}else{
					if($this->SelectedData == $this->ListData[$i] || ($this->SelectedText == $this->ListItem[$i] && $this->SelectedText)){
						$sel = "selected class=\"$this->SelectedClass\"";
						$this->MatchText=$this->ListItem[$i];
						$this->SelectedData=$this->ListData[$i];
						$this->SelectedText=$this->ListItem[$i];
					}else{
						$sel = "class=\"$this->Class\"";
					}
				}

				$this->HTML .= "\n\t<option value=\"" . $this->ListData[$i] . "\" $sel>" . str_replace('&amp;', '&', htmlspecialchars(stripslashes($this->ListItem[$i]))) . "</option>";
			}
			$this->HTML .= "</select>";
			if($this->ListType==3 && strlen($this->MatchText)>0)   $this->MatchText=substr($this->MatchText, 0, -1 * strlen($this->MultipleSeparator));
			if($this->ListType==3) $this->HTML .= '<br />'.$Translation['Hold CTRL key to select multiple items from the above list.'];
		}else{
			global $Translation;
			$separator = '&nbsp; &nbsp; &nbsp; &nbsp;';

			$j=0;
			if($this->AllowNull){
				$this->HTML .= "<input tabindex=\"1\" id=\"$this->SelectName$j\" type=\"radio\" name=\"$this->SelectName\" value=\"\" ".($this->SelectedData==''?'checked':'')."> <label for=\"$this->SelectName$j\">{$Translation['none']}</label>";
				$this->HTML .= ($this->RadiosPerLine==1 ? '<br />' : $separator);
				$shift=2;
			}else{
				$shift=1;
			}
			for($i = 0; $i < $ArrayCount; $i++){
				$j++;
				if($this->SelectedData == $this->ListData[$i] || ($this->SelectedText == $this->ListItem[$i] && $this->SelectedText)){
					$sel = "checked class=\"$this->SelectedClass\"";
					$this->MatchText=$this->ListItem[$i];
					$this->SelectedData=$this->ListData[$i];
					$this->SelectedText=$this->ListItem[$i];
				}else{
					$sel = "class=\"$this->Class\"";
				}

				$this->HTML .= "<input tabindex=\"1\" id=\"$this->SelectName$j\" type=\"radio\" name=\"$this->SelectName\" value=\"{$this->ListData[$i]}\" $sel> <label for=\"$this->SelectName$j\">".str_replace('&amp;', '&', htmlspecialchars(stripslashes($this->ListItem[$i])))."</label>";
				if(($i+$shift)%$this->RadiosPerLine){
					$this->HTML .= $separator;
				}else{
					$this->HTML .= '<br />';
				}
			}
		}

		return 1;
	}
}


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////


class DateCombo{
	// renders a date combo with a pre-selected date

	var $DateFormat,          // any combination of y,m,d
		$DefaultDate,         // format: yyyy-mm-dd
		$MinYear,
		$MaxYear,
		$MonthNames,
		$Comment,
		$NamePrefix,          // will be used in the HTML name prop as a prefix to "Year", "Month", "Day"
		$RTL,
		$CSSOptionClass,
		$CSSSelectedClass,
		$CSSCommentClass;

	function DateCombo(){
		// set default values
		$this->DateFormat = "ymd";
		$this->DefaultDate = '';
		$this->MinYear = 1900;
		$this->MaxYear = 2100;
		$this->MonthNames = "January,February,March,April,May,June,July,August,September,October,November,December";
		$this->Comment = "<empty>";
		$this->NamePrefix = "Date";

		$this->RTL = 0;
		$this->CSSOptionClass = '';
		$this->CSSSelectedClass = '';
		$this->CSSCommentClass = '';
	}

	function GetHTML($readOnly=false){
		list($xy, $xm, $xd)=explode('-', $this->DefaultDate);

		//$y : render years combo
		$years = new Combo;
		for($i=$this->MinYear; $i<=$this->MaxYear; $i++){
			$years->ListItem[] = $i;
			$years->ListData[] = $i;
		}
		$years->SelectName = $this->NamePrefix . 'Year';
		$years->SelectID = $this->NamePrefix;
		$years->SelectedData = $xy;
		$years->Class = $this->CSSOptionClass;
		$years->SelectedClass = $this->CSSSelectedClass;
		$years->Render();
		$y = ($readOnly ? substr($this->DefaultDate, 0, 4) : $years->HTML);

		//$m : render months combo
		$months = new Combo;
		for($i=1; $i<=12; $i++){
			$months->ListData[] = $i;
		}
		$months->ListItem = explode(",", $this->MonthNames);
		$months->SelectName = $this->NamePrefix . 'Month';
		$months->SelectID = $this->NamePrefix . '-mm';
		$months->SelectedData = intval($xm);
		$months->Class = $this->CSSOptionClass;
		$months->SelectedClass = $this->CSSSelectedClass;
		$months->Render();
		$m = ($readOnly ? $xm : $months->HTML);

		//$d : render days combo
		$days = new Combo;
		for($i=1; $i<=31; $i++){
			$days->ListItem[] = $i;
			$days->ListData[] = $i;
		}
		$days->SelectName = $this->NamePrefix . 'Day';
		$days->SelectID = $this->NamePrefix . '-dd';
		$days->SelectedData = intval($xd);
		$days->Class = $this->CSSOptionClass;
		$days->SelectedClass = $this->CSSSelectedClass;
		$days->Render();
		$d = ($readOnly ? $xd : $days->HTML);

		$p1 = substr($this->DateFormat, 0, 1);
		$p2 = substr($this->DateFormat, 1, 1);
		$p3 = substr($this->DateFormat, 2, 1);

		return ($readOnly ? "${$p1}/${$p2}/${$p3}" : "${$p1} / ${$p2} / ${$p3}");
	}
}

///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////

function toMySQLDate($formattedDate, $sep='/', $ord='dmY'){
	// extract date elements
	$de=explode($sep, $formattedDate);
	$mySQLDate=intval($de[strpos($ord, 'Y')]).'-'.intval($de[strpos($ord, 'm')]).'-'.intval($de[strpos($ord, 'd')]);
	return $mySQLDate;
}

function highlight($needle, $haystack){
	$needle = preg_quote($needle, "/");
	return preg_replace("#(?!<.*?)(".$needle.")(?![^<>]*?>)#i", '<span style="background-color: #FFFF00;">\1</span>', $haystack);
}

function reIndex(&$arr){
	/*	returns a copy of the given array,
		with keys replaced by 1-based numeric indices,
		and values replaced by original keys
	*/
	$i=1;
	foreach($arr as $n=>$v){
		$arr2[$i]=$n;
		$i++;
	}
	return $arr2;
}

?>