#!/usr/bin/perl

use strict;
use warnings;
use DBI;

#Flush output
$|=1;

#Constants
my $dbHost = '';
my $dbName = '';
my $dbUser = '';
my $dbPass = '';

#Function prototypes
sub RunFlow($$);
sub RunSQL($);
sub RunCMD($);
sub ReplaceVariables($);

#Connect to the database
print("Attempting to connect to the database...\n");
my $dbh = DBI->connect("dbi:mysql:$dbName:$dbHost",$dbUser,$dbPass);
print("Connected\n");

#Get the last step added in the oldest group (top of the first stack in the queue)
my $select = $dbh->prepare("SELECT a.id,a.step_id,a.run_group,s.name,s.flow_id,s.sql,s.cmd FROM run_stack a JOIN (SELECT max(id) id FROM run_stack GROUP BY run_group ORDER BY run_group ASC LIMIT 1) b ON a.id = b.id LEFT JOIN steps s ON a.step_id = s.id");
#Delete the step from run_stack
my $delete = $dbh->prepare("DELETE FROM run_stack WHERE id=?");
#Insert into run_log
my $insert = $dbh->prepare("INSERT INTO run_log (step_id,start_time,status) VALUES (?,sysdate(),'running')");
#Update run_log
my $update = $dbh->prepare("UPDATE run_log SET stop_time=sysdate(),status=?,return_code=? WHERE id=?");
#Get a flow's steps; reverse order for pushing onto the stack
my $getSteps = $dbh->prepare("SELECT step_id from flow_steps where flow_id=? ORDER BY `index` DESC");
#Add a step to the run_stack
my $addStep = $dbh->prepare("CALL RunStepInGroup(?,?)");
#Update the heartbeats table
my $heartbeat = $dbh->prepare("UPDATE heartbeats SET date = sysdate() WHERE name = 'automation.pl'");
#Get tasks to execute
my $selectTasks = $dbh->prepare("SELECT id,step_id,date,`interval` FROM tasks WHERE sysdate() > date ORDER BY date ASC");
#Update tasks
my $updateTask = $dbh->prepare("UPDATE tasks SET date = from_unixtime(unix_timestamp(date) + (`interval` * (floor((unix_timestamp(sysdate())-unix_timestamp(date)) / `interval`)+1))) WHERE id=?");
#Delete tasks
my $deleteTask = $dbh->prepare("DELETE FROM tasks WHERE id=?");
#Add step from a task
my $addTaskStep = $dbh->prepare("CALL RunStep(?)");
#Get the value of a variable
my $getVariableValue = $dbh->prepare("SELECT `value` FROM variables WHERE name = ?");

#Always check to see if anything is on the run stack
while(1) {
   #Push tasks onto the stack
   if(!$selectTasks->execute()) {
      die("selectTasks didn't execute\n");
   } else {
      my @tasks = ();
      while(my $row = $selectTasks->fetchrow_hashref()) {
         my %task = ();
         $task{'id'} = $row->{id};
         $task{'step_id'} = $row->{step_id};
         $task{'date'} = $row->{date};
         $task{'interval'} = $row->{interval};
         push(@tasks,\%task);
      }
      for(my $i = 0; $i < scalar(@tasks); ++$i) {
         my $task = $tasks[$i];
         #Push the task onto the stack
         print(sprintf("   Pushing step [step_id=%d]\n",$task->{'step_id'}));
         if(!$addTaskStep->execute($task->{'step_id'})) {
            die("Couldn't add the taks to the run_stack\n");
         }
         if($task->{'interval'}) {
            #Update the task
            if(!$updateTask->execute($task->{'id'})) {
               die("Couldn't update the task\n");
            }
         } else {
            #Delete the task
            if(!$deleteTask->execute($task->{'id'})) {
               die("Couldn't delete the task\n");
            }
         }
      }
   }

   #Run the next step on the stack
   if(!$select->execute()) {
      die("select didn't execute\n");
   } else {
      if(my $row = $select->fetchrow_hashref()) {
         printf("Starting step [id=%d|step_id=%d|name=%s]\n",$row->{id},$row->{step_id},$row->{name});
         if(!$row->{name}) {
            print("   Warning: That step doesn't exist\n");
         } else {
            #Log this run
            if(!$insert->execute($row->{step_id})) {
               die("Couldn't insert into run_log\n");
            }
            my $run_log_id = $insert->{'mysql_insertid'};

            #The actual run
            my $return_code = 0;
            if($row->{flow_id}) {
               $return_code = RunFlow($row->{run_group},$row->{flow_id});
            } elsif($row->{sql}) {
               $return_code = RunSQL(ReplaceVariables($row->{sql}));
            } elsif($row->{cmd}) {
               $return_code = RunCMD(ReplaceVariables($row->{cmd}));
            }

            #Update the run_log with the result
            if($return_code == 0) {
               $update->execute('success',0,$run_log_id);
            } else {
               $update->execute('fail',$return_code,$run_log_id);
            }
         }
         if(!$delete->execute($row->{id})) {
            die(sprintf("   Failed to delete step from run_stack [run_stack_id=%s]",$row->{id}));
         }
      } else {
         #die("Nothing found on the run stack\n");
         sleep(30);
      }
   }

   #Update
   if(!$heartbeat->execute()) {
      print("Failed to update the heartbeats table\n");
   }
}

#Explicit exit
exit(0);

sub RunFlow($$) {
   my $run_group = shift;
   my $flow_id = shift;
   my $error = 0;
   print("   Inserting flow...\n");

   #Push all this flow's steps onto the run_stack
   if($getSteps->execute($flow_id)) {
      while(my $row = $getSteps->fetchrow_hashref()) {
         print(sprintf("   Pushing step [step_id=%d]\n",$row->{step_id}));
         if(!$addStep->execute($row->{step_id},$run_group)) {
            ++$error;
         }
      }
   } else {
      ++$error;
   }

   #Return result
   if(!$error) {
      print("   Success\n");
      return 0;
   } else {
      printf("   Fail [run_group=%d|flow_id=%d]\n",$run_group,$flow_id);
      return 1;
   }
}

sub RunSQL($) {
   my $sql = shift;
   print("   Running sql...\n");

   #Execute SQL
   my $sth = $dbh->prepare($sql);
   if($sth->execute()) {
      print("   Success\n");
      return 0;
   } else {
      printf("   Fail [sql=%s]\n",$sql);
      return 1;
   }
}

sub RunCMD($) {
   my $cmd = shift;
   print("   Running cmd...\n");

   #Execute CMD
   my $return_code = system($cmd);
   if($return_code == 0) {
      print("   Success\n");
   } else {
      printf("   Fail [cmd=%s]\n",$cmd);
   }

   #Return code
   return $return_code;
}

sub ReplaceVariables($) {
   my $str = shift;
   my $limit = 100;
   while($limit-- > 0 && $str =~ m/\[\[(.*?)\]\]/) {
      my $variable = $1;
      if($getVariableValue->execute($variable)) {
         if(my $row = $getVariableValue->fetchrow_hashref()) {
            my $replace = $row->{value};
            $str =~ s/\[\[(.*?)\]\]/$replace/;
            print "   [[$variable]] = $replace\n";
         }
      } else {
         die("Couldn't select from variables table\n");
      }
   }
   if($limit <= 0) {
      print "   Warning: limit was reached when replacing variables!\n";
   }
   return $str;
}
