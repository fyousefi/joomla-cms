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

<div class="row-fluid">
	<div class="span6">
		<legend><?php echo JText::_("JCM_RESTRICTIONS"); ?></legend>
		<?php
			foreach($this->form->getFieldset("restrictions") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>

		<legend><?php echo JText::_("JCM_SECURITY"); ?></legend>
		<?php
			foreach($this->form->getFieldset("security") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>
	</div>
</div>