<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Flows</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');

      //Add New Flow
      $name = mysqli_real_escape_string($dbh, $_REQUEST['name']);
      if($name) {
         AddFlow($dbh, $name);
         echo "<p>Added flow [name={$name}]</p>";
      }

      //List of flows
      ?><table>
         <tr><th>id</th><th>name</th></tr><?php
         $flows = GetFlows($dbh);
         foreach($flows as $flow) {
            echo "<tr><td>{$flow['id']}</td><td><a href=\"flow_edit.php?id={$flow['id']}\">{$flow['name']}</a></td></tr>";
         }
      ?></table>

      <form method="post">
         name=<input type="text" name="name" /><br />
         <input type="submit" value="Add Flow" />
      </form>

      <?php
      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
