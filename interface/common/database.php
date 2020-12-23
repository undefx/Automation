<?php
function DatabaseConnect() {
   require('settings.php');
   $dbh = mysql_connect($dbHost, $dbUser, $dbPass);
   if($dbh) {
      mysql_select_db($dbName, $dbh);
   }
   return $dbh;
}

function GetEmailQueueSize() {
   $result = mysql_query("SELECT count(1) size FROM email_queue WHERE status != 1");
   if($row = mysql_fetch_array($result)) {
      return $row['size'];
   }
   return -1;
}

function AddFlow($name) {
   mysql_query("INSERT INTO flows (name) VALUES ('{$name}')");
}

function UpdateFlow($id,$name) {
   mysql_query("UPDATE flows SET name='{$name}' WHERE id={$id}");
}

function DeleteFlow($id) {
   mysql_query("DELETE FROM flow_steps WHERE flow_id={$id}");
   mysql_query("DELETE FROM flows WHERE id={$id}");
}

function GetFlow($id) {
   $result = mysql_query("SELECT id,name FROM flows WHERE id={$id}");
   if($row = mysql_fetch_array($result)) {
      $flow['id'] = $row['id'];
      $flow['name'] = $row['name'];
      return $flow;
   }
   return false;
}

function GetFlows() {
   $result = mysql_query('SELECT id,name FROM flows');
   $flows = array();
   while($row = mysql_fetch_array($result)) {
      $flow['id'] = $row['id'];
      $flow['name'] = $row['name'];
      array_push($flows, $flow);
   }
   return $flows;
}

function AddStep($name,$flow_id,$sql,$cmd) {
   $columns = 'name';
   $values = "'{$name}'";
   if($flow_id) {
      $columns .= ',flow_id';
      $values .= ",{$flow_id}";
   }
   if($sql) {
      $columns .= ',`sql`';
      $values .= ",'{$sql}'";
   }
   if($cmd) {
      $columns .= ',cmd';
      $values .= ",'{$cmd}'";
   }
   mysql_query("INSERT INTO steps ({$columns}) VALUES ({$values})");
}

function UpdateStep($id,$name,$flow_id,$sql,$cmd) {
   $flow_id = ($flow_id)?$flow_id:'NULL';
   $sql = ($sql)?"'{$sql}'":'NULL';
   $cmd = ($cmd)?"'{$cmd}'":'NULL';
   mysql_query("UPDATE steps SET name='{$name}',flow_id={$flow_id},`sql`={$sql},cmd={$cmd} WHERE id={$id}");
}

function IsStepInUse($id) {
   $result = mysql_query("SELECT count(1) ct FROM flow_steps WHERE step_id={$id}");
   if($row = mysql_fetch_array($result)) {
      if($row['ct'] != 0) {
         return true;
      }
   } else {
      return true;
   }

   $result = mysql_query("SELECT count(1) ct FROM tasks WHERE step_id={$id}");
   if($row = mysql_fetch_array($result)) {
      if($row['ct'] != 0) {
         return true;
      }
   } else {
      return true;
   }

   return false;
}

function DeleteStep($id) {
   mysql_query("DELETE FROM steps WHERE id={$id}");
}

function GetStep($id) {
   $result = mysql_query("SELECT id,name,flow_id,`sql`,cmd FROM steps WHERE id={$id}");
   if($row = mysql_fetch_array($result)) {
      $step['id'] = $row['id'];
      $step['name'] = $row['name'];
      $step['flow_id'] = $row['flow_id'];
      $step['sql'] = $row['sql'];
      $step['cmd'] = $row['cmd'];
      return $step;
   }
   return false;
}

function GetSteps() {
   $result = mysql_query('SELECT id,name,flow_id,`sql`,cmd FROM steps');
   $steps = array();
   while($row = mysql_fetch_array($result)) {
      $step['id'] = $row['id'];
      $step['name'] = $row['name'];
      $step['flow_id'] = $row['flow_id'];
      $step['sql'] = $row['sql'];
      $step['cmd'] = $row['cmd'];
      array_push($steps, $step);
   }
   return $steps;
}

function AddFlowStep($flow_id,$step_id,$index) {
   mysql_query("INSERT INTO flow_steps (flow_id,step_id,`index`) VALUES ({$flow_id},{$step_id},{$index})");
}

function DeleteFlowStep($id) {
   $flowStep = GetFlowStep($id);
   if($flowStep) {
      mysql_query("DELETE FROM flow_steps WHERE id={$id}");
      mysql_query("UPDATE flow_steps SET `index` = `index` - 1 WHERE flow_id={$flowStep['flow_id']} AND `index`>{$flowStep['index']}");
   }
}

function MoveFlowStep($id,$direction) {
   $flowStep = GetFlowStep($id);
   if($flowStep) {
      $result = mysql_query("SELECT min(`index`) min,max(`index`) max FROM flow_steps WHERE flow_id={$flowStep['flow_id']}");
      if($row = mysql_fetch_array($result)) {
         $min = $row['min'];
         $max = $row['max'];
      }
      $newIndex = $flowStep['index'] + $direction;
      if($newIndex >= $min && $newIndex <= $max) {
         mysql_query("UPDATE flow_steps SET `index` = {$flowStep['index']} WHERE flow_id={$flowStep['flow_id']} AND `index`={$newIndex}");
         mysql_query("UPDATE flow_steps SET `index` = {$newIndex} WHERE id={$flowStep['id']}");
      }
   }
}

function GetFlowStep($id) {
   $result = mysql_query("SELECT id,flow_id,step_id,`index` FROM flow_steps WHERE id={$id}");
   if($row = mysql_fetch_array($result)) {
      $flowStep['id'] = $row['id'];
      $flowStep['flow_id'] = $row['flow_id'];
      $flowStep['step_id'] = $row['step_id'];
      $flowStep['index'] = $row['index'];
      return $flowStep;
   }
   return false;
}

function GetFlowSteps($flow_id) {
   $result = mysql_query("SELECT id,flow_id,step_id,`index` FROM flow_steps WHERE flow_id={$flow_id} ORDER BY `index` ASC");
   $flowSteps = array();
   while($row = mysql_fetch_array($result)) {
      $flowStep['id'] = $row['id'];
      $flowStep['flow_id'] = $row['flow_id'];
      $flowStep['step_id'] = $row['step_id'];
      $flowStep['index'] = $row['index'];
      array_push($flowSteps, $flowStep);
   }
   return $flowSteps;
}

function RunStep($step_id) {
   mysql_query("CALL RunStep({$step_id})");
}

function GetHeartbeats() {
   $result = mysql_query("SELECT id,name,date,unix_timestamp(sysdate()) - unix_timestamp(date) delta FROM heartbeats");
   $heartbeats = array();
   while($row = mysql_fetch_array($result)) {
      $heartbeat['id'] = $row['id'];
      $heartbeat['name'] = $row['name'];
      $heartbeat['date'] = $row['date'];
      $heartbeat['delta'] = $row['delta'];
      array_push($heartbeats, $heartbeat);
   }
   return $heartbeats;
}

function GetRunLog() {
   $result = mysql_query("SELECT rl.id,rl.step_id,rl.start_time,rl.stop_time,rl.status,rl.return_code,s.name,unix_timestamp(stop_time) - unix_timestamp(start_time) delta FROM run_log rl LEFT JOIN steps s ON rl.step_id = s.id ORDER BY rl.id DESC LIMIT 25");
   $runs = array();
   while($row = mysql_fetch_array($result)) {
      $run['id'] = $row['id'];
      $run['step_id'] = $row['step_id'];
      $run['start_time'] = $row['start_time'];
      $run['stop_time'] = $row['stop_time'];
      $run['status'] = $row['status'];
      $run['return_code'] = $row['return_code'];
      $run['name'] = $row['name'];
      $run['delta'] = $row['delta'];
      array_push($runs, $run);
   }
   return $runs;
}

function GetActiveSteps() {
   $result = mysql_query("SELECT rl.id,rl.step_id,rl.start_time,rl.stop_time,rl.status,rl.return_code,s.name,unix_timestamp(CASE WHEN stop_time IS NULL THEN sysdate() ELSE stop_time END) - unix_timestamp(start_time) delta FROM run_log rl LEFT JOIN steps s ON rl.step_id = s.id WHERE status != 'success' ORDER BY rl.id DESC");
   $runs = array();
   while($row = mysql_fetch_array($result)) {
      $run['id'] = $row['id'];
      $run['step_id'] = $row['step_id'];
      $run['start_time'] = $row['start_time'];
      $run['stop_time'] = $row['stop_time'];
      $run['status'] = $row['status'];
      $run['return_code'] = $row['return_code'];
      $run['name'] = $row['name'];
      $run['delta'] = $row['delta'];
      array_push($runs, $run);
   }
   return $runs;
}

function GetRunStack() {
   $result = mysql_query("SELECT rs.id,rs.step_id,rs.run_group,s.name FROM run_stack rs LEFT JOIN steps s ON rs.step_id = s.id ORDER BY rs.run_group ASC,rs.id DESC");
   $runs = array();
   while($row = mysql_fetch_array($result)) {
      $run['id'] = $row['id'];
      $run['step_id'] = $row['step_id'];
      $run['run_group'] = $row['run_group'];
      $run['name'] = $row['name'];
      array_push($runs, $run);
   }
   return $runs;
}

function AddTask($step_id,$date,$interval) {
   $interval = ($interval)?$interval:'NULL';
   mysql_query("INSERT INTO tasks (step_id,date,`interval`) VALUES ({$step_id},'{$date}',{$interval})");
}

function UpdateTask($id,$step_id,$date,$interval) {
   $interval = ($interval)?$interval:'NULL';
   mysql_query("UPDATE tasks SET step_id = {$step_id},date = '{$date}',`interval` = {$interval} WHERE id = {$id}");
}

function DeleteTask($id) {
   mysql_query("DELETE FROM tasks WHERE id = {$id}");
}

function GetTask($id) {
   $result = mysql_query("SELECT t.id,t.step_id,t.date,t.`interval`,s.name FROM tasks t LEFT JOIN steps s ON t.step_id = s.id WHERE t.id = {$id}");
   if($row = mysql_fetch_array($result)) {
      $task['id'] = $row['id'];
      $task['step_id'] = $row['step_id'];
      $task['date'] = $row['date'];
      $task['interval'] = $row['interval'];
      $task['name'] = $row['name'];
      return $task;
   }
   return false;
}

function GetTasks() {
   $result = mysql_query("SELECT t.id,t.step_id,t.date,t.`interval`,s.name FROM tasks t LEFT JOIN steps s ON t.step_id = s.id");
   $tasks = array();
   while($row = mysql_fetch_array($result)) {
      $task['id'] = $row['id'];
      $task['step_id'] = $row['step_id'];
      $task['date'] = $row['date'];
      $task['interval'] = $row['interval'];
      $task['name'] = $row['name'];
      array_push($tasks, $task);
   }
   return $tasks;
}

function AddVariable($name,$value) {
   mysql_query("INSERT INTO variables (name,`value`) VALUES ('{$name}','{$value}')");
}

function UpdateVariable($id,$name,$value) {
   mysql_query("UPDATE variables SET name = '{$name}',`value` = '{$value}' WHERE id = {$id}");
}

function DeleteVariable($id) {
   mysql_query("DELETE FROM variables WHERE id = {$id}");
}

function GetVariable($id) {
   $result = mysql_query("SELECT v.id,v.name,v.value FROM variables v WHERE v.id = {$id}");
   if($row = mysql_fetch_array($result)) {
      $variable['id'] = $row['id'];
      $variable['name'] = $row['name'];
      $variable['value'] = $row['value'];
      return $variable;
   }
   return false;
}

function GetVariables() {
   $result = mysql_query("SELECT v.id,v.name,v.value FROM variables v ORDER BY id ASC");
   $variables = array();
   while($row = mysql_fetch_array($result)) {
      $variable['id'] = $row['id'];
      $variable['name'] = $row['name'];
      $variable['value'] = $row['value'];
      array_push($variables, $variable);
   }
   return $variables;
}
?>
