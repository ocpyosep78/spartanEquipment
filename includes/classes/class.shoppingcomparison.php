<?php
include_once(ISC_BASE_PATH.'/lib/api/category.api.php');

/**
 * The abstract base class for all shopping comparison modules.
 *
 * @author trey.shugart
 * @date 2010-02-15
 */
abstract class Isc_ShoppingComparison extends Isc_Module
{
	/**
	 * The database class.
	 *
	 * @var MySQLDb
	 */
	protected $db;

	/**
	 * Comparison Sites Logos and Url
	 */
	protected $logoImages = array('logo.png');
	protected $logoUrls = array('');

	/**
	 * Small icon image for this module
	 */
	protected $icon = 'icon.png';

	/**
	 * Export file settings
	 */
	protected $exportFileExtension = ".csv";
	protected $exportFileSeparator = ",";

	/**
	 * Taxonomy file
	 */
	protected $taxonomyFile = "";

	/**
	 * Module output messages
	 */
	protected $messages = array();

	/**
	 * Cache handle
	 **/
	protected $cache;

	/**
	 * Returns an array of all available shopping comparison modules
	 *
	 * @return array
	 */
	static public function getAllModules()
	{
		$return  = array();
		$modules = getAvailableModules('shoppingcomparison');

		foreach ($modules as $module) {
			$return[] = $module['object'];
		}

		return $return;
	}

	/**
	 * Returns an array of shopping comparison modules with taxonomies
	 *
	 * @return array
	 */
	static public function getModulesWithTaxonomies()
	{
		$allModules = self::getAllModules();
		$modules = array();

		foreach($allModules as $module) {
			if($module->hasTaxonomy() && $module->checkEnabled()) {
				$modules[] = $module;
			}
		}

		return $modules;
	}

	/**
	 * Constructs the parent class and sets defaults.
	 *
	 * @return Isc_ShoppingComparison
	 */
	public function __construct()
	{
		// the id and the type must be set before constructing
		$this->setId(strtolower(get_class($this)));
		$this->setType('shoppingcomparison');

		// construct
		parent::__construct();

		// now set name and help-text
		$this->setName($this->name());
		$this->setHelpText($this->helpText());

		// access to the db class
		$this->db = getClass('Isc_Admin_Engine')->db;

		// keystore
		$this->cache = Interspire_KeyStore_Mysql::instance();
	}

	protected function name()
	{
		return getLang($this->getId() . '_name');
	}

	protected function helpText()
	{
		$replacements = array(
			"name" => $this->getName(),
			"tutorialId" => GetLang($this->getId().'_kb_tutorial'),
		);

		return getLang('ShoppingComparisonHelp', $replacements);
	}

	public function loadManageScreenData()
	{
		$replacements = array('name' => $this->getName());

		if($unmappedCount = $this->numUnmappedCategories()) {
			$replacements['count'] = $unmappedCount;
			$this->setMessage(
				'ShoppingComparisonCategoriesNotMapped',
				'Info',
				$replacements
			);
		}

		if($this->exportTask()) {
			$progress = $this->exportTask()->getProgress();
			$replacements['complete'] = $progress['progress'];

			$this->setMessage(
				'ShoppingComparisonFeedBeingGenerated',
				'Info',
				$replacements
			);
		}
		else if(($lastExport = $this->getLastExportDetails())) {
			$replacements['date'] = $lastExport['date'];
			$replacements['time'] = $lastExport['time'];

			$this->setMessage(
				'ShoppingComparisonFeedLastGenerated',
				'Success',
				$replacements
			);
		}
		else
		{
			$this->setMessage(
				'ShoppingComparisonNoFeed',
				'Info',
				$replacements
			);
		}
	}

	/**
	 * Returns the urls to the logo images declared in this->logos.
	 *
	 * @returns array
	 */
	public function getLogos()
	{
		$logos = array();

		$size = count($this->logoImages);
		for($i = 0; $i < $size; $i++) {
			$logos[] = array(
				'image' => $this->getImagePath().$this->logoImages[$i],
				'url' => $this->logoUrls[$i]);
		}

		return $logos;
	}

	/**
	 * Returns the url for this module's icon image.
	 */
	public function getIcon()
	{
		return $this->getImagePath().$this->icon;
	}

	/**
	 * Returns the shopping comparison site url.
	 */
	public function getLogoUrls()
	{
		return $this->siteUrl;
	}

	/**
	 * Returns true if a feed is available for download
	 */
	public function canDownloadFeed()
	{
		return true;
	}

	/**
	 * Returns true if a feed can be generated.
	 *
	 * @returns boolean
	 */
	public function canGenerateFeed()
	{
		return true;
	}

	/**
	 * Returns true if categories are mapped correctly
	 *
	 * @return boolean
	 */
	public function checkCategoriesMapped()
	{
		return true;
	}

	/**
	 * Returns the number of unmapped categories
	 *
	 * @return integer
	 */
	public function numUnmappedCategories()
	{
		$db = $GLOBALS['ISC_CLASS_DB'];

		$query = "
			SELECT
				count(*)
			FROM
				[|PREFIX|]categories c
			LEFT OUTER JOIN
				[|PREFIX|]shopping_comparison_category_associations ca
					ON (ca.category_id = c.categoryid AND ca.shopping_comparison_id = '".$this->getId()."')
			WHERE
				ca.category_id is NULL";


		return $db->FetchOne($query);
	}

	/**
	 * Set a message to be output to the user
	 *
	 * @param string the message
	 * @param string the type of message {Error, Info, Success}
	 */
	public function setMessage($msg, $type, $replacements=array())
	{
		$msg = GetLang($msg, $replacements);
		$this->messages[] = array('content' => $msg, 'type' => $type);
	}

	/**
	 * Returns any messages that this module has set
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Checks whether or not the given shopping comparison module is enabled.
	 *
	 * @return bool
	 */
	public function checkEnabled()
	{
		$enabled = explode(',', getConfig('ShoppingComparisonModules'));

		if (in_array($this->getId(), $enabled)) {
			return true;
		}

		return false;
	}

	/**
	 * Enables comparison module.
	 *
	 * @return bool
	 */
	public function enable()
	{
		if($this->checkEnabled())
			return true;

		$this->removeAllProducts();
		$this->addAllProducts();
		$this->loadTaxonomy();
		$this->pushExportTask();
		return true;
	}

	/**
	 * Disables the comparison module
	 *
	 * @return bool
	 */
	public function disable()
	{
		if(!$this->checkEnabled())
			return true;

		$this->removeAllProducts();
		return true;
	}

	/**
	 * Attempts to load the category taxonomy for this module,
	 * if one exists.
	 */
	protected function loadTaxonomy()
	{
		if($taxonomy = $this->getTaxonomy()) {
			return $taxonomy->load();
		}

		return false;
	}

	/**
	 * Add all products to this comparison feed
	 *
	 * @return bool
	 */
	public function removeAllProducts()
	{
		$query = '
			DELETE FROM [|PREFIX|]product_comparisons
			WHERE comparison_id="'.$GLOBALS['ISC_CLASS_DB']->Quote($this->getId()).'"';

		$result = $GLOBALS['ISC_CLASS_DB']->Query($query);

		return $result;
	}

	/**
	 * Remove all products from this comparison feed
	 *
	 * @return bool
	 */
	public function addAllProducts()
	{
		$query = '
			INSERT INTO [|PREFIX|]product_comparisons(product_id, comparison_id)
			SELECT productid, "'.$GLOBALS['ISC_CLASS_DB']->Quote($this->getId()).'"
			FROM [|PREFIX|]products';

		$result = $GLOBALS['ISC_CLASS_DB']->Query($query);

		return $result;
	}

	/**
	 * Creates or removes category mappings for a list of categories
	 * and their sub categories.
	 *
	 * @param array list of category ids to map
	 * @param integer the comparison category id to map to or null to remove mappings.
	 * @param string breadcrumb path for the shopping comparison category
	 */
	public function mapCategories($categories, $mapToCategoryId, $mapToPath)
	{
		if(empty($mapToCategoryId)) {
			$this->deleteCategoryMappings($categories);
			return;
		}

		$db = $GLOBALS['ISC_CLASS_DB'];

		$taxonomyId = $db->Quote($this->getId());
		$mapToCategoryId = (int) $mapToCategoryId;

		$ids = API_CATEGORY::getSubCategories($categories);

		foreach($ids as $categoryId) {
			$query = '
				INSERT INTO [|PREFIX|]shopping_comparison_category_associations
					(category_id, shopping_comparison_id, shopping_comparison_category_id)
				VALUES
					(' . $categoryId . ',"' . $taxonomyId . '", ' . $mapToCategoryId . ')
				ON DUPLICATE KEY UPDATE shopping_comparison_category_id= ' . $mapToCategoryId . ';';

			$db->Query($query);

			$this->updateCategoryMappingsCache($categoryId, $mapToCategoryId, $mapToPath);
		}
	}

	/**
	 * Removes category mappings for a list of categories and their
	 * sub categories.
	 */
	public function deleteCategoryMappings($categories)
	{
		$db = $GLOBALS['ISC_CLASS_DB'];

		$taxonomyId = $db->Quote($this->getId());
		$ids = API_CATEGORY::getSubCategories($categories);

		foreach($ids as $categoryId) {
			$this->updateCategoryMappingsCache($categoryId, null, null);

			$query = '
				DELETE FROM [|PREFIX|]shopping_comparison_category_associations
				WHERE
					category_id='.$categoryId.' AND shopping_comparison_id="'.$taxonomyId.'";';

			$db->Query($query);
		}
	}

	public function updateCategoryMappingsCache($categoryId, $mapToCategoryId, $mapToPath)
	{
		$db = $GLOBALS['ISC_CLASS_DB'];

		$query = '
			SELECT cataltcategoriescache
			FROM [|PREFIX|]categories
			WHERE categoryid='.$categoryId.';';

		// Fetch and decode the cached category mappings
		$categoryMappingsCache = (array)json_decode($db->FetchOne($query));

		// Update category mappings or delete if mapToCategoryId is empty
		if(empty($mapToCategoryId)) {
			unset($categoryMappingsCache[$this->getId()]);
		}
		else {
			$mappingInfo = array(
				'categoryid' => $mapToCategoryId,
				'path' => $mapToPath);

			$categoryMappingsCache[$this->getId()] = $mappingInfo;
		}

		// Encode category mappings and commit to database
		$query = '
			UPDATE [|PREFIX|]categories
			SET
				cataltcategoriescache = "'.$db->Quote(isc_json_encode($categoryMappingsCache)).'"
			WHERE
				categoryid='.$categoryId.';
		';

		$db->Query($query);
	}

	/**
	 * Queues a new export background job, and stores a handle to the
	 * job's controller accessible via $this->exportTask().
	 *
	 * @return object A controller handle to the queued export job.
	 */
	public function pushExportTask()
	{
		$controller = Job_Controller::create($this->getId());
		$this->setCurrentExportId($controller->getId());

		Interspire_TaskManager::createTask('shoppingcomparison',
			'Job_ShoppingComparison_RunExport',
			array("module" => $this->getId(),
				"controller" => $controller->getId()));

		$this->logSuccess(GetLang('ShoppingComparisonLogNewExportJob'));

		return $controller;
	}

	/**
	 * Returns true if a taxonomy exists for this module
	 */
	public function getTaxonomy()
	{
		if($this->taxonomyFile) {
			$file = ISC_BASE_PATH
				. '/modules/shoppingcomparison/taxonomies/'
				.$this->taxonomyFile;

			return new Isc_ShoppingComparison_Taxonomy($this->getId(), $file);
		}

		return false;
	}

	protected function logSuccess($summary, $message='')
	{
		$log = $GLOBALS['ISC_CLASS_LOG'];

		if(empty($message))
			$message = $summary;

		$type = array('shoppingcomparison', $this->getName());
		$log->LogSystemSuccess($type, $summary, $message);
	}

	protected function logError($severity, $summary, $message='')
	{
		$log = $GLOBALS['ISC_CLASS_LOG'];

		if(empty($message))
			$message = $summary;

		$type = array('shoppingcomparison', $this->getName());
		$log->LogSystemError($type, $summary, $message);
	}

	/**
	 * Returns true if this module has it's own category taxonomy
	 */
	public function hasTaxonomy()
	{
		return !empty($this->taxonomyFile);
	}

	/**
	 * Returns an absolute file path to the export file.
	 *
	 * @return string
	 */
	public function getExportFile()
	{
		return ISC_BASE_PATH . '/cache/feeds/feed_'.$this->getId().$this->exportFileExtension;
	}

	/**
	 * Set the date and time of the last export
	 *
	 * @param string Formatted date string eg 12th May 2010
	 * @param string Formatted time string eg 10:43am
	 */
	public function setLastExportDetails($date, $time)
	{
		$details = array('date' => $date, 'time' => $time);
		$this->cache->set($this->getId().'_lastExportDetails', isc_json_encode($details));
	}

	/**
	 * Get the date and time of the last export
	 */
	public function getLastExportDetails()
	{
		if(!($details = $this->cache->get($this->getId().'_lastExportDetails')))
			return null;

		return (array)json_decode($details);
	}

	/**
	 * Returns a template sql query for exporting products.
	 *
	 * The :columns placeholder can be replaced with:
	 *
	 * 	p.* = Product Table Fields
	 *	b.* = Brand Table Fields
	 *	c.* = Category Table Fields
	 *	sc.id = Shopping Comparison Category Id
	 *	sc.path = Shopping Comparison Category Path
	 *	sc.name = Shopping Comparison Category Name
	 *	pi.* = Product Images Table Fields (thumbnail)
	 *
	 * @return string sql query
	 */
	private function exportProductsQuery()
	{
		$query = '
			SELECT
				:columns
			FROM
				[|PREFIX|]product_comparisons pc,
				[|PREFIX|]products p

				/* Join categories */
				INNER JOIN (
					SELECT
						c.catname,
						c.categoryid,
						ca.productid
					FROM
						[|PREFIX|]categoryassociations ca,
						[|PREFIX|]categories c
					WHERE
						c.categoryid=ca.categoryid
					GROUP BY ca.productid
				) c ON (c.productid = p.productid)

				/* Join shopping comparison categories */
				LEFT JOIN (
					SELECT
						sc_ca.category_id,
						sc_c.*
					FROM
						[|PREFIX|]shopping_comparison_category_associations sc_ca,
						[|PREFIX|]shopping_comparison_categories sc_c
					WHERE
						sc_ca.shopping_comparison_category_id = sc_c.id
						AND sc_ca.shopping_comparison_id = "'.$this->getId().'"
						AND sc_c.shopping_comparison_id = "'.$this->getId().'"
				) sc on (sc.category_id=c.categoryid)

				/* Join product thumb image */
				LEFT JOIN
					[|PREFIX|]product_images pi ON (pi.imageprodid = p.productid AND pi.imageisthumb = 1)

				/* Join brands */
				LEFT JOIN
					[|PREFIX|]brands b ON (b.brandid = p.prodbrandid)

			WHERE
				pc.comparison_id = "'.$this->getId().'"
				AND p.productid=pc.product_id
				AND p.prodvisible=1
		';

		return $query;
	}

	/**
	 * Returns the total number of product rows to export.
	 *
	 * @return integer
	 */
	public function getTotalRows()
	{
		$query = $this->exportProductsQuery();
		$query = str_replace(':columns', 'count(*)', $query);

		return $GLOBALS['ISC_CLASS_DB']->FetchOne($query);
	}

	/**
	 * Fetch the product rows to export.
	 *
	 * @param integer start row index
	 * @param integer number of rows to fetch
	 * @return object returns a mysql resource on success or FALSE on error
	 */
	public function getExportRows($start, $numRows)
	{
		$columns = '
			p.*,
			b.brandname,
			c.catname,
			sc.id shopping_comparison_category_id,
			sc.path shopping_comparison_category_path,
			sc.name shopping_comparison_category_name,
			b.brandname,
			pi.*
		';

		$query = $this->exportProductsQuery();
		$query = str_replace(':columns', $columns, $query);
		$query .= 'LIMIT '.$start.', '.$numRows.';';

		$result = $GLOBALS['ISC_CLASS_DB']->Query($query);

		return $result;
	}

	public function writeHead()
	{
		return "";
	}

	public function writeRow($row)
	{
		return "";
	}

	/**
	 * Called by the export task at the end of an export. Updates the
	 * date and time of the last generated export if this is the active
	 * export task.
	 *
	 * @param object The job controller passed by the export task.
	 */
	public function exportEnd($controller)
	{
		if($this->getCurrentExportId() != $controller->getId())
			return;

		$date = CDate(time());
		$time = isc_date('h:i a', time());

		$this->setLastExportDetails($date, $time);
		$this->clearCurrentExportId();

		$this->logSuccess(
				getLang('ShoppingComparisonExportJobComplete',
					array("name" => $this->getName())
				)
		);
	}

	/**
	 * Returns a job controller to the current running export task.
	 */
	public function exportTask()
	{
		if($this->getCurrentExportId())
			return Job_Controller::findById($this->getCurrentExportId());
		else
			return null;
	}

	/**
	 * Aborts any running exports for this module.
	 * @todo this needs to be fleshed out, needs error checking
	 * and feedback.
	 */
	public function abortExportTask()
	{
		$this->clearCurrentExportId();
	}

	private function setCurrentExportId($id)
	{
		$this->cache->set($this->getId().'_exportid', $id);
	}

	public function getCurrentExportId()
	{
		return $this->cache->get($this->getId().'_exportid');
	}

	private function clearCurrentExportId()
	{
		$this->cache->delete($this->getId().'_exportid');
	}

	public function exportProgressMessage($progress)
	{
		$replacements = array('name' => $this->getName());

		if($progress < 100) {
			$replacements['complete'] = $progress;
			return GetLang('ShoppingComparisonFeedBeingGenerated', $replacements);
		}

		$details = $this->getLastExportDetails();
		$replacements['date'] = $details['date'];
		$replacements['time'] = $details['time'];

		return GetLang('ShoppingComparisonFeedLastGenerated', $replacements);
	}
}
