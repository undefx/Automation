<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <title>Automation - Edit Flow</title>
      <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="common/style.css">
   </head>
   <body>
      <?php
      //"Header" files
      require('./common/header.php');

      //Load Flow
      $id = mysqli_real_escape_string($dbh, $_REQUEST['id']);
      $flow = GetFlow($dbh, $id);
      if($flow) {
         //Do updates then reload the flow
         $action = $_REQUEST['action'];
         $name = mysqli_real_escape_string($dbh, $_REQUEST['name']);
         $step_id = mysqli_real_escape_string($dbh, $_REQUEST['step_id']);
         $flow_step_id = mysqli_real_escape_string($dbh, $_REQUEST['flow_step_id']);

         if($action == 'Delete') {
            DeleteFlow($dbh, $flow['id']);
            echo "<p>Deleted flow [id={$flow['id']}]</p>";
         } elseif($action == 'Update' && $name) {
            UpdateFlow($dbh, $flow['id'],$name);
            echo "<p>Updated flow [id={$flow['id']}|name={$flow['name']}]</p>";
         } elseif($action == 'Add Step' && $step_id) {
            AddFlowStep($dbh, $flow['id'],$step_id,count(GetFlowSteps($dbh, $flow['id'])) + 1);
            echo "<p>Added flow step [flow_id={$flow['id']}|step_id={$step_id}]</p>";
            if(!GetStep($dbh, $step_id)) {
               echo "<p>Warning: Step doesn't exist [id={$step_id}]</p>";
            }
         } elseif($action == 'Remove' && $flow_step_id) {
            DeleteFlowStep($dbh, $flow_step_id);
            echo "<p>Removed flow step [flow_step_id={$flow_step_id}]</p>";
         } elseif(($action == 'Move Down' || $action == 'Move Up') && $flow_step_id) {
            MoveFlowStep($dbh, $flow_step_id,($action == 'Move Down')?1:-1);
            echo "<p>Moved flow step [flow_step_id={$flow_step_id}]</p>";
         }

         //Reload the flow
         $flow = GetFlow($dbh, $id);
         if($flow) {
            $flowSteps = GetFlowSteps($dbh, $flow['id']);
            echo "id={$flow['id']}<br />";
            ?>
            <form method="post">
               <input type="hidden" name="id" value="<?php echo $flow['id']; ?>" />
               name=<input type="text" name="name" value="<?php echo htmlspecialchars($flow['name']); ?>" /><br />
               <input type="submit" name="action" value="Update" /><br />
               <input type="submit" name="action" value="Delete" /><br />
               <p>Steps (<?php echo count($flowSteps); ?>)
                  <table>
                     <tr><th>id</th><th>flow_id</th><th>step_id</th><th>index</th><th>name</th><th>selected</th></tr>
                     <?php
                     foreach($flowSteps as $flowStep) {
                        $step = GetStep($dbh, $flowStep['step_id']);
                        ?>
                        <tr>
                           <td><?php echo $flowStep['id']; ?></td>
                           <td><?php echo $flowStep['flow_id']; ?></td>
                           <td><?php echo $flowStep['step_id']; ?></td>
                           <td><?php echo $flowStep['index']; ?></td>
                           <td><a href="step_edit.php?id=<?php echo $step['id']; ?>"><?php echo $step['name']; ?></a></td>
                           <td><input type="radio" name="flow_step_id" value="<?php echo $flowStep['id']; ?>" /></td>
                        </tr>
                        <?php
                        //echo "<li><a href=\"step_edit.php?id={$step['id']}\">{$step['name']}</a></li>";
                     }
                     ?>
                  </table>
                  <input type="submit" name="action" value="Move Up" />
                  <input type="submit" name="action" value="Move Down" />
                  <input type="submit" name="action" value="Remove" />
               </p>
               step_id=<input type="text" name="step_id" /><br />
               <input type="submit" name="action" value="Add Step" />
            </form>
            <?php
         } else {
            echo "<p>Flow doesn't exist [id={$id}]</p>";
         }
      } else {
         echo "<p>Flow doesn't exist [id={$id}]</p>";
      }

      //"Footer" files
      require('./common/footer.php');
      ?>
   </body>
</html>
