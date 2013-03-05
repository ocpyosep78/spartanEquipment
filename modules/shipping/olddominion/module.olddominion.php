<?php
class SHIPPING_OLDDOMINION extends ISC_SHIPPING
 {

//variables go here

/**
		* Variables for the OldDominion shipping module
		*/

		/*
			The delivery type for OldDominion shipments
		*/
		private $_deliverytype = "";

		/*
			The destination country ISO code for OldDominion shipments
		*/
		private $_destcountry = "";

		/*
			The destination country zip for OldDominion shipments
		*/
		private $_destzip = "";

		/*
			The shipping rate OldDominion shipments
		*/
		private $_shippingrate = "";

		/*
			The packaging type for OldDominion shipments
		*/
		private $_packagingtype = "";

		/*
			The destination type (residential or commercial) for OldDominion shipments
		*/
		private $_destination = "";

		/*
			Shipping class constructor
		*/
public function __construct()
	{
		// Setup the required variables for the OldDominionshipping module
		parent::__construct();
		$this->_name = GetLang('OldDominionName');
		$this->_image = "masthead_logo.png";
		$this->_description = GetLang('OldDominionDesc');
		$this->_help = GetLang('OldDominionHelp');
		$this->_height = 310;

		$this->_deliverytypes = array(
		//here we will put the service types example as follows:
				"PRIORITY_OVERNIGHT" => GetLang('OldDominionServiceType1')
			);

		//Here we will put the countries we will support. For old dominion we will only support the United States as far as I can tell
		$this->_countries = array("United States");
	}

	public function SetCustomVars()
	{//the following is the format for custom variables which will be used in the admin section when setting up Old Dominion
	//example as follows 
	$this->_variables['NMFCclass'] = array("name" => "nmfc class",
		   "type" => "dropdown",
		   "help" => GetLang('OldDominionHelp'),
		   "default" => "",
		   "required" => true,
		   "options" => array("50" => "50",
							  "55" => "55",
							  "60" => "60",
							  "65" => "65",
							  "70" => "70",
							  "77" => "77",
							  "85" => "85",
							  "92" => "92",
							  "100" => "100",
							  "110" => "110",
							  "125" => "125",
							  "150" => "150",
							  "175" => "175",
							  "200" => "200",
							  "250" => "250",
							  "300" => "300",
							  "400" => "400",
							  "500" => "500"
							),
				"multiselect" => false
		);
		
		$this->_variables['odfl4meUser'] = array("name" => "odfl4meUser",
		   "type" => "textbox",
		   "help" => GetLang('OldDominionHelp'),
		   "default" => "",
		   "required" => false
		);
		
		$this->_variables['odfl4mePassword'] = array("name" => "odfl4mePassword",
		   "type" => "password",
		   "help" => GetLang('OldDominionHelp'),
		   "default" => "",
		   "required" => false
		);
		
		$this->_variables['odflCustomerAccount'] = array("name" => "odflCustomerAccount",
		   "type" => "textbox",
		   "help" => GetLang('OldDominionHelp'),
		   "default" => "",
		   "required" => false
		);
		
		$this->_variables['RequestorType'] = array("name" => "RequestorType",
		   "type" => "dropdown",
		   "help" => GetLang('OldDominionHelp'),
		   "default" => "S",
		   "required" => false,
		   "options" => array("S"=>"S",
			 				  "C"=>"C"
							 ),
				"multiselect" => false
		);
		
	}
	
	/**
	* Test the shipping method by displaying a simple HTML form
	*/
	public function TestQuoteForm()
	{

			// Load up the module variables
			$this->SetCustomVars();
			$GLOBALS['WeightUnit'] = GetConfig('WeightMeasurement');

			// Which countries has the user chosen to ship orders to?
			$GLOBALS['Countries'] = GetCountryList("United States");

			$GLOBALS['Image'] = $this->_image;

			$this->ParseTemplate("module.olddominion.test");
	}
	
	public function TestQuoteResult()
	{

		// Add a single test item - no dimensions needed for OldDominion
		$this->additem($_POST['delivery_weight']);

		// Setup all of the shipping variables
		$this->_destcountry = GetCountryISO2ById($_POST['delivery_country']);
		$this->_destzip = $_POST['delivery_zip'];
		
			// Next actually retrieve the quote
		$result = $this->GetQuote();

		if(!is_object($result) && !is_array($result)) {
			$GLOBALS['Color'] = "red";
			$GLOBALS['Status'] = GetLang('StatusFailed');
			$GLOBALS['Label'] = GetLang('ShipErrorMessage');
			$GLOBALS['Message'] = implode('<br />', $this->GetErrors());
		}
		else {
			$GLOBALS['Color'] = "green";
			$GLOBALS['Status'] = GetLang('StatusSuccess');
			$GLOBALS['Label'] = GetLang('ShipQuotePrice');

			// Get each available shipping option and display it
			$GLOBALS['Message'] = "";

			if(!is_array($result)) {
				$result = array($result);
			}

			foreach($result as $quote) {
				if(count($result) > 1) {
					$GLOBALS['Message'] .= "<li>";
				}

				$GLOBALS['Message'] .= $quote->getdesc(false) . " - $" . $quote->getprice() . " USD";

				if(count($result) > 1) {
					$GLOBALS['Message'] .= "</li>";
				}
			}
		}

		$GLOBALS['Image'] = $this->_image;
		$this->ParseTemplate("module.olddominion.testresult");
	}
	
	private function GetQuote()
	{
		$weight = $this->_weight;
		$origin_zip = $this->_origin_zip;
		$dest_zip = $this->_destzip;
		$NmfcClass = $this->GetValue('NMFCclass');
		$odfl4meUser = $this->GetValue('odfl4meUser');
		$odfl4mePassword = $this->GetValue('odfl4mePassword');
		$odflCustomerAccount = $this->GetValue('odflCustomerAccount');
		$requestorType = $this->GetValue('RequestorType');
		
		if ($weight < 0.1) {
			$weight = 0.1;
		}
		
		$freightArray = array(
			'nmfcClass' => (int)$NmfcClass,
			'weight'=> $weight,
			'length' => '',
			'width' => '',
			'height'=> '',
			'handlingUnits' => '',
			'density' => ''
		);
	
		$accessorialArray = array(
			//'code' => '',
			//'value'=> ''
		);
		
		$rateRequest = array(
			'originPostalCode' => $origin_zip,
			'originCountry' => 'usa',
			'destinationPostalCode' => $dest_zip,
			'destinationCountry' => 'usa',
			'requestorType' => $requestorType,
			'odfl4meUser' => $odfl4meUser,
			'odfl4mePassword' => $odfl4mePassword,
			'odflCustomerAccount' => $odflCustomerAccount,
			'freightArray' => $freightArray,
			'accessorialArray' => $accessorialArray,
			'mexicoServiceCenter' => '',
			'currencyFormat' => '',
			'requestReferenceNumber' =>''
		);

		
		try{
			$client = new SoapClient('https://www.odfl.com/wsRate_v1/services/ODRate/wsdl/ODRate-v1.wsdl'); 
			
			$valid_quote = $client->getRateEstimate($rateRequest);
			
			if(!isset($valid_quote->rateReturn->errorMessages)) {
				$quote = new ISC_SHIPPING_QUOTE($this->GetId(), $this->GetDisplayName(), (float)$valid_quote->rateReturn->rateEstimate->netFreightCharge, "Freight");
				return $quote;
			}
			else{
				return false;
			}
		}catch (Exception $e) {
				return false;
			//I don't want to output the error message to a live site for potential security flaws. 
			//echo $e->getMessage();	
		}	
		
		return $olddominion_quote;
	}
	
	public function GetServiceQuotes()
		{
			$this->ResetErrors();
			$QuoteList = array();
			// Set the OldDominion-specific variables
			$this->_destcountry = $this->_destination_country['country_iso'];
			$this->_destzip = $this->_destination_zip;

			if($this->_destination_rescom == "COM") {
				$this->_destination = "0";
			} else {
				$this->_destination = "1";
			}

			// Convert the weight to pounds
			$this->_weight = ConvertWeight($this->_weight, 'pounds');

				$err = "";
				$result = $this->GetQuote($err);

				// Was it a valid quote?
				if(is_object($result)) {
					array_push($QuoteList, $result);
				// Invalid quote, log the error
				} else {
					foreach($this->GetErrors() as $error) {
						$GLOBALS['ISC_CLASS_LOG']->LogSystemError(array('shipping', $this->GetName()), $this->_deliverytypes[$service].": " .GetLang('ShippingQuoteError'), $error);
					}
			}
			return $QuoteList;
		}

		/**
		 * Get a human readable list of of the delivery methods available for the shipping module
		 *
		 * @return array
		 **/
		public function GetAvailableDeliveryMethods()
		{
		}

		/**
		* Generate a link to track items for OldDominion.
		*
		* @return string The tracking URL for OldDominion shipments.
		*/
		public function GetTrackingLink($trackingNumber = "")
		{
			//return "http://www.ups.com/WebTracking/track?loc=en_US&WT.svl=PNRO_L1";
		}
}
