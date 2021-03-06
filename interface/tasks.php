<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Tasks</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');

      //Add New Task
      $step_id = mysqli_real_escape_string($dbh, $_REQUEST['step_id']);
      $date = mysqli_real_escape_string($dbh, $_REQUEST['date']);
      $interval = mysqli_real_escape_string($dbh, $_REQUEST['interval']);
      if($step_id && $date) {
         AddTask($dbh, $step_id,$date,$interval);
         echo "<p>Added task [step_id={$step_id}|date={$date}|interval={$interval}]</p>";
         if(!GetStep($dbh, $step_id)) {
            echo "<p>Warning: Step doesn't exist [id={$step_id}]</p>";
         }
      }

      //List of tasks
      ?>
      <table>
         <tr><th>id</th><th>step_id</th><th>date</th><th>interval</th><th>name</th></tr>
         <?php
         $tasks = GetTasks($dbh);
         foreach($tasks as $task) {
            ?>
            <tr>
               <td><a href="task_edit.php?id=<?php echo $task['id']; ?>"><?php echo $task['id']; ?></a></td>
               <td><?php echo $task['step_id']; ?></td>
               <td><?php echo $task['date']; ?></td>
               <td><?php echo $task['interval']; ?></td>
               <td><a href="step_edit.php?id=<?php echo $task['step_id']; ?>"><?php echo $task['name']; ?></a></td>
            </tr>
            <?php
         }
         ?>
      </table>
      <form method="post">
         step_id=<input type="text" name="step_id" /><br />
         date=<input type="text" name="date" value="<?php echo date('Y-m-d H:i:s'); ?>" /><br />
         interval=<input type="text" name="interval" /><br />
         <input type="submit" name="action" value="Add Task" />
      </form>
      <?php
      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
