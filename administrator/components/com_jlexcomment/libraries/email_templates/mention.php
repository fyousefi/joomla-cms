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

<h1 class="caption"><?php echo jtext::_('JCM_HI') ?> <?php echo $nof->rep_name ?>!</h1>
<span><?php echo $nof->caption; ?> <?php echo jtext::_('JCM_IN') ?> <b><?php echo $nof->object_name; ?></b></span>

<div class="quote"><?php echo $nof->comment; ?></div>

<p style="text-align:center">
	<a class="btn" href="<?php echo $nof->url ?>"><?php echo jtext::_('JCM_NOF_VIEW_IT') ?></a>
</p>

<?php
$body = ob_get_contents();
ob_end_clean();

$title 			= $nof->caption;
$unsubscribe 	= $nof->unsubscribe;
include dirname(__FILE__) . '/body.php';