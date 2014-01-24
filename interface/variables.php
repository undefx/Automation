<?php require('./common/session.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Variables</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');
      
      //Add New Variable
      $name = mysql_real_escape_string($_REQUEST['name']);
      $value = mysql_real_escape_string($_REQUEST['value']);
      if($name && $value) {
         AddVariable($name,$value);
         echo "<p>Added variable [name={$name}|value={$value}]</p>";
      }
      
      //List of variables
      ?><table>
         <tr><th>id</th><th>name</th><th>value</th></tr><?php
         $variables = GetVariables();
         foreach($variables as $variable) {
            echo "<tr><td>{$variable['id']}</td><td><a href=\"variable_edit.php?id={$variable['id']}\">[[{$variable['name']}]]</a></td><td>{$variable['value']}</td></tr>";
         }
      ?></table>
      
      <form method="post">
         name=<input type="text" name="name" /><br />
         value=<input type="text" name="value" /><br />
         <input type="submit" value="Add Variable" />
      </form>
      
      <?php
      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
