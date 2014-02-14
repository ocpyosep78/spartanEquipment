<?php

include(dirname(__FILE__)."/init.php");
require(dirname(__FILE__).'/includes/classes/class.finance.php');
$GLOBALS['ISC_CLASS_FINANCE'] = GetClass('ISC_FINANCE');
$GLOBALS['ISC_CLASS_FINANCE']->HandlePage();