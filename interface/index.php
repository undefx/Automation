<?php require('./common/session.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Home</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');
      ?>
      <p>
         Queued Emails: <?php echo GetEmailQueueSize(); ?>
      </p>
      System Status
      <table>
         <tr><th>process</th><th>last update</th><th>delta</th></tr>
         <?php
         $heartbeats = GetHeartbeats();
         foreach($heartbeats as $heartbeat) {
            echo "<tr><td>{$heartbeat['name']}</td><td>{$heartbeat['date']}</td><td>{$heartbeat['delta']}</td></tr>";
         }
         ?>
      </table><br />
      Run Stack
      <table>
         <tr><th>id</th><th>step_id</th><th>run_group</th><th>name</th></tr>
         <?php
         $runs = GetRunStack();
         foreach($runs as $run) {
            echo "<tr><td>{$run['id']}</td><td>{$run['step_id']}</td><td>{$run['run_group']}</td><td>{$run['name']}</td></tr>";
         }
         ?>
      </table><br />
      Active Steps
      <table>
         <tr><th>id</th><th>step_id</th><th>start_time</th><th>stop_time</th><th>delta</th><th>status</th><th>return_code</th><th>name</th></tr>
         <?php
         $runs = GetActiveSteps();
         foreach($runs as $run) {
            echo "<tr><td>{$run['id']}</td><td>{$run['step_id']}</td><td>{$run['start_time']}</td><td>{$run['stop_time']}</td><td>{$run['delta']}</td><td>{$run['status']}</td><td>{$run['return_code']}</td><td>{$run['name']}</td></tr>";
         }
         ?>
      </table><br />
      Run Log
      <table>
         <tr><th>id</th><th>step_id</th><th>start_time</th><th>stop_time</th><th>delta</th><th>status</th><th>return_code</th><th>name</th></tr>
         <?php
         $runs = GetRunLog();
         foreach($runs as $run) {
            echo "<tr><td>{$run['id']}</td><td>{$run['step_id']}</td><td>{$run['start_time']}</td><td>{$run['stop_time']}</td><td>{$run['delta']}</td><td>{$run['status']}</td><td>{$run['return_code']}</td><td>{$run['name']}</td></tr>";
         }
         ?>
      </table><br />
      <?php
      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
