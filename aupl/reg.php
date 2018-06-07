<?php

$str = '<b>example: </b><div align=left>this is a test</div>';
preg_match_all("|<[^>]+>(.*)</[^>]+>|U", $str, $matchs);
var_dump($matchs);

?>