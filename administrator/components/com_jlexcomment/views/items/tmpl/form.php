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
<div id="jlexcomment">
	<div class="jcm-box">
		<form action="<?=JUri::base(true)."/index.php?option=com_jlexcomment&view=items"?>" method="post" name="adminForm" id="adminForm">
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
			?>

			<?php
				$groups = ["basic", "params"];
				foreach($groups as $g)
				{
					foreach($this->form->getFieldset($g) as $field)
					{
						echo $field->hidden?$field->input:$field->renderField();
					}
				}
			?>
			<input type="hidden" name="task" value="" />
		</form>
	</div>
</div>