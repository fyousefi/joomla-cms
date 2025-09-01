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

<div class="row-fluid row">
	<div class="span6 col-md-6">
		<legend><?php echo JText::_("JCM_COMMENT"); ?></legend>
		<?php
			foreach($this->form->getFieldset("comment") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>
	</div>

	<div class="span6 col-md-6">
		<legend><?php echo JText::_("JCM_COMMENT_LISTING"); ?></legend>
		<?php
			foreach($this->form->getFieldset("comment_list") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>

		<legend><?php echo JText::_("JCM_COMMENT_FORM"); ?></legend>
		<?php
			foreach($this->form->getFieldset("comment_form") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>

		<legend><?php echo JText::_("JCM_GIPHY"); ?></legend>
		<?php
			foreach($this->form->getFieldset("giphy") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>
	</div>
</div>