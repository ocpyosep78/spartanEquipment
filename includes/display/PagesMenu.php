<?php //Multi column menu 

require_once('class_ndx_multi_column_menu.php');

CLASS ISC_PAGESMENU_PANEL extends PANEL
{
	
	// Changeable for each site:
	
	var	$desired_columns = 5;
	var	$max_rows_per_column  = 110;
	var	$max_top_menu_items = 12;	

	var $max_before_more = 3; // set to zero for unlimited

	
	// End changeable for each site
	
	var $anchor_prefix = "nm_anchor_";
	var $menu_prefix = "nm_menu_";
	var $image_prefix = "nm_image_";

	public function SetPanelSettings()
	{
		error_reporting(E_ERROR); 	
		error_reporting(E_ALL); 	
		
	
		$mega1 = $GLOBALS['ISC_CLASS_DATA_STORE']->Read('mega1');
		if ($mega1) {
			$mega2 = $GLOBALS['ISC_CLASS_DATA_STORE']->Read('mega2');
			if ($mega2) {
				$GLOBALS['SNIPPETS']['PageMenu1'] = $mega1;
				$GLOBALS['SNIPPETS']['PageMenu2'] = $mega2;
				
				// include the following lines where the categories are changed
					//$GLOBALS['ISC_CLASS_DATA_STORE']->Delete('mega1');
					//$GLOBALS['ISC_CLASS_DATA_STORE']->Delete('mega2');
				
//echo 'From Data Store'; // sdw tmp
				return;
			}
		}
//echo 'NOT From Data Store';// sdw tmp
		
		$menu_item_count = 0;
//		$includeCategoryIds=array(1,2,3,4,5,6,7,134);
		
		$categories = $GLOBALS['ISC_CLASS_DATA_STORE']->Read('RootCategories', true);
		
		if (!isset($categories[0])) {
			$this->DontDisplay = true;
			return;
		}
		
		$total_output = "";
		$total_output_2 = "";
		$output = "";
		$output_1 = "";
		$output_js = "";
		
		foreach ($categories[0] as $rootCat) {
			
			$category_totals = array();
			$column_totals = array();
			
			// If we don't have permission to view this category then skip
			if(!CustomerGroupHasAccessToCategory($rootCat['categoryid'])) {
				continue;
			}
			
//			if (!in_array($rootCat['categoryid'], $includeCategoryIds)) {
//				continue;
//			}

			/* Limit the number of top menu items */
			if (++$menu_item_count > $this->max_top_menu_items) {
				continue;
			}
			
			$parentid = $rootCat['categoryid'];
			
			$anchor_id = $this->anchor_prefix.$parentid;
			$menu_id = $this->menu_prefix.$parentid;
			$image_id = $this->image_prefix.$parentid;
			$output_js .= "jkmegamenu.definemenu(\"{$anchor_id}\",\"{$menu_id}\",\"mouseover\");\n";
		
			foreach ($categories[$parentid] as $subCat) {
				$subparentid = $subCat['categoryid'];
				//$subclass_count = count($categories[$subparentid]) + 1;  undefined index mz
				if (isset($categories[$subparentid])) { 
					$subclass_count = count($categories[$subparentid]) + 1; 
					// sdw part of "more" change 2012_02_02
					if ($this->max_before_more > 0 && $subclass_count > $this->max_before_more) {
						$subclass_count = $this->max_before_more + 1;
					}
				} else {
					$subclass_count = 1;
				}
				$category_totals[] = $subclass_count;
			}
			
			$mca = new NDX_MULTI_COLUMN_MENU ($this->desired_columns, $this->max_rows_per_column);
			$column_totals = $mca->create_multi_column_menu ($category_totals);
			
			$column_totals_string = $mca->category_total_to_string ();	

			unset ($mca);
		
			
			// now we build the subcategory menu
			$i=-1;
			$ncats_in_col = 0;
			$ncol = 0;
			$output_2 = "";
			foreach ($categories[$parentid] as $subCat) {
				$subparentid = $subCat['categoryid'];
				//$subclass_count = count($categories[$subparentid]) + 1; // undefined index mz
				if (isset($categories[$subparentid])) { 
					$subclass_count = count($categories[$subparentid]) + 1; 
				} else {
					$subclass_count = 1;
				}				
				
				// end of a column
				if ($ncats_in_col < 1){
					
					// end the column
					if ($ncol > 0) {
						// not the first
						$output_2 .= "     </div>\n";
					}
					
					// start the next col
					$ncol++;
					$ncats_in_col = count($column_totals[++$i]);
					$output_2 .= "     <div class=\"column\">\n";
				}
				$ncats_in_col--;

				$output_2 .= $this->subcat2 ($categories, $subCat);
			} // end inner foreach
			
			if ($ncol > 0) {
				$output_2 .= "     </div>\n";
			}
		
			// process category level 1 - start
			
//			$GLOBALS['PageMenuList'] = $output_2;
			$GLOBALS['PageMenuList'] = "";
			$GLOBALS['LastChildClass']='';
			$GLOBALS['CategoryName'] = isc_html_escape($rootCat['catname']);
			$badchar=array(" ","/"); // sdw
			$GLOBALS['CategoryNameValidFileName'] = str_replace($badchar,"-", strtolower($GLOBALS['CategoryName'])); //sdw
			$GLOBALS['CategoryLink'] = CatLink($rootCat['categoryid'], $rootCat['catname'], true);
			$GLOBALS['CategoryId'] = $rootCat['categoryid'];
			$GLOBALS['AnchorId'] = $anchor_id;
			$GLOBALS['MenuId'] = $menu_id;
			
			$output = $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("PageMenu");
			
			if ($output_2 != "") {
				$output_2 = "<div id='{$menu_id}' class='megamenu'> \n{$output_2}</div>\n";
			}
//			echo "\n{$output_2}\n";
			$total_output_2 .= $output_2;
			
			$total_output .= $output;
		} // end outer (main menu bar) foreach
		
		
		if(!$total_output) {
			$this->DontDisplay = true;
			return;
		}
		
		$total_output = "\n<script type=\"text/javascript\">\n" . $output_js . "</script>\n" . $total_output;
		$total_output2 = "<div>\n" .$total_output_2. "</div>";
		
		// return all
		$GLOBALS['SNIPPETS']['PageMenu1'] = $total_output;
		$GLOBALS['SNIPPETS']['PageMenu2'] = $total_output2;
		
		$GLOBALS['ISC_CLASS_DATA_STORE']->Save('mega1', $total_output);
		$GLOBALS['ISC_CLASS_DATA_STORE']->Save('mega2', $total_output2);
		
	} // end function

	function subcat2 (&$categories, $subCat) 
	{
		// If we don't have permission to view this category then skip
		if (!CustomerGroupHasAccessToCategory($subCat['categoryid'])) {
			continue;
		}

		$catLink = CatLink($subCat['categoryid'], $subCat['catname'], false);
		$catName = isc_html_escape($subCat['catname']);
		
		$output_3 = ""; //mz 
		$subparentid = $subCat['categoryid'];  
		if (isset ($categories[$subparentid])) {
			$output_3 = $this->subcat3 ($categories, $subparentid, $catName, $catLink);
		}		

		$GLOBALS['PageMenuList'] = $output_3;
		$GLOBALS['CategoryName'] = $catName;
		$GLOBALS['CategoryLink'] = $catLink;
		$output = $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("PageSubMenu");			
		
		return $output;
	}

	function subcat3 (&$categories, $parentid, $parentCatName, $parentCatLink)
	{
		$output = "";
		$subcat_count = 0;
		$moreLimit = $this->max_before_more + 1;
		
		foreach ($categories[$parentid] as $subCat) { 
			
			// If we don't have permission to view this category then skip
			if (!CustomerGroupHasAccessToCategory($subCat['categoryid'])) {
				continue;
			}
			
			//sdw 2012_02_02
			$subcat_count++;
			if ($this->max_before_more > 0) {
				if ($subcat_count == $moreLimit) {
					$GLOBALS['PageMenuList'] = "";
					$GLOBALS['CategoryName'] = $parentCatName;
					$GLOBALS['CategoryLink'] = $parentCatLink;
					$output .= $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("PageSubMenu3More");					
					break;
				} elseif ($subcat_count > $moreLimit) {					
					break;
				}
			}
	
			$catLink = CatLink($subCat['categoryid'], $subCat['catname'], false);
			$catName = isc_html_escape($subCat['catname']);
			$GLOBALS['PageMenuList'] = "";
			
			//set the class for the last category of its parent category
	
			$GLOBALS['CategoryName'] = $catName;
			$GLOBALS['CategoryLink'] = $catLink;
			$output .= $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("PageSubMenu3");			
		}
		if ($output != "") {
			$output = "	<ul>\n".$output."\n	</ul>\n";
		}
		return $output;
	}
	
} // end class

?>