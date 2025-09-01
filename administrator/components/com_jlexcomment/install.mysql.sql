CREATE TABLE IF NOT EXISTS `#__jlexcomment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `obj_id` int(11) unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_email` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `root_parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `child_count` int(5) unsigned NOT NULL DEFAULT '0',
  `child_count_active` int(5) unsigned NOT NULL DEFAULT '0',
  `up_point` int(5) NOT NULL DEFAULT '0',
  `down_point` int(5) NOT NULL DEFAULT '0',
  `report_count` int(4) unsigned DEFAULT '0',
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime DEFAULT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `language` char(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*',
  `ip_address` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sticker_id` int(4) NOT NULL DEFAULT '0',
  `params` mediumtext COLLATE utf8mb4_unicode_ci,
  `reaction_count` int(5) NOT NULL DEFAULT '0',
  `reaction_data` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `style_id` int(3) NOT NULL DEFAULT '0',
  `giphy_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locked` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_obj` (`obj_id`),
  KEY `idx_pid` (`parent_id`),
  KEY `idx_rid` (`root_parent_id`),
  KEY `idx_created` (`created_time`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_published` (`published`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_blacklist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `method` tinyint(1) NOT NULL DEFAULT '1',
  `method_value` varchar(60) NOT NULL DEFAULT '',
  `reason` varchar(250) DEFAULT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_media` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(60) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `path` varchar(60) DEFAULT '',
  `fileSize` varchar(20) DEFAULT NULL,
  `fileName` varchar(60) DEFAULT NULL,
  `fileType` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cmid` (`comment_id`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_notification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `obj_id` int(11) unsigned NOT NULL,
  `comment_id` int(11) unsigned NOT NULL,
  `action_type` char(20) NOT NULL DEFAULT 'REPLY',
  `guest_remind_name` varchar(30) NOT NULL DEFAULT '',
  `guest_remind_email` char(40) DEFAULT NULL,
  `user_remind` int(10) unsigned NOT NULL,
  `guest_name` varchar(30) NOT NULL DEFAULT '',
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `unread` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_obj` (`obj_id`),
  KEY `idx_type` (`action_type`),
  KEY `idx_created` (`created_time`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_unread` (`unread`),
  KEY `idx_sent` (`sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_notification_off` (
  `uid` int(11) unsigned NOT NULL,
  `email` char(40) DEFAULT NULL,
  `created_time` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `idx_uid` (`uid`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_obj` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `com_name` char(30) NOT NULL DEFAULT '',
  `com_key` char(15) NOT NULL DEFAULT '',
  `com_id` int(11) unsigned NOT NULL,
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `cm_count` int(10) unsigned NOT NULL DEFAULT '0',
  `cm_count_active` int(10) unsigned NOT NULL DEFAULT '0',
  `cm_i_count` int(10) unsigned NOT NULL DEFAULT '0',
  `cm_i_count_active` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(250) DEFAULT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `params` text,
  `latest_update` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_replacer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `caption` varchar(60) NOT NULL DEFAULT '',
  `regexClause` varchar(250) NOT NULL DEFAULT '',
  `replaceClause` text NOT NULL,
  `group_cid` varchar(250) NOT NULL DEFAULT '',
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `reason_code` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `reason_msg` varchar(200) DEFAULT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `ip_address` varchar(150) NOT NULL,
  `guest_name` varchar(40) DEFAULT NULL,
  `guest_email` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_roles` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(3) unsigned NOT NULL,
  `title` varchar(40) NOT NULL DEFAULT '',
  `colour` char(20) NOT NULL DEFAULT '',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `created_time` datetime NOT NULL,
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_sticker` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `caption` varchar(250) NOT NULL DEFAULT '',
  `path2file` varchar(100) NOT NULL DEFAULT '',
  `group_id` int(3) unsigned NOT NULL DEFAULT '0',
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_sticker_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(200) DEFAULT '',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_subscribe` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `obj_id` int(11) unsigned NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `email_hash` char(60) DEFAULT NULL,
  `email_confirmed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `point` int(10) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_obj` (`obj_id`),
  KEY `idx_point` (`point`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_users` (
  `userid` int(11) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `auth` varchar(10) NOT NULL,
  `auth_id` varchar(60) NOT NULL,
  `auth_url` varchar(200) NOT NULL,
  `auth_picture` varchar(200) NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_vote` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `point` tinyint(1) NOT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `ip_address` varchar(150) NOT NULL,
  `change_times` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cmid` (`comment_id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_style` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `caption` varchar(100) NOT NULL DEFAULT '',
  `css` text NOT NULL,
  `maxlength` varchar(5) NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__jlexcomment_sync` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `object` varchar(40) NOT NULL,
  `entry_details` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cb_entry_details` text NOT NULL,
  `entry_updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cb_entry_updated` text NOT NULL,
  `author_follow` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cb_author_follow` text NOT NULL,
  `latest_log` text,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `modified_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;