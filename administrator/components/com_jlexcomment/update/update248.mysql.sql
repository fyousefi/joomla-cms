ALTER TABLE `#__jlexcomment_subscribe`
	ADD `published` TINYINT(1)  UNSIGNED  NULL  DEFAULT '1'  AFTER `point`;


ALTER TABLE `#__jlexcomment`
	ADD INDEX `idx_obj` (`obj_id`),
  	ADD INDEX `idx_pid` (`parent_id`),
  	ADD INDEX `idx_rid` (`root_parent_id`),
	ADD INDEX `idx_created` (`created_time`),
	ADD INDEX `idx_createdby` (`created_by`),
	ADD INDEX `idx_published` (`published`),
	ADD INDEX `idx_ip` (`ip_address`),
	ADD INDEX `idx_language` (`language`);


ALTER TABLE `#__jlexcomment_media`
	ADD INDEX `idx_cmid` (`comment_id`),
  	ADD INDEX `idx_createdby` (`created_by`);


ALTER TABLE `#__jlexcomment_notification_off`
	ADD INDEX `idx_uid` (`uid`),
  	ADD INDEX `idx_email` (`email`);


ALTER TABLE `#__jlexcomment_notification`
	ADD INDEX `idx_obj` (`obj_id`),
  	ADD INDEX `idx_type` (`action_type`),
  	ADD INDEX `idx_created` (`created_time`),
  	ADD INDEX `idx_createdby` (`created_by`),
  	ADD INDEX `idx_unread` (`unread`),
  	ADD INDEX `idx_sent` (`sent`);


ALTER TABLE `#__jlexcomment_subscribe`
	ADD INDEX `idx_obj` (`obj_id`),
  	ADD INDEX `idx_point` (`point`),
  	ADD INDEX `idx_createdby` (`created_by`),
  	ADD INDEX `idx_published` (`published`);


ALTER TABLE `#__jlexcomment_vote`
	ADD INDEX `idx_cmid` (`comment_id`),
  	ADD INDEX `idx_ip` (`ip_address`),
  	ADD INDEX `idx_createdby` (`created_by`);