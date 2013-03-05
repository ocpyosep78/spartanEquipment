<?php
/**
* Imports a product image from a local or remote file and generates thumbnail and tiny images
*
* @param string The URL or file name of the image to import
* @param string The product name
* @param bool Should the tiny version of the image be created?
* @param array Referenced set of generated product images
*
* @return mixed True on success, or an array of warning messages
*/
function ImportProductImage($imageFile, $productName, $createTiny = true, &$productImages = array())
{
	$warnings = array();

	// Is this a remote file?
	$imageDirectory = ISC_BASE_PATH."/".GetConfig('ImageDirectory');
	if(isc_substr(isc_strtolower($imageFile), 0, 7) == "http://" || isc_substr(isc_strtolower($imageFile), 0, 8) == "https://") {
		// Need to fetch the remote file
		$image = PostToRemoteFileAndGetResponse($imageFile);
		if($image) {
			// Place it in our images directory
			$randomDir = strtolower(chr(rand(65, 90)));
			if(!is_dir($imageDirectory."/".$randomDir)) {
				if(!@mkdir($imageDirectory."/".$randomDir, ISC_WRITEABLE_DIR_PERM)) {
					$randomDir = '';
				}
			}

			// Generate a random filename
			$basename = preg_replace("/%20/", '_', basename($imageFile));
			$fileName = $randomDir . "/" . GenRandFileName($basename);
			if(!@file_put_contents($imageDirectory."/".$fileName, $image)) {
				$warnings[] = $productName.GetLang('ImportProductImageUnableToMove');
			}
			// Check to see if it is an image
			else if (!is_array(@getimagesize($imageDirectory."/".$fileName))) {
				@unlink($imageDirectory."/".$fileName);
				$warnings[] = $productName.GetLang('ImportProductImageInvalidFile');
			}
			else {
				$productImages[] = array(
					"imagefile" => $fileName,
					"imageisthumb" => 0,
					"imagesort" => 1
				);

				$thumbName = $GLOBALS['ISC_CLASS_ADMIN_PRODUCT']->_AutoGenerateThumb($fileName);
				if($thumbName) {
					$productImages[] = array(
						"imagefile" => $thumbName,
						"imageisthumb" => 1,
						"imagesort" => 1
					);
				}
				else {
					$warnings[] = $productName.GetLang('ImportProductImageCorrupt');
				}

				if ($createTiny) {
					$tinyName = $GLOBALS['ISC_CLASS_ADMIN_PRODUCT']->_AutoGenerateThumb($fileName, "tiny");
					if($tinyName) {
						$productImages[] = array(
							"imagefile" => $tinyName,
							"imageisthumb" => 2,
							"imagesort" => 1
						);
					}
					else {
						$warnings[] =$productName.GetLang('ImportProductImageCorrupt');
					}
				}
			}
		}
		else {
			$warnings[] = $productName.GetLang('ImportProductImageDoesntExist');
		}
	}
	// Treating the file as a local file, in the product_images/import directory
	else {
		// This file exists, can be imported
		if(file_exists($imageDirectory."/import/".$imageFile)) {

			// Move it to our images directory
			$randomDir = strtolower(chr(rand(65, 90)));
			if(!is_dir($imageDirectory."/".$randomDir)) {
				if(!@mkdir($imageDirectory."/".$randomDir, ISC_WRITEABLE_DIR_PERM)) {
					$randomDir = '';
				}
			}

			// Generate a random filename
			$baseFileName = basename($imageFile);
			if($baseFileName != $imageFile) {
				$localDirectory = dirname($imageFile)."/";
			}
			else {
				$localDirectory = '';
			}
			$fileName = $randomDir . "/" . GenRandFileName($baseFileName);
			if(!@copy($imageDirectory."/import/".$imageFile, $imageDirectory."/".$fileName)) {
				$warnings[] = $productName.GetLang('ImportProductImageUnableToMove');
			}
			else {
				$productImages[] = array(
					"imagefile" => $fileName,
					"imageisthumb" => 0,
					"imagesort" => 1
				);

				// Does a thumbnail file exist?
				$thumbFile = "thumb_".$baseFileName;
				if(file_exists($imageDirectory."/import/".$localDirectory.$thumbFile)) {
					$thumbName = $randomDir . "/" . GenRandFileName($thumbFile);
					if(@copy($imageDirectory."/import/".$localDirectory.$thumbFile, $imageDirectory."/".$thumbName)) {
						$productImages[] = array(
							"imagefile" => $thumbName,
							"imageisthumb" => 1,
							"imagesort" => 1
						);
					}
				}
				// Otherwise, generate the thumb
				else {
					$thumbName = $GLOBALS['ISC_CLASS_ADMIN_PRODUCT']->_AutoGenerateThumb($fileName);
					if($thumbName) {
						$productImages[] = array(
							"imagefile" => $thumbName,
							"imageisthumb" => 1,
							"imagesort" => 1
						);
					}
				}

				// Still need to generate "tiny" thumbnail
				if ($createTiny) {
					$tinyName = $GLOBALS['ISC_CLASS_ADMIN_PRODUCT']->_AutoGenerateThumb($fileName, "tiny");
					if($tinyName) {
						$productImages[] = array(
							"imagefile" => $tinyName,
							"imageisthumb" => 2,
							"imagesort" => 1
						);
					}
				}
			}
		}
		else {
			$warnings[] = $productName.GetLang('ImportProductImageDoesntExist');
		}
	}

	if (empty($warnings)) {
		return true;
	}
	else {
		return $warnings;
	}
}