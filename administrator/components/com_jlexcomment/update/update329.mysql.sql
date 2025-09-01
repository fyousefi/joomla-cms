ALTER TABLE `#__jlexcomment` ADD `locked` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `giphy_id`;

ALTER TABLE `#__jlexcomment_vote` CHANGE `ip_address` `ip_address` VARCHAR(150)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';

ALTER TABLE `#__jlexcomment` CHANGE `ip_address` `ip_address` VARCHAR(150)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';

ALTER TABLE `#__jlexcomment_report` CHANGE `ip_address` `ip_address` VARCHAR(150)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';