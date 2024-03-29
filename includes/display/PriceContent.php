<?php

	CLASS ISC_PRICECONTENT_PANEL extends PANEL
	{
		public function SetPanelSettings()
		{

			// Should we hide the comparison button?
			if(GetConfig('EnableProductComparisons') == 0 || $GLOBALS['ISC_CLASS_PRICE']->GetNumProducts() < 2) {
				$GLOBALS['HideCompareItems'] = "none";
			}

			// Load the products into the reference array
			$GLOBALS['ISC_CLASS_PRICE']->GetProducts($products);
			$GLOBALS['PriceProductListing'] = "";

			if(!GetConfig('ShowProductRating')) {
				$GLOBALS['HideProductRating'] = "display: none";
			}

			$display_mode = ucfirst(GetConfig("CategoryDisplayMode"));
			if ($display_mode == "Grid") {
				$display_mode = "";
			}
			$GLOBALS['DisplayMode'] = $display_mode;

			if ($display_mode == "List") {
				if (GetConfig('ShowAddToCartLink') && $GLOBALS['ISC_CLASS_PRICE']->GetNumProducts() > 0) {
					$GLOBALS['HideAddButton'] = '';
				} else {
					$GLOBALS['HideAddButton'] = 'none';
				}

				$GLOBALS['ListJS'] = $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("ListCheckForm");
			}

			$GLOBALS['CompareButton'] = $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("CompareButton" . $display_mode);

			if ($display_mode == "List" && $GLOBALS['ISC_CLASS_PRICE']->GetNumPages() > 1) {
				$GLOBALS['CompareButtonTop'] = $GLOBALS['CompareButton'];
			}


			$GLOBALS['AlternateClass'] = '';
			foreach($products as $row) {
				if($GLOBALS['AlternateClass'] == 'Odd') {
					$GLOBALS['AlternateClass'] = 'Even';
				}
				else {
					$GLOBALS['AlternateClass'] = 'Odd';
				}


				$GLOBALS['ProductCartQuantity'] = '';
				if(isset($GLOBALS['CartQuantity'.$row['productid']])) {
					$GLOBALS['ProductCartQuantity'] = (int)$GLOBALS['CartQuantity'.$row['productid']];
				}

				$GLOBALS['ProductId'] = (int) $row['productid'];
				$GLOBALS['ProductName'] = isc_html_escape($row['prodname']);
				$GLOBALS['ProductLink'] = ProdLink($row['prodname']);
				$GLOBALS['ProductRating'] = (int)$row['prodavgrating'];

				// Determine the price of this product
				$GLOBALS['ProductPrice'] = CalculateProductPrice($row);

				$GLOBALS['ProductThumb'] = ImageThumb($row['imagefile'], ProdLink($row['prodname']));

				if (isId($row['prodvariationid']) || trim($row['prodconfigfields'])!='' || $row['prodeventdaterequired'] == 1) {
					$GLOBALS['ProductURL'] = ProdLink($row['prodname']);
					$GLOBALS['ProductAddText'] = GetLang('ProductChooseOptionLink');
				} else {
					$GLOBALS['ProductURL'] = CartLink($row['productid']);
					$GLOBALS['ProductAddText'] = GetLang('ProductAddToCartLink');
				}

				if (CanAddToCart($row) && GetConfig('ShowAddToCartLink')) {
					$GLOBALS['HideActionAdd'] = '';
				} else {
					$GLOBALS['HideActionAdd'] = 'none';
				}

				$GLOBALS['HideProductVendorName'] = 'display: none';
				$GLOBALS['ProductVendor'] = '';
				if(GetConfig('ShowProductVendorNames') && $row['prodvendorid'] > 0) {
					$vendorCache = $GLOBALS['ISC_CLASS_DATA_STORE']->Read('Vendors');
					if(isset($vendorCache[$row['prodvendorid']])) {
						$GLOBALS['ProductVendor'] = '<a href="'.VendorLink($vendorCache[$row['prodvendorid']]).'">'.isc_html_escape($vendorCache[$row['prodvendorid']]['vendorname']).'</a>';
						$GLOBALS['HideProductVendorName'] = '';
					}
				}


				// for list style
				if ($display_mode == "List") {
					// get a small chunk of the product description
					$GLOBALS['ProductDescription'] = isc_substr(strip_tags($row['proddesc']), 0, 250);
					if (isc_strlen($row['proddesc']) > 250) {
						$GLOBALS['ProductDescription'] .= "...";
					}

					$GLOBALS['AddToCartQty'] = "";

					if (CanAddToCart($row) && GetConfig('ShowAddToCartLink')) {
						if (isId($row['prodvariationid']) || trim($row['prodconfigfields'])!='' || $row['prodeventdaterequired']) {
							$GLOBALS['AddToCartQty'] = '<a href="' . $GLOBALS["ProductURL"] . '">' . $GLOBALS['ProductAddText'] . "</a>";
						}
						else {
							$GLOBALS['CartItemId'] = $GLOBALS['ProductId'];
							// If we're using a cart quantity drop down, load that
							if (GetConfig('TagCartQuantityBoxes') == 'dropdown') {
								$GLOBALS['Quantity0'] = "selected=\"selected\"";
								$GLOBALS['QtyOptionZero'] = '<option %%GLOBAL_Quantity0%% value="0">Quantity</option>';
								$GLOBALS['QtySelectStyle'] = 'width: auto;';
								$GLOBALS['AddToCartQty'] = $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("CartItemQtySelect");
							// Otherwise, load the textbox
							} else {
								$GLOBALS['ProductQuantity'] = 0;
								$GLOBALS['AddToCartQty'] = $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("CartItemQtyText");
							}
						}
					}
				} // for grid style
				else {
					$GLOBALS["CompareOnSubmit"] = "onsubmit=\"return compareProducts(config.CompareLink)\"";
				}

				$GLOBALS['PriceProductListing'] .= $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("CategoryProductsItem" . $display_mode);
			}

			if($GLOBALS['ISC_CLASS_PRICE']->GetNumProducts() == 0) {
				// There are no products in this category
				$GLOBALS['PriceProductListing'] = $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("CategoryNoProductsMessage");
			}
		}
	}