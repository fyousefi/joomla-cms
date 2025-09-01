CREATE TABLE IF NOT EXISTS `#__jlexcomment_sync` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `object` varchar(40) NOT NULL,
  `entry_details` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cb_entry_details` text NOT NULL COMMENT 'Get detail of item such as title, link, section_id',
  `entry_updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cb_entry_updated` text NOT NULL COMMENT 'Event when rating of item is changed',
  `author_follow` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Turn On/Off author follow automatilly',
  `cb_author_follow` text NOT NULL COMMENT 'Auto add subscription for user who created reviewed item',
  `latest_log` text NOT NULL COMMENT 'Latest error logging when process this comand',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `modified_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;