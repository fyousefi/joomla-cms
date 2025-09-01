ALTER TABLE `#__jlexcomment` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__jlexcomment`
	CHANGE COLUMN `guest_name` `guest_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	CHANGE COLUMN `guest_email` `guest_email` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
	CHANGE COLUMN `modified_by` `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
	ADD `root_parent_id` INT(11) unsigned NOT NULL DEFAULT '0' AFTER `parent_id`,
	ADD `giphy_id` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `style_id`;

ALTER TABLE `#__jlexcomment_media`
	CHANGE COLUMN `comment_id` `comment_id` int(11) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `#__jlexcomment_obj`
	CHANGE COLUMN `com_key` `com_key` char(15) NOT NULL DEFAULT '',
	CHANGE COLUMN `com_id` `com_id` int(11) unsigned NOT NULL,
	CHANGE COLUMN `created_by` `created_by` int(11) unsigned NOT NULL DEFAULT '0',
	CHANGE COLUMN `created_time` `created_time` datetime DEFAULT NULL,
	ADD `latest_update` int(10) unsigned NOT NULL DEFAULT '0' AFTER `params`;


ALTER TABLE `#__jlexcomment_replacer`
	CHANGE COLUMN `replaceClause` `replaceClause` text NOT NULL;

ALTER TABLE `#__jlexcomment_roles`
	CHANGE COLUMN `created_by` `created_by` int(11) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `#__jlexcomment_sticker`
	CHANGE COLUMN `group_id` `group_id` int(3) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `#__jlexcomment_sticker_group`
	CHANGE COLUMN `created_by` `created_by` int(11) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `#__jlexcomment_subscribe`
	CHANGE COLUMN `email` `email` varchar(60) NOT NULL DEFAULT '',
	CHANGE COLUMN `created_by` `created_by` int(11) unsigned NOT NULL DEFAULT '0',
	CHANGE COLUMN `point` `point` int(10) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `#__jlexcomment_subscribe`
	DROP COLUMN `point_time`;