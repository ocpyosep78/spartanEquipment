<?php
require('init.php');
$pull = $GLOBALS['ISC_CLASS_DB']->Query("SELECT newsid, newscontent FROM [|PREFIX|]news where newscontent like '%ceunbound%'");
$counter = 0;
while($row = $GLOBALS['ISC_CLASS_DB']->Fetch($pull)){
	$counter++;
	$content = str_replace("https://www.ceunbound.com","http://www.ceunbound.com",$row['newscontent']);
	$id = $row['newsid'];
	$upit = $GLOBALS['ISC_CLASS_DB']->Query("UPDATE [|PREFIX|]news set newscontent = '$content' WHERE newsid = '$id'");
}
die("$counter rows were updated");
?>