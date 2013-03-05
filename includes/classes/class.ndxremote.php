<?php
class ISC_NDXREMOTE
{
	public function HandleToDo()
	{
		$quantity = array_key_exists('quantity_text', $_REQUEST) ?  $_REQUEST['quantity_text']: $_REQUEST['quantity_select'];
		//$stateId =  $_REQUEST['stateId'];
		$zipCode = $_REQUEST['zipCode'];
		$productId = $_REQUEST['product_id'];
		$weight = $_REQUEST['weight'];
		
		if ($quantity < 1 || !is_numeric($quantity)) {
			$quantity = 1;	
		}
		
		if ($weight == 0 || !is_numeric($weight)) {
			echo "Not a valid ZIP code. <br/>Please call us for shipping prices at 1-888-888-1085";
			return;
		}
		$weight = $weight * $quantity;
		$shippingmodule = $weight<150 ? "UPS" : "OLDDOMINION"; 
		
		switch($shippingmodule){
			case "UPS":
				try{
					$ratearray = $this->ndx_upsshippingestimate($weight, $zipCode);
					if (count($ratearray) < 2) {
						throw new Exception("<b>Not a valid ZIP code.</b> <br/></n> Please call us for shipping prices at 1-888-888-1085");
					}
					echo "<table> <tr><td>SHIPPING METHOD</td> <td>PRICE</td>";
					
					foreach($ratearray as $key=>$value){
						$value = sprintf("%01.2f",$value);
						echo "<tr><td>$key</td><td style='color:red;'>" .'$' .$value ."</td><tr>";					
					}
					echo "</table>";
				}
				catch(Exception $e){
					echo $e->getMessage();
				}
					break;
			case "OLDDOMINION":
				try {
					$rate = $this->ndx_odshippingestimate($weight, $zipCode);
					
					if(empty($rate)){
						throw new Exception("Not a valid ZIP code. <br/>Please call us for shipping prices at 1-888-888-1085");
					}
					$rate = sprintf("%01.2f", $rate);
					echo "<table>
					<tr><td>SHIPPING METHOD</td> <td>PRICE</td>
					<tr><td>OLD DOMINION(FREIGHT)</td><td style='color:red;'>" .'$' ."$rate</td><tr>						
					</table>";
				} catch(Exception $e){
					echo $e->getMessage();	
				}
				break;	
			default:
				try {
					$rate = $this->ndx_odshippingestimate($weight, $zipCode);
					
					if(empty($rate)){
						throw new Exception("Not a valid ZIP code. <br/>Please call us for shipping prices at 1-888-888-1085");
					}
					
					$rate = sprintf("%01.2f", $rate);
					echo "<table>
					<tr><td>SHIPPING METHOD</td> <td>PRICE</td>
					<tr><td>OLD DOMINION(FREIGHT)</td><td style='color:red;'>" .'$' ."$rate</td><tr>
										</table>";
					
				} catch(Exception $e){
					echo $e->getMessage();	
				}
				break;	
		}// end of switch statement
		
	}//end handletodo				
		
	public function ndx_upsshippingestimate($weight, $zipCode)
	{
		$ups_quote = array();
		$deliverytypes = array();
		
		$query = 'SELECT 
			      sv.variableval
				  FROM [|PREFIX|]zip_codes AS z
			  	  INNER JOIN [|PREFIX|]country_states AS s ON s.stateabbrv = z.state_code
			   	  INNER JOIN [|PREFIX|]shipping_zone_locations AS szl ON szl.locationvalueid = s.stateid
				  INNER JOIN [|PREFIX|]shipping_vars AS sv ON sv.zoneid =  szl.zoneid
				  WHERE zip_code = "' . $zipCode . '"' .
				  ' AND sv.modulename = "' . 'shipping_ups"' .
				  ' AND sv.variablename = "' . 'deliverytypes"';
		
		$result = $GLOBALS['ISC_CLASS_DB']->Query($query);
		
		/*UPSDeliveryType1 = "Next Day Air Early AM";
		UPSDeliveryType2 = "Next Day Air";
		UPSDeliveryType3 = "Next Day Air Saver";
		UPSDeliveryType4 = ""Next Day Air Saver";
		UPSDeliveryType5 = "2nd Day Air";
		UPSDeliveryType6 = "3 Day Select";
		UPSDeliveryType7 = "Ground";
		UPSDeliveryType8 = "Canada Standard";
		UPSDeliveryType9 = "Worldwide Express";
		UPSDeliveryType10 = "Worldwide Express Plus";
		UPSDeliveryType11 = "Worldwide Expedited";*/
		
		while ($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)) {
			$lang = '';
			switch ($row['variableval']) {
				case'1DM':
					$deliverytypes["Next Day Air Early AM"] = $row['variableval'];
					break;
				case'1DA':
					$deliverytypes["Next Day Air"] = $row['variableval'];
					break;
				case'1DP':
					$deliverytypes["Next Day Air Saver"] = $row['variableval'];
					break;
				case'2DM':
					$deliverytypes["Next Day Air Saver"] = $row['variableval'];
					break;
				case'2DA':
					$deliverytypes["2nd Day Air"] = $row['variableval'];
					break;
				case'3DS':
					$deliverytypes['3 Day Select'] = $row['variableval'];
					break;
				case'GND':
					$deliverytypes['Ground'] = $row['variablename'];
					break;
				case'STD':
					$deliverytypes['Canada Standard'] = $row['variablename'];
					break;
				case'XPR':
					$deliverytypes["Worldwide Express"] = $row['variableval'];
					break;
				case'XDM':
					$deliverytypes["Worldwide Express Plus"] = $row['variableval'];
					break;
				case'XPD':
					$deliverytypes["Worldwide Expedited"] = $row['variableval']; 
					break;
			}
		}
		
		$resultarray = array();
		
		// Connect to UPS.com to retrieve a live shipping quote
		$result = "";
		$valid_quote = false;
		$action = "3";
		$ups_url = "http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes&";
		
		$destZip = $zipCode;
		
		$zip = preg_replace('#^(\d{5})-?\d{4}$#', '\1', $zipCode);
		$price= '';
		
		$originZip = GetConfig('CompanyZip');
		$rate = 0;
		foreach($deliverytypes as $lang => $deliverytype){

			$post_vars = array(
			"10_action"       => $action,
			"13_product"      => $deliverytype,
			"14_origCountry"  => 'US',
			"15_origPostal"   => $originZip,
			"19_destPostal"   => $destZip,
			"22_destCountry"  => 'US',
			"23_weight"       => $weight,
			"47_rate_chart"   => "Regulary Daily Pickup",
			"48_container"    => '00',
			"49_residential"  => '1',
			);
			
			// build a query here for use with either curl or fopen (though, this should probably be
			// using PostToRemoteFileAndGetResponse)
			
			$post_vars = http_build_query($post_vars);
			
			if(function_exists("curl_exec")) {
				// Use CURL if it's available
				$ch = @curl_init($ups_url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
				curl_setopt($ch, CURLOPT_TIMEOUT, 60);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
				// Setup the proxy settings if there are any
				if (GetConfig('HTTPProxyServer')) {
					curl_setopt($ch, CURLOPT_PROXY, GetConfig('HTTPProxyServer'));
					if (GetConfig('HTTPProxyPort')) {
						curl_setopt($ch, CURLOPT_PROXYPORT, GetConfig('HTTPProxyPort'));
					}
				}
			
				if (GetConfig('HTTPSSLVerifyPeer') == 0) {
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				}
				
				$result = curl_exec($ch);
				
				if($result != "") {
					$valid_quote = true;
				}
			}
			else {
				// Use fopen instead
				if($fp = @fopen($ups_url . $post_vars, "rb")) {
					$result = "";
					
					while(!feof($fp))
					$result .= fgets($fp, 4096);
					
					@fclose($fp);
					$valid_quote = true;
				}
			}
					
			if($valid_quote) {
				$result = explode("%", $result);
				
				if(count($result) > 5) {
					$Error = false;
					$quote_desc = "";
					$resultarray[$lang] = $result[8];
				}
			} else {
				$this->SetError($result[1]);
				return false;
			}
		}//end of loop
		
		return $resultarray;
	}

	public function ndx_odshippingestimate($weight, $destzipcode)
	{
		$query = 'SELECT 
			      sv.*
				  FROM [|PREFIX|]zip_codes AS z
			  	  INNER JOIN [|PREFIX|]country_states AS s ON s.stateabbrv = z.state_code
			   	  INNER JOIN [|PREFIX|]shipping_zone_locations AS szl ON szl.locationvalueid = s.stateid
				  INNER JOIN [|PREFIX|]shipping_vars AS sv ON sv.zoneid =  szl.zoneid
				  WHERE zip_code = "' . $destzipcode . '"' .
				  ' AND sv.modulename = "' . 'shipping_olddominion"';
	
		    $result = $GLOBALS['ISC_CLASS_DB']->Query($query);
			//set default values
			$requestorType = '';
			$customerAccount = '';
			$password = '';
			$user = '';
			$nmfcClass = '';
			while ($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)) {
				switch ($row['variablename']) {
					case "RequestorType":
						$requestorType = $row['variableval'];
						break;
					case "odflCustomerAccount":
						$customerAccount = $row['variableval'];
						break;
					case "odfl4mePassword":
						$password = $row['variableval'];
						break;
					case "odfl4meUser":
						$user = $row['variableval'];
						break;
					case "NMFCclass":
						$nmfcClass = $row['variableval'];
						break;
				}
			}
		if (empty($requestorType) || empty($customerAccount) || empty($password) || empty($user) || empty($nmfcClass)) {
			return false;
		}
		
		$weight = round($weight);
		$originZip = GetConfig('CompanyZip');
		$rate = 0;
		
		$freightArray = array(
			'nmfcClass'     => $nmfcClass,
			'weight'        => $weight,
			'length'        =>'',
			'width'         => '',
			'height'        => '',
			'handlingUnits' => '',
			'density'       => ''
		);
		
		$accessorialArray = array();
			
		$rateRequest = array(
			'originPostalCode'       => $originZip,
			'originCountry'          => 'usa',
			'destinationPostalCode'  => $destzipcode,
			'destinationCountry'     => 'usa',
			'requestorType'          => $requestorType,
			'odfl4meUser'            => $user,
			'odfl4mePassword'        => $password,
			'odflCustomerAccount'    => $customerAccount,
			'freightArray'           => $freightArray,
			'accessorialArray'       => $accessorialArray,
			'mexicoServiceCenter'    => '',
			'currencyFormat'         => '',
			'requestReferenceNumber' =>''
		);
	
		$client = new SoapClient('http://www.odfl.com/wsRate_v1/services/ODRate/wsdl/ODRate-v1.wsdl');
		$valid_quote = $client->getRateEstimate($rateRequest);
		
		if(isset($valid_quote->rateReturn->errorMessages)) {
			return $rate;
		}
			$rate = (float)$valid_quote->rateReturn->rateEstimate->netFreightCharge;
			return $rate;
	}
}//end class