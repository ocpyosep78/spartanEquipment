<?php

class ISC_FINANCE
{
    var $fields = array(
        "bus_name" => "",
        "dba_name" => "",
        "bus_type" => "",
        "bus_time" => "",
        "time_in_bus" => "",
        "bus_credit" => "",
        "bus_address" => "",
        "bus_city" => "",
        "bus_state" => "",
        "bus_zip" => "",
        "bus_phone" => "",
        "bus_fax" => "",
        "tax_id" => "",
        "website" => "",
        "comments" => "",
        "equip_cost" => "",
        "equip_type" => "",
        "desired_term" => "",
        "ship_to" => "",
        "ship_to_address_other" => "",
        "ship_to_city_other" => "",
        "ship_to_state_other" => "",
        "ship_to_zip_other" => "",
        "equip_desc" => "",
        "first_name" => "",
        "last_name" => "",
        "title" => "",
        "ownership" => "",
        "ssn" => "",
        "address" => "",
        "city" => "",
        "state" => "",
        "zip" => "",
        "home_phone" => "",
        "mobile_phone" => "",
        "email" => "",
        "second_owner" => "",
        "first_name_second_owner" => "",
        "last_name_second_owner" => "",
        "title_second_owner" => "",
        "ownership_second_owner" => "",
        "ssn_second_owner" => "",
        "address_second_owner" => "",
        "city_second_owner" => "",
        "state_second_owner" => "",
        "zip_second_owner" => "",
        "home_phone_second_owner" => "",
        "mobile_phone_second_owner" => "",
        "email_second_owner" => "",
        "accept_terms" => "",
        "productId" => "",
        "productName" => ""
    );
    /*
     * enter all files types in lowercase
     */
    var $validExt = array(
        "jpg",
        "jpeg",
        "ai",
        "eps",
        "tiff",
        "tif",
        "png",
        "gif",
        "bmp",
        "doc",
        "docx"
    );
    var $image = array();
    var $tmpDirectory = "previewTmp/";
    var $targetPath = "";
    var $toEmail = "jfranco@newdynamx.com";
    var $fromEmail = "preview@promotionalproductsonline.com";
    var $fileType = "";
    var $imageContents = "";
    var $minSize = 100000;//100kb
    var $maxSize = 5000000;//5mb

    public function HandlePage()
    {
        $action = "";
        if(isset($_REQUEST['action'])) {
            $action = isc_strtolower($_REQUEST['action']);
        }else{
            $this->goToHomePage();
        }

        switch($action) {
            case "finance":
                $this->viewFinanceForm();
                break;

            case "submit":
                $this->processForm();
                break;

            case "thankyou":
                $this->loadThankYouPage();
                break;

            default:
                $this->goToHomePage();

        }
    }

    public function processForm()
    {
        if(!empty($_POST))
        {
            $this->setFormData();
            $this->validateFields();
            $this->save_upload_image();
            $this->setFileContents();
            $this->sendPreviewEmail();
            $this->deleteUploadedImage();
            $this->goToThankYouPage();
        }
    }

    public function deleteUploadedImage()
    {
        if(!empty($this->image) && $this->image['name'] != '')
        {
            unlink($this->getTargetPath());
        }
    }

    public function sendPreviewEmail()
    {
        $this->setGlobals();

        $emailTemplate = FetchEmailTemplateParser();
        $emailTemplate->SetTemplate("product_preview_email");
        $message = $emailTemplate->ParseTemplate(true);

        $sendTo = $this->getToEmail();
        $store_name = GetConfig('StoreName');
        $subject = sprintf("Product Preview Requested", $store_name);
        require_once(ISC_BASE_PATH . "/lib/email.php");
        $obj_email = GetEmailClass();
        $obj_email->Set('CharSet', GetConfig('CharacterSet'));
        $obj_email->From(GetConfig('OrderEmail'), $store_name);
        $obj_email->Set('Subject', $subject);
        $obj_email->AddBody("html", $message);
        $obj_email->AddRecipient($sendTo , "", "h");
        $obj_email->AddAttachmentData($this->getFileContents(), 'image.'.$this->getFileType());
        $email_result = $obj_email->Send();
    }

    public function save_upload_image ()
    {
        if(!empty($this->image) && $this->image['name'] != '')
        {
            $files_entry = $this->image;
            // check if we know how to deal with this file type
            // isolate the file type
            $fn = basename($files_entry['name']);
            $this->setFileType($this->get_wfiletype($fn));
            $ft = $this->getFileType();

            if (! in_array(strtolower($ft), $this->validExt)) {

                $etype = implode (", ", $this->validExt);
                return ("we can only process these file types: $etype");
            }

            $this->setTargetPath($files_entry['name']);
            $target_path = $this->getTargetPath();
            $target_dir_noslash = substr ($this->tmpDirectory, 0, strlen($this->tmpDirectory)-1);

            // does directory exist?
            if (! move_uploaded_file($files_entry['tmp_name'], $_SERVER["DOCUMENT_ROOT"]."/".$target_path))	{
                return "Error uploading file (#3)";
            }
            return TRUE;
        }
    }

    public function setFileContents()
    {
        $location = $_SERVER["DOCUMENT_ROOT"]."/".$this->getTargetPath();
        $this->imageContents = file_get_contents($location);
    }

    public function getFileContents()
    {
        return $this->imageContents;
    }

    public function setFileType($value)
    {
        $this->fileType = $value;
    }

    public function getFileType()
    {
        return $this->fileType;
    }

    public function getToEmail()
    {
        return $this->toEmail;
    }

    public function setTargetPath($name)
    {
        $this->targetPath = $this->tmpDirectory . $name;
    }

    public function getTargetPath()
    {
        return $this->targetPath;
    }

    public function getMinSize()
    {
        return $this->minSize;
    }

    public function getMaxSize()
    {
        return $this->maxSize;
    }

    public function get_wfiletype ($fn)
    {
        //	return (substr($fn,strrpos($fn, '.')+1));
        return (substr(strrchr($fn, '.'), 1));
    }

    public function validateFields()
    {
        $err = false;
        $GLOBALS['errMess'] = "";
        if($this->fields['first_name'] == "")
        {
            $err = true;
        }
        if($this->fields['last_name'] == "")
        {
            $err = true;
        }
        if($this->fields['email'] == "")
        {
            $err = true;
        }
        if($this->fields['preferred_phone'] == "")
        {
            $err = true;
        }
        if($this->validateEmail($this->fields['email']))
        {
            $this->setFieldArray("email", "");
            $err = true;
        }

        if(!empty($this->image) && $this->image['name'] != '')
        {
            $file_parts = pathinfo($this->image['name']);
            if(!in_array(strtolower($file_parts['extension']), $this->validExt))
            {
                $err = true;
            }
            if($this->image['size'] < $this->getMinSize())//100kb
            {
                $err = true;
            }
            if($this->image['size'] > $this->getMaxSize())//5mb
            {
                $err = true;
            }
        }
        if($err)
        {
            $this->setGlobals();
            $_REQUEST['product'] = $this->fields['productId'];
            $GLOBALS['errMess'] = "Error processing data. Please try again";
            $GLOBALS['errDisplay'] = " style='display: block;color: red'";
            $this->viewProductPreview();
        }

    }

    public function validateEmail($email)
    {
        $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
        if (!preg_match($pattern, $email) === 1) {
            return true;
        }
        return false;
    }

    public function setGlobals()
    {
        foreach($this->fields as $key => $val)
        {
            $GLOBALS[$key] = $val;
        }
    }

    public function setFormData()
    {
        foreach($_POST as $key => $val)
        {
            if($val != "")
            {
                $this->setFieldArray($key, $val);
            }
        }
        if(!empty($_FILES))
        {
            $this->setImageArray($_FILES['image']);
        }
    }

    public function setImageArray($val)
    {
        $this->image = $val;
    }

    public function setFieldArray($field, $val)
    {
        $this->fields[$field] = trim(addslashes($val));
    }

    public function viewFinanceForm()
    {
        if(!isset($GLOBALS['errMess']))
        {
            $GLOBALS['errMess'] = "";
            $GLOBALS['errDisplay'] = " style='display: none'";
        }
        if(!isset($_REQUEST['product']))
        {
            $this->goToHomePage();
        }
        $productId = $_REQUEST['product'];
        $productInfo = $this->getProductInfo($productId);
        $GLOBALS['productName'] = $productInfo['prodname'];
        $GLOBALS['prodcode'] = $productInfo['prodcode'];
        $GLOBALS['productImage'] = $GLOBALS['ISC_CFG']["ShopPath"]."/".$GLOBALS['ISC_CFG']["ImageDirectory"]."/".$productInfo['imagefilestd'];
        $GLOBALS['productId'] = $productId;
        $GLOBALS['ownershipSelect'] = $this->buildOwnerShipSelect();
        $GLOBALS['stateOptionList'] = $this->buildStateOptionList();


        $GLOBALS['ISC_CLASS_TEMPLATE']->SetPageTitle(sprintf("%s - %s", GetConfig('StoreName'), "Finance Form"));
        $GLOBALS['ISC_CLASS_TEMPLATE']->SetTemplate("finance");
        $GLOBALS['ISC_CLASS_TEMPLATE']->ParseTemplate();
    }

    public function buildStateOptionList()
    {
        $html = '<option selected="selected" value=""> </option>
        <option value="AL">Alabama</option>
        <option value="AK">Alaska</option>
        <option value="AZ">Arizona</option>
        <option value="AR">Arkansas</option>
        <option value="CA">California</option>
        <option value="CO">Colorado</option>
        <option value="CT">Connecticut</option>
        <option value="DE">Delaware</option>
        <option value="FL">Florida</option>
        <option value="GA">Georgia</option>
        <option value="HI">Hawaii</option>
        <option value="ID">Idaho</option>
        <option value="IL">Illinois</option>
        <option value="IN">Indiana</option>
        <option value="IA">Iowa</option>
        <option value="KS">Kansas</option>
        <option value="KY">Kentucky</option>
        <option value="LA">Louisiana</option>
        <option value="ME">Maine</option>
        <option value="MD">Maryland</option>
        <option value="MA">Massachusetts</option>
        <option value="MI">Michigan</option>
        <option value="MN">Minnesota</option>
        <option value="MS">Mississippi</option>
        <option value="MO">Missouri</option>
        <option value="MT">Montana</option>
        <option value="NE">Nebraska</option>
        <option value="NV">Nevada</option>
        <option value="NH">New Hampshire</option>
        <option value="NJ">New Jersey</option>
        <option value="NM">New Mexico</option>
        <option value="NY">New York</option>
        <option value="NC">North Carolina</option>
        <option value="ND">North Dakota</option>
        <option value="OH">Ohio</option>
        <option value="OK">Oklahoma</option>
        <option value="OR">Oregon</option>
        <option value="PA">Pennsylvania</option>
        <option value="RI">Rhode Island</option>
        <option value="SC">South Carolina</option>
        <option value="SD">South Dakota</option>
        <option value="TN">Tennessee</option>
        <option value="TX">Texas</option>
        <option value="UT">Utah</option>
        <option value="VT">Vermont</option>
        <option value="VA">Virginia</option>
        <option value="WA">Washington</option>
        <option value="DC">Washington, DC</option>
        <option value="WV">West Virginia</option>
        <option value="WI">Wisconsin</option>
        <option value="WY">Wyoming</option>';
        return($html);
    }

    public function  buildOwnerShipSelect()
    {
        $html = '';
        for($i=0;$i<=100;$i++)
        {
           $html .= "<option value='$i'>$i %</option>";
        }
        return ($html);
    }


    public function loadThankYouPage()
    {
        if(!isset($GLOBALS['errMess']))
        {
            $GLOBALS['errMess'] = "";
            $GLOBALS['errDisplay'] = " style='display: none'";
        }

        $GLOBALS['SNIPPETS']['PreviewForm'] .= $GLOBALS['ISC_CLASS_TEMPLATE']->GetSnippet("PreviewForm");

        $GLOBALS['ISC_CLASS_TEMPLATE']->SetPageTitle(sprintf("%s - %s", GetConfig('StoreName'), "Product Preview"));
        $GLOBALS['ISC_CLASS_TEMPLATE']->SetTemplate("preview_thankyou");
        $GLOBALS['ISC_CLASS_TEMPLATE']->ParseTemplate();
    }

    public function getProductInfo($productId)
    {
        $query = "
				SELECT p.*, FLOOR(prodratingtotal/prodnumratings) AS prodavgrating, pi.*, ".GetProdCustomerGroupPriceSQL().",
				(SELECT COUNT(fieldid) FROM [|PREFIX|]product_customfields WHERE fieldprodid=p.productid) AS numcustomfields,
				(SELECT COUNT(reviewid) FROM [|PREFIX|]reviews WHERE revstatus='1' AND revproductid=p.productid AND revstatus='1') AS numreviews,
				(SELECT brandname FROM [|PREFIX|]brands WHERE brandid=p.prodbrandid) AS prodbrandname,
				(SELECT COUNT(imageid) FROM [|PREFIX|]product_images WHERE imageprodid=p.productid) AS numimages,
				(SELECT COUNT(discountid) FROM [|PREFIX|]product_discounts WHERE discountprodid=p.productid) AS numbulkdiscounts
				FROM [|PREFIX|]products p
				LEFT JOIN [|PREFIX|]product_images pi ON (pi.imageisthumb=1 AND p.productid=pi.imageprodid)
				WHERE p.productid='$productId' AND p.prodvisible='1'
			";
        $result = $GLOBALS['ISC_CLASS_DB']->Query($query);
        $row = $GLOBALS['ISC_CLASS_DB']->Fetch($result);
        return ($row);

    }

    public function goToThankYouPage()
    {
        ob_end_clean();
        header("Location: " . $GLOBALS['ShopPath'] ."/preview.php?action=thankyou");
        die();
    }

    public function goToHomePage()
    {
        ob_end_clean();
        header("Location: " . $GLOBALS['ShopPath']);
        die();
    }

}
