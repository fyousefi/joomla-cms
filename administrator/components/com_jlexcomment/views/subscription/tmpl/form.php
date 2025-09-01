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
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment&view=subscription'?>" 
	method="post" name="adminForm" id="adminForm" class="form-horizontal jcm-box">
		<?php
			foreach($this->form->getFieldset("basic") as $field):
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