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
<div id="jlexcomment" class="_settings">
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment&view=settings'?>" method="post" 
	name="adminForm" id="adminForm"
	class="form-horizontal jcm-box nospace">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab" class="active"><?php echo JText::_("JCM_GENERAL"); ?></a></li>
				<li><a href="#layout" data-toggle="tab"><?php echo JText::_("JCM_LAYOUT"); ?></a></li>
				<li><a href="#permission" data-toggle="tab"><?php echo JText::_("JCM_PERMISSION"); ?></a></li>
				<li><a href="#restriction" data-toggle="tab"><?php echo JText::_("JCM_RESTRICTIONS"); ?></a></li>
				<li><a href="#advanced" data-toggle="tab"><?php echo JText::_("JCM_ADVANCED"); ?></a></li>
			</ul>

<?php
	if(JE_JVERSION=="J4")
	{
		foreach($this->form->getFieldset() as $field)
		{
			if(strtolower($field->type)=="radio")
			{
				$this->form->setFieldAttribute($field->fieldname, "layout", "joomla.form.field.radio.switcher", $field->group);
				$this->form->setFieldAttribute($field->fieldname, "class", "", $field->group);
			}
		}
	}
?>

			<div class="tab-content">
				<div class="tab-pane active" id="general">
					<?php echo $this->loadTemplate('general'); ?>
				</div>
				<div class="tab-pane" id="layout">
					<?php echo $this->loadTemplate('layout'); ?>
				</div>
				<div class="tab-pane" id="permission">
					<?php echo $this->loadTemplate('permission'); ?>
				</div>
				<div class="tab-pane" id="restriction">
					<?php echo $this->loadTemplate('restrictions'); ?>
				</div>
				<div class="tab-pane" id="advanced">
					<?php echo $this->loadTemplate('advanced'); ?>
				</div>
			</div>
		<div class="clearfix"></div>
		<input type="hidden" name="task" value="" />
	</form>
</div>