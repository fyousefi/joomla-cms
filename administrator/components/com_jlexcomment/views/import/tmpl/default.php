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
		<div class="row-fluid row">
			<div class="span6 col-md-6">
				<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&task=import.upload',false) ?>"
				 method="post" enctype="multipart/form-data" class="jcm-form-import">
					<h3><?php echo JText::_("JCM_SELECT_FILE_TO_IMPORT"); ?></h3>
					<input type="file" name="file" required />
					<p style="padding-top:15px">
						<button class="btn btn-primary"><?php echo JText::_("JCM_UPLOAD_A_FILE"); ?></button>
					</p>
				</form>
			</div>

			<div class="span6 col-md-6">
				<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&task=import.migrator',false) ?>"
				 method="post" class="jcm-form-import">
					<h3><?php echo JText::_("JCM_IMPORT_FROM_OTHER_EXTENSION"); ?></h3>
					<?php echo $this->migrators; ?>
					<div style="padding-top:15px">
						<button id="import-btn" type="button" class="btn btn-primary"><?php echo JText::_("JCM_IMPORT");?></button>
						<div class="progress" style="display:none">
							<div class="progress progress-success progress-striped active">
								<div class="bar" style="width:0%;"><span class="bar-value">0</span>% <?php echo JText::_("JCM_COMPLETED"); ?></div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div class="clearfix"></div>
	</div>
	
	<div class="jcm-overlay loading">
	</div>
</div>