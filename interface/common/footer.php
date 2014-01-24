<?php
//Calculate the processing time
$footerTime = microtime(true);
$processingTime = $footerTime - $headerTime;
echo '</div><hr /><div>' . sprintf('[%.1f sec]',$processingTime) . '</div>';

//Close the connection to the database
mysql_close($dbh);
?>