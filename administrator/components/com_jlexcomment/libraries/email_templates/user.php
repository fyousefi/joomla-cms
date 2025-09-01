<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();
ob_start (); ?>

<h1 class="caption"><?php echo jtext::sprintf("JCM_HELLO_USER", $comment->author); ?></h1>
<div style="text-align:center"><?php echo jtext::sprintf("JCM_EMAIL_USER_BODY", '<a href="'.$comment->url.'">'.$comment->entry_name.'</a>') ?>
</div>
<br/>
<div class="quote"><?php echo $comment->comment; ?></div>
<?php
$body = ob_get_contents ();
ob_end_clean ();

$title = $user_title;
include dirname(__FILE__) . '/body.php';
?>