<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined('_JEXEC') or die;

if(JE_JVERSION=="J4")
{
	foreach($this->form->getFieldset() as $field)
	{
		if(strtolower($field->type)=="radio")
		{
			$this->form->setFieldAttribute($field->fieldname, "layout", "joomla.form.field.radio.switcher", $field->group);
		}
	}
}

?>

<div id="jlexcomment">
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment'?>" method="post" name="adminForm" id="adminForm">
		<div class="form-horizontal jcm-box">
		 	<?php
				foreach($this->form->getFieldset("basic") as $field):
				    if ($field->hidden):
				        echo $field->input;
				    else:
				    	echo $field->renderField();
				    endif;
				endforeach;
			?>
		</div>
		<input type="hidden" name="task">
	</form>
</div>