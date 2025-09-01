<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
?>
<div id="jlexcomment">
	<div class="jcm-box">
		<form 
			action="<?=JUri::base(true)."/index.php?option=com_jlexcomment"?>" 
			method="post" name="adminForm" id="adminForm"
			class="form-horizontal">
			<?php
				if(JE_JVERSION=="J3")
				{
					foreach($this->form->getFieldset() as $field)
					{
						if(strtolower($field->type)=="radio")
						{
							$this->form->setFieldAttribute($field->fieldname, "layout", "", $field->group);
						}
					}
				}

				foreach($this->form->getFieldset("basic") as $field):
				    if ($field->hidden):
				        echo $field->input;
				    else:
				    	echo $field->renderField();
				    endif;
				endforeach;
			?>

			<h3>Media</h3>
			<div class="">
				<input type="file" multiple id="jcm-file-attached">
				<ul id="list-files" class="jcm-list-unstyled">
				</ul>
			</div>

			<h3>Location</h3>
			<?php
				foreach($this->form->getFieldset("params") as $field):
				    if ($field->hidden):
				        echo $field->input;
				    else:
				    	echo $field->renderField();
				    endif;
				endforeach;
			?>

			<input type="hidden" name="task" value="">
		</form>
	</div>
</div>