ALTER TABLE #__jlexcomment
	ADD `reaction_count` int(5) NOT NULL DEFAULT '0' AFTER `params`,
	ADD `reaction_data` varchar(255) DEFAULT NULL AFTER `reaction_count`,
	ADD `style_id` int(3) NOT NULL DEFAULT '0' AFTER `reaction_data`;

CREATE TABLE IF NOT EXISTS `#__jlexcomment_style` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `caption` varchar(100) NOT NULL DEFAULT '',
  `css` text NOT NULL,
  `maxlength` varchar(5) NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__jlexcomment_style` (`id`, `caption`, `css`, `maxlength`, `published`)
VALUES
	(1, 'Style 1', 'background-image:url(//i.imgur.com/IKsJNGo.jpg);\r\nbackground-size:cover;\r\nbackground-position:center;\r\ncolor:#fff;\r\nfont-size:36px;\r\nline-height:1.8;\r\nfont-weight:bold;', '0', 1),
	(2, 'Style 2', 'background-image:url(//i.imgur.com/Hw3YN6n.jpg);\r\nbackground-size:cover;\r\nbackground-position:center;\r\ncolor:#fff;\r\nfont-size:36px;\r\nline-height:1.8;\r\nfont-weight:bold;', '0', 1),
	(3, 'Style 3', 'background: #FC466B;\r\nbackground: -webkit-linear-gradient(to right, #3F5EFB, #FC466B);\r\nbackground: linear-gradient(to right, #3F5EFB, #FC466B);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(4, 'Style 4', 'background: #00b09b;\r\nbackground: -webkit-linear-gradient(to right, #96c93d, #00b09b); \r\nbackground: linear-gradient(to right, #96c93d, #00b09b);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(5, 'Style 5', 'background: #1488CC;\r\nbackground: -webkit-linear-gradient(to right, #2B32B2, #1488CC);\r\nbackground: linear-gradient(to right, #2B32B2, #1488CC);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(6, 'Style 6', 'background: #e52d27;\r\nbackground: -webkit-linear-gradient(to right, #b31217, #e52d27);\r\nbackground: linear-gradient(to right, #b31217, #e52d27);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(7, 'Style 7', 'background: #6441A5;\r\nbackground: -webkit-linear-gradient(to right, #2a0845, #6441A5);\r\nbackground: linear-gradient(to right, #2a0845, #6441A5);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1);
