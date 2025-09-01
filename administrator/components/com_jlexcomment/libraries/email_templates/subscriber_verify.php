<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined('_JEXEC') or die;
ob_start(); ?>

<h1 class="caption"><?php echo jtext::_('JCM_HI') ?> <?php echo $entry->guest_name; ?>!</h1>
<div>
	<p style="text-align:center"><?php echo jtext::_("JCM_EMAIL_SUB_WELCOME") ?></p>
	<div><?php echo jtext::sprintf("JCM_EMAIL_SUB_WELCOME_INTRO", '<strong>'.$entry->title.'</strong>') ?></div>
	<div style="text-align:center">
		<a class="btn" href="<?php echo $entry->link2verify; ?>" target="_blank"><?php echo jtext::_("JCM_EMAIL_SUB_CONFIRM_BTN") ?></a>
	</div>
	<i><?php echo jtext::_("JCM_EMAIL_SUB_CONFIRM_LINK_DESC") ?></i>
	<div class="quote">
		<a href="<?php echo $entry->link2verify; ?>"><?php echo $entry->link2verify; ?></a>
	</div>
</div>

<?php
$body = ob_get_contents();
ob_end_clean();

include dirname(__FILE__) . '/body.php';