<?php
$headerTime = microtime(true);
?>
<div>
   <h1>Automation</h1>
   <a href="index.php">Home</a> |
   <a href="tasks.php">Tasks</a> |
   <a href="flows.php">Flows</a> |
   <a href="steps.php">Steps</a> |
   <a href="variables.php">Variables</a>
</div>
<hr />
<div>
   <?php
   require('database.php');
   require('utils.php');
   $dbh = DatabaseConnect();
   $connectTime = microtime(true);
   if(!$dbh) {
      echo '<p>Unable to connect to the database! [' . mysql_error() . ']</p>';
   }
   ?>
