<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Edit Variable</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');

      //Load Variable
      $id = mysqli_real_escape_string($dbh, $_REQUEST['id']);
      $variable = GetVariable($dbh, $id);
      if($variable) {
         //Do updates then reload the variable
         $action = $_REQUEST['action'];
         $name = mysqli_real_escape_string($dbh, $_REQUEST['name']);
         $value = mysqli_real_escape_string($dbh, $_REQUEST['value']);

         if($action == 'Delete') {
            DeleteVariable($dbh, $variable['id']);
            echo "<p>Deleted variable [id={$variable['id']}]</p>";
         } elseif($action == 'Update' && $name && $value) {
            UpdateVariable($dbh, $variable['id'],$name,$value);
            echo "<p>Updated variable [name={$name}|value={$value}]</p>";
         }

         //Reload the variable
         $variable = GetVariable($dbh, $id);
         if($variable) {
            echo "id={$variable['id']}<br />";
            ?>
            <form method="post">
               <input type="hidden" name="id" value="<?php echo $variable['id']; ?>" />
               name=<input type="text" name="name" value="<?php echo $variable['name']; ?>" /><br />
               value=<input type="text" name="value" value="<?php echo $variable['value']; ?>" /><br />
               <input type="submit" name="action" value="Update" /><br />
               <input type="submit" name="action" value="Delete" /><br />
            </form>
            <?php
         } else {
            echo "<p>Variable doesn't exist [id={$id}]</p>";
         }
      } else {
         echo "<p>Variable doesn't exist [id={$id}]</p>";
      }

      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
