INSERT INTO `#__jlexcomment_sticker_group` (`id`, `name`, `description`, `created_by`, `created_time`, `published`)
VALUES
	(1, 'Minion', '', {{userid}}, '{{date}}', 1),
	(2, 'Halloween', '', {{userid}}, '{{date}}', 1);

INSERT INTO `#__jlexcomment_sticker` (`id`, `caption`, `path2file`, `group_id`, `created_by`, `created_time`, `published`)
VALUES
	(NULL, 'Curious', 'media/jcm/stickers/9c0a8536e3c08259.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Alert', 'media/jcm/stickers/df7aad53879d4eeb.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Shy', 'media/jcm/stickers/3ad0de03f4b80069.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Reading', 'media/jcm/stickers/2165e9323bdd41d3.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Kungfu', 'media/jcm/stickers/d2efa914d053223a.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Dancing', 'media/jcm/stickers/2be209641920b8d8.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Banana', 'media/jcm/stickers/fc5dd6cc4c6133f0.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Sing', 'media/jcm/stickers/0dfe5713be7d2242.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Game', 'media/jcm/stickers/1ed4f1390c3326e0.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Hi', 'media/jcm/stickers/81634c391a1ebae7.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Evil', 'media/jcm/stickers/eecd17d27a773575.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Girl', 'media/jcm/stickers/587428533e0e8bff.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Happy', 'media/jcm/stickers/db3cc6ef04e8b379.png', 1, {{userid}}, '{{date}}', 1),
	(NULL, 'Full', 'media/jcm/stickers/3398898c473e6da9.gif', 2, {{userid}}, '{{date}}', 1),
	(NULL, 'Happy', 'media/jcm/stickers/f8f0cef3ee68f552.gif', 2, {{userid}}, '{{date}}', 1),
	(NULL, 'Angry', 'media/jcm/stickers/0a9344469562a3a0.gif', 2, {{userid}}, '{{date}}', 1),
	(NULL, 'No', 'media/jcm/stickers/d56b546e1069034a.gif', 2, {{userid}}, '{{date}}', 1),
	(NULL, 'Sad', 'media/jcm/stickers/a1150bc6d8b83801.gif', 2, {{userid}}, '{{date}}', 1),
	(NULL, 'Good night', 'media/jcm/stickers/97613da26df31936.gif', 2, {{userid}}, '{{date}}', 1),
	(NULL, 'Ok', 'media/jcm/stickers/77722f9416b05d78.gif', 2, {{userid}}, '{{date}}', 1),
	(NULL, 'Bye', 'media/jcm/stickers/ace9218446392ed6.gif', 2, {{userid}}, '{{date}}', 1);

INSERT INTO `#__jlexcomment_replacer` (`id`, `caption`, `regexClause`, `replaceClause`, `group_cid`, `created_by`, `created_time`, `published`)
VALUES
	(2, 'Image', '/(?s)<a[^<]*>.*?<\\/a>(*SKIP)(*F)|(https?:\\/\\/)([^\\s([\"<,>\\/]*)(\\/)[^\\s[\",><]*(\\.png|\\.jpg)(\\?[^\\s[\",><]*)?/m', '<a href=\"$0\" target=\"_blank\">$0</a>\r\n<img src=\"$0\" class=\"jcm-img-preview\" />', '1', {{userid}}, '{{date}}', 1),
	(3, 'Vimeo', '/(?s)<a[^<]*>.*?<\\/a>(*SKIP)(*F)|(?:https?:\\/\\/)?(?:www\\.)?(?:player\\.)?vimeo\\.com\\/(?:[a-z]*\\/)*([0-9]{6,11})/mi', '<a href=\"$0\">$0</a>\r\n<iframe src=\"https://player.vimeo.com/video/$1?color=1e6e75&byline=0&portrait=0&badge=0\" width=\"640\" height=\"360\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', '1', {{userid}}, '{{date}}', 1),
	(4, 'Youtube', '/(?s)<a[^<]*>.*?<\\/a>(*SKIP)(*F)|(?:http(?:s)?:\\/\\/)?(?:www\\.)?(?:m\\.)?(?:youtu\\.be\\/|youtube\\.com\\/(?:(?:watch)?\\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\\/))([A-z0-9\\-\\_]+)/m', '<div class=\"jcm-player\" data-source=\"yt\"><div class=\"jcm-player-main\" data-embed=\"https://www.youtube.com/embed/$1?autoplay=1\" data-image=\"https://img.youtube.com/vi/$1/hqdefault.jpg\"></div><a href=\"$0\" class=\"jcm-player-title\" target=\"_blank\">$0</a></div>', '1', {{userid}}, '{{date}}', 1);


INSERT INTO `#__jlexcomment_style` (`id`, `caption`, `css`, `maxlength`, `published`)
VALUES
	(1, 'Style 1', 'background-image:url(//i.imgur.com/IKsJNGo.jpg);\r\nbackground-size:cover;\r\nbackground-position:center;\r\ncolor:#fff;\r\nfont-size:36px;\r\nline-height:1.8;\r\nfont-weight:bold;', '0', 1),
	(2, 'Style 2', 'background-image:url(//i.imgur.com/Hw3YN6n.jpg);\r\nbackground-size:cover;\r\nbackground-position:center;\r\ncolor:#fff;\r\nfont-size:36px;\r\nline-height:1.8;\r\nfont-weight:bold;', '0', 1),
	(3, 'Style 3', 'background: #FC466B;\r\nbackground: -webkit-linear-gradient(to right, #3F5EFB, #FC466B);\r\nbackground: linear-gradient(to right, #3F5EFB, #FC466B);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(4, 'Style 4', 'background: #00b09b;\r\nbackground: -webkit-linear-gradient(to right, #96c93d, #00b09b); \r\nbackground: linear-gradient(to right, #96c93d, #00b09b);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(5, 'Style 5', 'background: #1488CC;\r\nbackground: -webkit-linear-gradient(to right, #2B32B2, #1488CC);\r\nbackground: linear-gradient(to right, #2B32B2, #1488CC);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(6, 'Style 6', 'background: #e52d27;\r\nbackground: -webkit-linear-gradient(to right, #b31217, #e52d27);\r\nbackground: linear-gradient(to right, #b31217, #e52d27);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1),
	(7, 'Style 7', 'background: #6441A5;\r\nbackground: -webkit-linear-gradient(to right, #2a0845, #6441A5);\r\nbackground: linear-gradient(to right, #2a0845, #6441A5);\r\ncolor:#fff;\r\nfont-size:36px;\r\nfont-weight:bold;\r\nline-height:1.7;', '0', 1);
