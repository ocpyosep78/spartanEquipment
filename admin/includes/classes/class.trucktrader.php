<?php
require_once "class.ajaxexporter.php";


class ISC_ADMIN_TRUCKTRADER extends ISC_ADMIN_AJAXEXPORTER
{
    protected $_brands = array();
    protected $_catNames = array();
    protected $_catParents = array();

    public function __construct()
    {
        $this->exportName = 'Trucktrader feed';
        $this->className = 'Trucktrader';
        $this->displayAutoExport = true;
        $this->exportIcon = 'froogle.gif';

        $GLOBALS['ExportName'] = "Trucktrader Feed";
        $GLOBALS['ExportIntro'] = "Trucktrader intro";
        $GLOBALS['ExportGenerate'] = "Trucktrader Generate";

        parent::__construct();
    }

    protected function GetResultCount()
    {
        $query = "
            SELECT COUNT(*) FROM (
                SELECT
                    p.productid
                FROM
                    [|PREFIX|]products p
                    LEFT JOIN [|PREFIX|]categoryassociations ca ON (p.productid=ca.productid)
                WHERE
                    p.prodvisible=1
                    AND p.proddesc <> ''
                GROUP BY
                    p.productid
            ) as count
        ";
        $result = $GLOBALS['ISC_CLASS_DB']->Query($query);
        $count = $GLOBALS['ISC_CLASS_DB']->FetchOne($result);

        return $count;
    }

    protected function WriteHeader()
    {
        $header = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $header .= "<Products>\n";
        fwrite($this->handle, $header);
    }

    protected function WriteFooter()
    {
        fwrite($this->handle, '</Products>');
    }

    public function GetResult($generateFull = false, $start = 0) {

    }

    public function WriteRow($row) {

    }

    protected function getBrands()
    {
        if (empty($this->_brands)) {
            $query = "SELECT brandid, brandname
            FROM [|PREFIX|]brands";
            $result = $GLOBALS['ISC_CLASS_DB']->Query($query);
            while ($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)) {
                $this->_brands[$row['brandid']] = $row['brandname'];
            }
        }
    }

    protected function getCategories()
    {
        if (empty($this->_catNames)) {
               $query = "SELECT categoryid, catparentid, catname
                       FROM [|PREFIX|]categories";
            $result = $GLOBALS['ISC_CLASS_DB']->Query($query);
            while ($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)) {
                      $this->_catParents[$row['categoryid']] = $row['catparentid'];
                      $this->_catNames[$row['categoryid']] = $row['catname'];
            }
        }
    }

    public function &getTruckTraderResult($start)
    {
        static $loopCount = 0;
        //get all the category information
        $this->getCategories();
        $catNames = &$this->_catNames;
        $catParents = &$this->_catParents;

        //get the brand information
        $this->getBrands();
        $brands = &$this->_brands;

        //get the product information
        $query = "SELECT productid, prodname, prodcode, proddesc, prodprice, 
                  prodcalculatedprice, prodbrandid, prodcatids
                  FROM [|PREFIX|]products
        		  WHERE prodvisible = '1'
                  ORDER BY productid
                  ";

        $query .= $GLOBALS['ISC_CLASS_DB']->AddLimit($start, $this->resultsPerPage);
        $result = $GLOBALS['ISC_CLASS_DB']->Query($query);
        if (!$result) {
            return false;
        }
        $productArray = array();
        $product = array();
        while ($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)) {
               $productArray[$row['productid']] = $row;
        }

        if (!empty($productArray)) {
            //get the image information
            $query = "SELECT *
                FROM [|PREFIX|]product_images
                WHERE imageprodid IN (" . implode(",", array_keys($productArray)) .")
                ORDER BY imagesort asc";
            $result = $GLOBALS['ISC_CLASS_DB']->Query($query);
            $images = array ();
            while ($row = $GLOBALS['ISC_CLASS_DB']->Fetch($result)) {
                $images[$row['imageprodid']][$row['imageid']] = array (
                            'imageid'            => $row['imageid'],
                            'imageprodid'        => $row['imageprodid'],
                            'imageprodhash'      => $row['imageprodhash'],
                            'imagefile'          => $row['imagefile'],
                            'imageisthumb'       => $row['imageisthumb'],
                            'imagesort'          => $row['imagesort'],
                            'imagefiletiny'      => $row['imagefiletiny'],
                            'imagefilethumb'     => $row['imagefilethumb'],
                            'imagefilestd'       => $row['imagefilestd'],
                            'imagefilezoom'      => $row['imagefilezoom'],
                            'imagedesc'          => $row['imagedesc'],
                            'imagedateadded'     => $row['imagedateadded'],
                            'imagefiletinysize'  => $row['imagefiletinysize'],
                            'imagefilethumbsize' => $row['imagefilethumbsize'],
                            'imagefilestdsize'   => $row['imagefilestdsize'],
                            'imagefilezoomsize'  => $row['imagefilezoomsize']
                        );
            }
            
            $query = "SELECT * 
                FROM `[|PREFIX|]product_videos` 
                WHERE video_product_id IN (" . implode(',', array_keys($productArray)) 
                .") ORDER BY `video_sort_order` asc";
                $result = $GLOBALS["ISC_CLASS_DB"]->Query($query);
                
                $videos = array();
                while ($row = $GLOBALS["ISC_CLASS_DB"]->Fetch($result)) {
                    $videos[$row['video_product_id']][$row['video_id']] = array(
                        'title' => $row['video_title'], 
                        'desc' => $row['video_description'], 
                        'length' => $row['video_length']
                    );
                }
        }

        //processing loop
        foreach($productArray as &$product) {
               $productid = $product['productid'];
               $prodcat = $product['prodcatids'];
               $prodcatArray = explode(',', $prodcat);
               foreach ($prodcatArray as $prodcat) {
                   $treeArray = $this->buildCategoryTree($prodcat);
                   $product['cattree'][] = $treeArray;
               }
               $product['brandname'] = isset($brands[$product['prodbrandid']])
                   ? $brands[$product['prodbrandid']]
                   : null;

               $product['images'] = isset($images[$productid])
                   ? $images[$productid]
                   : null;
                   
               $product['videos'] = isset($videos[$productid])
                   ? $videos[$productid]
                   : null;
        }
        return $productArray;
    }

    //Build the category tree
    protected function buildCategoryTree($prodcat)
    {
        $catparents = &$this->_catParents;
        $catnames   = &$this->_catNames;

        $cat_tree = array();
        $parent = $prodcat;
        $safetyCount = 0;
        while ($parent != 0) {
            if(++$safetyCount > 10) {
                echo "Error: Category structure too deep for category ". $prodcat ."<br>";
                break;
            }
            array_push($cat_tree, $parent);
            if(array_key_exists($parent, $catparents)) {
                $parent = $catparents[$parent];
            } else {
                $parent = 0;
            }
        }

        $treeArray = array();
        foreach (array_reverse($cat_tree) as $cat) {
            if(array_key_exists($cat, $catnames)) {
                array_push($treeArray, $catnames[$cat]);
            }
        }

        return implode('/', $treeArray);
    }

    public function WriteRowTruckTrader($products)
    {
        foreach( $products as $id => $product) {
            $prodName = trim($product['prodname']);
            $prodName = $this->cleanXml($prodName);
            $prodDesc = strip_tags($product['proddesc']);
            $prodDesc = $this->cleanXml($prodDesc);
            $prodBrand = $this->cleanXml($product['brandname']);
            $entry = array(
            	'Class' => '',
            	'Category' => '',
            	'Attachment_Type' => '',
            	'Seriers' => '',
            	'Manufacturer' => $prodBrand,
            	'Model' => $prodName,
            	'Year' => 2013,
            	'Serial_Number' => '',
            	'Stock_Number' => $product['prodcode'],
            	'Hours' => '',
            	'Horse_Power' => '',
            	'Capacity' => '',
            	'Drive' => '',
            	'Condition' => '',
            	'Attachment1' => '',
            	'Attachment2' => '',
            	'Attachment3' => '',
            	'Brief_Description' => $prodName,
            	'Description' => $prodDesc,
            	'List_Price'  => $product['prodcalculatedprice'],
            	'currency' => 'U.S. Dollars',
            	'Equipment_Status' => '',
            	'Status_Details' => '',
            	'Daily_Rental_Price' => '',
            	'Weekly_Rental_Price' => '',
            	'Monthly_Rental_Price' => '',
            );
            
            if(!is_null($product['images'])) {
                try {
                    $i = 1;
                    $imageList = '';
                    $entry['Pictures'] = '';
                    foreach($product['images'] as $key) {
                    	if($i > 5)
                    	{
                    		break;
                    	}
                        $image = new ISC_PRODUCT_IMAGE();
                        $image->populateFromDatabaseRow($key);
                        $newImage = $image->getResizedUrl(ISC_PRODUCT_IMAGE_SIZE_ZOOM, true);
                        $entry['Pictures'] .= $newImage.' ';
                        $i++;
                    }
                }
                catch (Exception $ex) {
                      $entry['Pictures'] = '';
                }
            } else {
                $entry['Pictures'] = '';
            }
            
            if(!is_null($product['videos'])) {
                try {
                    $i = 1;
                    $videoList = '';
                    $entry['Videos'] = '';
                    foreach($product['videos'] as $key => $value) {
                    	if($i > 5)
                    	{
                    		break;
                    	}
                        $entry['Videos'] .= 'http://www.youtube.com/embed/'. $key.' ';
                        $i++;
                }

                }
                catch (Exception $ex) {
                      $entry['Videos'] = "";
                } 
            } else {
                $entry['Videos'] = "";
            }

            $xml = "\t<Product>\n";
            foreach($entry as $k => $v) {
            	if($k == 'Category')
            	{
            		$i = 1;
            		foreach ($product['cattree'] as $catTree) {
            			if($i > 1)
            			{
            				break;
            			}
            			$catcleaned  = $this->cleanXml($catTree);
            			$xml .= "\t\t<$k>" .$catcleaned ."</$k> \n";
            			$i++;
            		}
            		continue;
            	}
            	
                $xml .= "\t\t<".$k.">".$v."</".$k.">\n";
            }
            
            $xml .= "\t</Product>\n";

            fwrite($this->handle, $xml);
        }
    }
        
        protected function cleanXml($value)
        {
            $iscClean = isc_html_escape($value);
            $res = preg_replace('/[^\x20-\x7E]/','', $iscClean);
            $res = preg_replace('/@/','', $res);
            $res = preg_replace('/�/','', $res);
            return $res;
        }
}