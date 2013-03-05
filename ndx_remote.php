<?php
	define('ISC_AJAX', 1);
	include(dirname(__FILE__)."/init.php");
	include(dirname(__FILE__) . "/includes/classes/class.captcha.php");
	$GLOBALS['ISC_CLASS_NDX_REMOTE'] = GetClass('ISC_NDXREMOTE');
	$GLOBALS['ISC_CLASS_NDX_REMOTE']->HandleToDo();