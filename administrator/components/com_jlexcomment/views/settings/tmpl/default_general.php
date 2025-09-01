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
		<legend><?php echo JText::_("JCM_SYSTEM"); ?></legend>
		<?php
			foreach($this->form->getFieldset("system") as $field):
				echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>

		<legend><?php echo JText::_("JCM_AUTHENTICATION"); ?></legend>
		<?php
			foreach($this->form->getFieldset("authentication") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>
	</div>

	<div class="span6 col-md-6">
		<legend><?php echo JText::_("JCM_ADMIN_NOTIFICATION"); ?></legend>
		<?php
			foreach($this->form->getFieldset("notification") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>

		<legend><?php echo JText::_("JCM_USER_NOTIFICATION"); ?></legend>
		<?php
			foreach($this->form->getFieldset("notification_user") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>

		<legend>RSS</legend>
		<?php
			foreach($this->form->getFieldset("rss") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>

		<legend>CronJob</legend>
		<?php
			foreach($this->form->getFieldset("cronjob") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>
	</div>
</div>