<?php

if (!defined('ISC_BASE_PATH')) {
	die();
}

class ISC_ADMIN_REMOTE_CATEGORIES extends ISC_ADMIN_BASE
{
	public function __construct()
	{
		$GLOBALS['ISC_CLASS_ADMIN_ENGINE']->LoadLangFile('categories');

		parent::__construct();
	}

	public function HandleToDo()
	{
		/**
		 * Convert the input character set from the hard coded UTF-8 to their
		 * selected character set
		 */
		convertRequestInput();

		GetLib('class.json');

		$action = isc_strtolower(@$_REQUEST['w']);

		if(method_exists($this, $action)) {
			$this->$action();
			die();
		}
	}

	private function getChildCategories()
	{
		if (empty($_GET['catId'])) {
			exit;
		}

		$categoryId = (int)$_GET['catId'];

		// Get shopping comparison engine modules that are anabled and
		// have category taxonomies to provide bulk mapping options via
		// the bulk editing select box.
		$shoppingComparisonModules = Isc_ShoppingComparison::getModulesWithTaxonomies();

		// Categories mapped to the Google Product Search
		$googleProductMappings = ISC_ADMIN_GOOGLEPS_CATEGORIES::getMappings();

		// display column in the grid if any shopping comparison or
		// Google Product Search mappings exist for the category
		if (count($shoppingComparisonModules) > 0 || count($googleProductMappings) > 0) {
			$this->template->assign('HasCategoryMappings', true);
			$this->template->assign('GoogleProductSearchMappings', $googleProductMappings);
			$this->template->assign('ShoppingComparisonModules', $shoppingComparisonModules);

			// If the Google Product Search Category mapping isn't valid, display message.
			$googlePs = new GooglePs();
			$invalidCatsMapping = $googlePs->getInvalidCategoriesMapping();
			if (!empty ($invalidCatsMapping)) {

				// Assign these invalid categories to template var so we can determine it on the template.
				$this->template->assign('invalidCatsMapping', $invalidCatsMapping);

			}
		} else {
			$this->template->assign('HasCategoryMappings', false);
		}

		$catClass = GetClass('ISC_ADMIN_CATEGORY');
		echo $catClass->_BuildCategoryList($categoryId);
	}

	private function getChildCategoriesJSON()
	{
		ISC_ADMIN_CATEGORY::getChildCategoriesJSON();
	}


	// Update the visibility of a product with a simple query
	private function toggleCategoryVisiblity()
	{
		$errorMsg = GetLang('ErrCategoryVisibilityNotChanged');
		if (empty($_GET['catId'])) {
			ISC_JSON::output($errorMsg, false);
		}

		$catId = (int)$_GET['catId'];

		$query = "
			SELECT
				catname,
				catvisible,
				catparentlist,
				catnsetleft,
				catnsetright
			FROM
				[|PREFIX|]categories WHERE categoryid = " . $catId;
		$result = $this->db->Query($query);
		$category = $this->db->Fetch($result);

		// is the category currently visible?
		$catVisible = (bool)$category['catvisible'];

		$affectedCategories = array($catId);

		// toggle visibility
		$update = array(
			'catvisible' => (int)!$catVisible
		);

		// if the category is currently visible, we need only hide this one
		if ($catVisible) {
			$where = 'categoryid = ' . $catId;
		}
		// to make the invisible category visible we need to make this and the parent categories visible
		else {
			if (trim($category['catparentlist']) !== '') {
				$parentCategories = explode(',', $category['catparentlist']);
				$affectedCategories = array_unique(array_merge($affectedCategories, $parentCategories));
				//$where = 'catnsetleft <= ' . $category['catnsetleft'] . ' AND catnsetright >= ' . $category['catnsetright'];
				$where = "categoryid IN (" . implode(',', $affectedCategories) . ")";
			} else {
				// no parent
				$where = 'categoryid = ' . $catId;
			}
		}

		if ($this->db->UpdateQuery('categories', $update, $where)) {
			// Log this action
			$GLOBALS['ISC_CLASS_LOG']->LogAdminAction($catId, $category['catname']);

			// Update the data store
			$GLOBALS['ISC_CLASS_DATA_STORE']->UpdateRootCategories();

			// does this category have child categories?
			$hasChildren = false;
			if (($category['catnsetleft'] + 1) < $category['catnsetright']) {
				$hasChildren = true;
			}

			$andSubCats = '';
			if ($hasChildren) {
				$andSubCats = GetLang('SubCategories');
			}
			$successMsg = GetLang('CategoryVisibilitySuccessfully', array('andSubCats' => $andSubCats));

			ISC_JSON::output($successMsg, true, array('visible' => !$catVisible, 'affected' => $affectedCategories));
		}

		ISC_JSON::output($errorMsg, false);
	}
}
