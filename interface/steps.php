<?php require('./common/session.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Steps</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');
      
      //Add New Step
      $name = mysql_real_escape_string($_REQUEST['name']);
      $flow_id = mysql_real_escape_string($_REQUEST['flow_id']);
      $sql = mysql_real_escape_string($_REQUEST['sql']);
      $cmd = mysql_real_escape_string($_REQUEST['cmd']);
      if($name && ($flow_id || $sql || $cmd)) {
         AddStep($name,$flow_id,$sql,$cmd);
         echo "<p>Added step [name={$name}|flow_id={$flow_id}|sql={$sql}|cmd={$cmd}]</p>";
         if($flow_id && !GetFlow($flow_id)) {
            echo "<p>Warning: Flow doesn't exist [id={$flow_id}]</p>";
         }
      }
      
      //List of steps
      ?><table>
         <tr><th>id</th><th>name</th><th>flow_id</th><th>sql</th><th>cmd</th></tr><?php
         $steps = GetSteps();
         foreach($steps as $step) {
            echo "<tr><td>{$step['id']}</td><td><a href=\"step_edit.php?id={$step['id']}\">{$step['name']}</a></td><td>{$step['flow_id']}</td><td>{$step['sql']}</td><td>{$step['cmd']}</td></tr>";
         }
      ?></table>
      
      <form method="post">
         name=<input type="text" name="name" /><br />
         flow_id=<input type="text" name="flow_id" /><br />
         sql=<input type="text" name="sql" /><br />
         cmd=<input type="text" name="cmd" /><br />
         <input type="submit" value="Add Step" />
      </form>
      
      <?php
      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
