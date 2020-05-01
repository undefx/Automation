--
-- Table structure for table `email_addresses`
--
CREATE TABLE `email_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `email` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `email_groups`
--
CREATE TABLE `email_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `email_queue`
--
CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` varchar(256) NOT NULL,
  `to` varchar(256) DEFAULT NULL,
  `to_group` int(11) DEFAULT NULL,
  `subject` varchar(1024) NOT NULL,
  `body` text NOT NULL,  -- 64 KiB
  `priority` double NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `flow_steps`
--
CREATE TABLE `flow_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flow_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `index` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Table structure for table `flows`
--
CREATE TABLE `flows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `heartbeats`
--
CREATE TABLE `heartbeats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `run_log`
--
CREATE TABLE `run_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `stop_time` datetime DEFAULT NULL,
  `status` varchar(32) NOT NULL,
  `return_code` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `run_stack`
--
CREATE TABLE `run_stack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_id` int(11) NOT NULL,
  `run_group` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Table structure for table `sequence`
--
CREATE TABLE `sequence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(254) NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `steps`
--
CREATE TABLE `steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `flow_id` int(11) DEFAULT NULL,
  `sql` varchar(1024) DEFAULT NULL,
  `cmd` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Table structure for table `tasks`
--
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `interval` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

--
-- Table structure for table `variables`
--
CREATE TABLE `variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `value` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Dumping routines for database 'automation'
--
DELIMITER ;;
CREATE FUNCTION `GetCurrentValue`(name varchar(32)) RETURNS int(11)
BEGIN
   return (select `value` - 1 from sequence s where s.name = name);
END ;;
DELIMITER ;

DELIMITER ;;
CREATE FUNCTION `GetNextValue`(name varchar(32)) RETURNS int(11)
BEGIN
   declare temp int;
   set temp = (select `value` from sequence s where s.name = name);
   update sequence s set `value` = `value` + 1 where s.name = name;
   return temp;
END ;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `RunStep`(step_id INT)
BEGIN
   INSERT INTO run_stack (step_id,run_group) VALUES (step_id,GetNextValue('run_group'));
END ;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `RunStepInGroup`(step_id INT,run_group INT)
BEGIN
   INSERT INTO run_stack (step_id,run_group) VALUES (step_id,run_group);
END ;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `SendEmail`(from_ VARCHAR(256), to_ VARCHAR(256), subject_ VARCHAR(1024), body_ TEXT, priority_ DOUBLE)
BEGIN
   INSERT INTO email_queue (`from`,`to`,`subject`,`body`,`priority`,`timestamp`) VALUES (from_,to_,subject_,body_,priority_,UNIX_TIMESTAMP(NOW()));
END ;;
DELIMITER ;

DELIMITER ;;
CREATE PROCEDURE `SendGroupEmail`(from_ VARCHAR(256), group_name_ VARCHAR(256), subject_ VARCHAR(1024), body_ TEXT, priority_ DOUBLE)
BEGIN
   INSERT INTO email_queue (`from`,`to_group`,`subject`,`body`,`priority`,`timestamp`) VALUES (from_,(SELECT id FROM email_groups WHERE name = group_name_),subject_,body_,priority_,UNIX_TIMESTAMP(NOW()));
END ;;
DELIMITER ;

--
-- Initial values
--
INSERT INTO `heartbeats` (`name`, `date`) VALUES ('automation.pl', now());
INSERT INTO `sequence` (`name`, `value`) VALUES ('run_group', 0);
