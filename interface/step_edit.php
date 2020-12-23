<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Edit Step</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');

      //Load Step
      $id = mysqli_real_escape_string($dbh, $_REQUEST['id']);
      $step = GetStep($dbh, $id);
      if($step) {
         //Do updates then reload the step
         $action = $_REQUEST['action'];
         $name = mysqli_real_escape_string($dbh, $_REQUEST['name']);
         $flow_id = mysqli_real_escape_string($dbh, $_REQUEST['flow_id']);
         $sql = mysqli_real_escape_string($dbh, $_REQUEST['sql']);
         $cmd = mysqli_real_escape_string($dbh, $_REQUEST['cmd']);

         if($action == 'Delete') {
            if(IsStepInUse($dbh, $step['id'])) {
               echo "<p>Step is used by a flow or a task [id={$step['id']}]</p>";
            } else {
               DeleteStep($dbh, $step['id']);
               echo "<p>Deleted step [id={$step['id']}]</p>";
            }
         } elseif($action == 'Update' && $name && ($flow_id || $sql || $cmd)) {
            UpdateStep($dbh, $step['id'],$name,$flow_id,$sql,$cmd);
            echo "<p>Updated step [name={$name}|flow_id={$flow_id}|sql={$sql}|cmd={$cmd}]</p>";
            if($flow_id && !GetFlow($dbh, $flow_id)) {
               echo "<p>Warning: Flow doesn't exist [id={$flow_id}]</p>";
            }
         } elseif($action == 'Run Step') {
            RunStep($dbh, $step['id']);
            echo "<p>Added step to the run_stack [step_id={$step['id']}]</p>";
         }

         //Reload the step
         $step = GetStep($dbh, $id);
         if($step) {
            echo "id={$step['id']}<br />";
            ?>
            <form method="post">
               <input type="hidden" name="id" value="<?php echo $step['id']; ?>" />
               name=<input type="text" name="name" value="<?php echo htmlspecialchars($step['name']); ?>" /><br />
               flow_id=<input type="text" name="flow_id" value="<?php echo $step['flow_id']; ?>" /><br />
               sql=<input type="text" name="sql" value="<?php echo $step['sql']; ?>" /><br />
               cmd=<input type="text" name="cmd" value="<?php echo $step['cmd']; ?>" /><br />
               <input type="submit" name="action" value="Update" /><br />
               <input type="submit" name="action" value="Delete" /><br />
               <input type="submit" name="action" value="Run Step" /><br />
            </form>
            <?php
         } else {
            echo "<p>Step doesn't exist [id={$id}]</p>";
         }
      } else {
         echo "<p>Step doesn't exist [id={$id}]</p>";
      }

      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
