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
	<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&view=license',false) ?>" method="post" name="adminForm" id="adminForm">
		<div id="j-sidebar-container" class="j-sidebar-container j-sidebar-visible">
		    <?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
			<div id="jcm-license-form">
				<p>
					<span style="padding-bottom: 10px;display: block;">In order using this extension, you must enter your API key (one time only). You can get this key from here.</span>
					<img src="components/com_jlexcomment/assets/api_key.png" />
				</p>
				<input type="text" name="api_key" id="api_key" placeholder="XXXXXXXXXX" required />
				<button type="submit">Activate</button>
			</div>
		</div>
		<div class="clearfix"></div>
		<input type="hidden" name="task" value="license.activate" />
	</form>
</div>