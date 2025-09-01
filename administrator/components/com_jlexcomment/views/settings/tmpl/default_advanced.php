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
	<div class="span12">
		<?php
			foreach($this->form->getFieldset("advanced") as $field):
			    echo $field->hidden?$field->input:$field->renderField();
			endforeach;
		?>
	</div>

	<div class="span6">
		
	</div>
</div>