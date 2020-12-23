<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Edit Task</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');

      //Load Step
      $id = mysql_real_escape_string($_REQUEST['id']);
      $task = GetTask($id);
      if($task) {
         //Do updates then reload the task
         $action = $_REQUEST['action'];
         $step_id = mysql_real_escape_string($_REQUEST['step_id']);
         $date = mysql_real_escape_string($_REQUEST['date']);
         $interval = mysql_real_escape_string($_REQUEST['interval']);

         if($action == 'Delete') {
            DeleteTask($task['id']);
            echo "<p>Deleted task [id={$task['id']}]</p>";
         } elseif($action == 'Update' && $step_id && $date) {
            UpdateTask($task['id'],$step_id,$date,$interval);
            echo "<p>Updated task [step_id={$step_id}|date={$date}|interval={$interval}]</p>";
            if(!GetStep($step_id)) {
               echo "<p>Warning: Step doesn't exist [id={$step_id}]</p>";
            }
         }

         //Reload the task
         $task = GetTask($id);
         if($task) {
            echo "id={$task['id']}<br />";
            ?>
            <form method="post">
               <input type="hidden" name="id" value="<?php echo $task['id']; ?>" />
               step_id=<input type="text" name="step_id" value="<?php echo $task['step_id']; ?>" /><br />
               date=<input type="text" name="date" value="<?php echo $task['date']; ?>" /><br />
               interval=<input type="text" name="interval" value="<?php echo $task['interval']; ?>" /><br />
               <input type="submit" name="action" value="Update" /><br />
               <input type="submit" name="action" value="Delete" />
            </form>
            <?php
         } else {
            echo "<p>Task doesn't exist [id={$id}]</p>";
         }
      } else {
         echo "<p>Task doesn't exist [id={$id}]</p>";
      }

      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
