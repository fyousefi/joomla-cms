ALTER TABLE #__jlexcomment_notification
	ADD `guest_remind_name` varchar(30) NOT NULL DEFAULT '' AFTER `action_type`,
	ADD `guest_remind_email` char(40) DEFAULT NULL AFTER `guest_remind_name`;

ALTER TABLE #__jlexcomment_notification_off
	ADD `email` char(40) DEFAULT NULL AFTER `uid`;