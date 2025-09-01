<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

//defined ( '_JEXEC' ) or die ();

$config 	= JFactory::getConfig();
$logo 	 	= 'http://i.imgur.com/es7zIIH.png'; //<= replace your logo here (width:100)
$url  	 	= JUri::root();
$baseUrl 	= trim(preg_replace('/^https?:\/\//', '', $url),'/');
$sitename 	= $config->get( 'sitename' );

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="format-detection" content="telephone=no" /> <!-- disable auto telephone linking in iOS -->
		<title><?php echo $title; ?></title>
		<style type="text/css">
			h1,h2,h3{font-style:normal}#body .caption,#footer,#header{text-align:center}html{background-color:#f1f1f1;margin:0;padding:0;line-height:1.7;color:#555}#bodyCell,#bodyTable,body{height:100%!important;margin:0;padding:0;width:100%!important;font-family:Helvetica,Arial,"Lucida Grande",sans-serif}table{border-collapse:collapse}table[id=bodyTable]{width:100%!important;margin:auto;max-width:500px!important;color:#7A7A7A;font-weight:400}a img,img{border:0;outline:0;text-decoration:none;height:auto;line-height:100%}a{text-decoration:none!important;border-bottom:1px solid;color:#1DA1F2}#body .btn:hover,img{text-decoration:none}h1,h2,h3,h4,h5,h6{color:#5F5F5F;font-weight:400;font-family:Helvetica;font-size:20px;line-height:125%;text-align:Left;letter-spacing:normal;margin:0 0 15px;padding:0}.ExternalClass,.ExternalClass div,.ExternalClass font,.ExternalClass p,.ExternalClass span,.ExternalClass td,h1,h4{line-height:100%}.ExternalClass,.ReadMsgBody{width:100%}table,td{mso-table-lspace:0;mso-table-rspace:0}#outlook a{padding:0}img{-ms-interpolation-mode:bicubic;display:block;outline:0}a,blockquote,body,li,p,table,td{-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;font-weight:400!important}h1,h2,h3,h4{display:block;font-weight:400}.ExternalClass td[class=ecxflexibleContainerBox] h3{padding-top:10px!important}h1{font-size:26px}h2{font-size:20px;line-height:120%}h3{font-size:17px;line-height:110%}h4{font-size:18px;font-style:italic}a:hover{color:#1981c1}.container{margin:0 auto;max-width:600px}#body .btn,#header{margin-bottom:10px}#body{background:#fff;min-height:100px;border:1px solid #ccc;padding:20px;margin:0 10px 30px}#body .caption{font-weight:500;color:#1da1f2;line-height:1.5;}#body .btn{background:#1da1f2;color:#fff;height:40px;display:inline-block;line-height:40px;padding:0 20px;border-radius:4px;font-size:18px;border:none}#body .btn+.btn{margin-right:10px}#body .btn:hover{background:#1c8cd1}#header{padding-top:20px}#logo img{height:auto;width:100px;margin:0 auto}#footer{font-size:11px;padding:10px 30px;color:#767676;margin-bottom:30px}

			#body .cm-block+.cm-block{margin-top:20px}#body .quote{background:#f1f1f1;padding:10px 6px;font-size:13px;border-radius:4px;color:#666}#body ._quicklinks a{background:#dfdfdf;font-size:11px;padding:2px 10px;border-radius:2px}

			#body .i-block a{color:#333;text-decoration:none}.i-block .c{font-size:.9em}.i-block .h{color:#1da2f2}.i-block+.i-block{border-top:1px solid #dfdfdf;padding-top:10px;margin-top:10px}
		</style>
	</head>
	<body bgcolor="#F1F1F1" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<div style="background:#F1F1F1;margin:0;padding:0;line-height:1.7;color:#555">
			<div class="container">
				<?php if (!preg_match('/^\s*$/', $logo)): ?>
				<div id="header">
					<a id="logo" href="<?php echo $url ?>">
						<img src="<?php echo $logo ?>" />
					</a>
				</div>
				<?php endif; ?>
	
				<div id="body" style="-moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px;box-shadow:2px 2px 10px rgba(0,0,0,0.1);-mozbox-shadow:2px 2px 10px rgba(0,0,0,0.1);-webkit-box-shadow:2px 2px 10px rgba(0,0,0,0.1);"><?php echo $body; ?></div>
	
				<div id="footer">
					Ⓒ <?php echo date('Y'); ?> <a href="<?php echo $url ?>" target="_blank"><?php echo $sitename; ?></a>
					<?php if(isset($unsubscribe) && $unsubscribe!=''): ?>
					<br>
					<?php echo jtext::sprintf("JCM_EMAIL_FOOTER_DESC", '<a href="#" target="_blank">'.$baseUrl.'</a>', '<a href="'.$unsubscribe.'" style="color:#9697b4;text-decoration:underline" target="_blank">', '</a>'); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>