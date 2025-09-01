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
	<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&view=integration',false) ?>" method="post"
	name="adminForm" id="adminForm" enctype="multipart/form-data">
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="20%"><?php echo JText::_('JCM_ADDON') ?></th>
					<th width="10%"><?php echo JText::_('JCM_THUMB') ?></th>
					<th width="30%"><?php echo JText::_('JCM_DESCRIPTION') ?></th>
					<th width="10%"><?php echo JText::_('JCM_LATEST_VERSION') ?></th>
					<th width="20%"><?php echo JText::_('JCM_ACTION') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!$this->items): ?>
				<tr>
					<td colspan="5"><?php echo JText::_('JCM_NO_ADDON_FOUND') ?></td>
				</tr>
				<?php else: ?>
					<?php foreach ($this->items as $item): ?>
					<tr>
						<td><?php echo $item->title ?></td>
						<td>
							<?php if (isset($item->img)): ?>
							<img src="<?php echo $item->img ?>" style="max-width:48px;height:auto" />
							<?php endif; ?>
						</td>
						<td><?php echo $item->description ?></td>
						<td><?php echo $item->version ?></td>
						<td>
							<?php if ($item->installed): ?>
								<a href="<?php echo $item->url_manager ?>" class="btn btn-success btn-small"><?php echo JText::_("JCM_MANAGE"); ?></a>
								<?php if($item->update): ?>
									<button type="button" class="btn-app-install btn btn-danger btn-small" data-id="<?php echo $item->id; ?>"><?php echo JText::_("JCM_UPDATE"); ?></button>
								<?php endif;?>
							<?php else: ?>
								<?php if($item->direct): ?>
								<button type="button" class="btn-app-install btn btn-primary btn-small" data-id="<?php echo $item->id; ?>"><?php echo JText::_("JCM_INSTALL"); ?></button>
								<?php else: ?>
								<a href="<?php echo $item->url ?>" target="_blank" class="btn btn-danger btn-small"><?php echo JText::_("JCM_LEARN_MORE"); ?></button>
								<?php endif; ?>
							<?php endif; ?>

							<?php if(isset($item->doc)): ?>
								<a href="<?php echo $item->doc ?>" target="_blank" class="btn btn-danger btn-small"><?php echo JText::_("JCM_DOC"); ?></button>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<div class="clearfix"></div>

		<input type="hidden" name="id" value="" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
	</form>
</div>