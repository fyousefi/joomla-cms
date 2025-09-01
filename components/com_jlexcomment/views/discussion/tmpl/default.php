<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();
?>

<?php if ($this->heading):?>
	<div class="page-header">
		<h2><?php echo $this->heading ?></h2>
	</div>
<?php endif; ?>

<?php echo $this->content; ?>