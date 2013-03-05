<?php

class ISC_CARTCONTENT1_PANEL extends PANEL
{

	/**
	 * Set the settings to display this panel.
	 */
	public function setPanelSettings()
	{
//$GLOBALS['ShippingCountryList'] = GetCountryList($selectedCountry);
$GLOBALS['ShippingStateList'] = GetStateListAsOptions(226);
//$GLOBALS['ShippingStateName'] = isc_html_escape($selectedStateName);

	}
}