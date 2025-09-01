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
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment&view=style'?>" 
	method="post" name="adminForm" id="adminForm" class="form-horizontal jcm-box row">
		<div class="span6 col-md-6">
		<?php
			foreach($this->form->getFieldset("basic") as $field):
			    if ($field->hidden):
			        echo $field->input;
			    else:
			    ?>
			    <div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			    <?php
			    endif;
			endforeach;
		?>
		</div>
		<div class="span6 col-md-6">
			<label><?php echo JText::_('JCM_PREVIEW') ?></label>
			<div id="jcm-style-preview"><?php echo JText::_('JCM_HELLO_MSG') ?></div>
		</div>

		<input type="hidden" name="task">
	</form>
</div>