ALTER TABLE `#__jlexcomment_vote` CHANGE `ip_address` `ip_address` VARCHAR(45)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';

ALTER TABLE `#__jlexcomment` CHANGE `ip_address` `ip_address` VARCHAR(45)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';

ALTER TABLE `#__jlexcomment_report` CHANGE `ip_address` `ip_address` VARCHAR(45)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';