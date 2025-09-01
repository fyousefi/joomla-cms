<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;
?>
<div id="jlexcomment">
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment&view=sync'?>" method="post" name="adminForm" id="adminForm">
		<div id="syncIndex">
			<p class="msg"><?php echo jtext::_('JCM_SYNC_INDEX_WARNING') ?></p>
			<div class="jpercent"><span>--</span>/100%</div>
			<div class="jbar">
				<div class="jprocess" style="width:0"></div>
			</div>
		</div>

		<table class="table table-striped">
			<thead>
				<tr>
					<th width="40">#</th>
					<th width="25%"><?php echo JHTML::_('grid.sort' , jtext::_('JCM_OBJECT') , 's.object' , $this->lists['order_dir'] , $this->lists['order']) ?></th>
					<th><?php echo JHTML::_('grid.sort' , jtext::_('JCM_CREATED_TIME') , 's.created_time' , $this->lists['order_dir'] , $this->lists['order']) ?></th>
					<th class="center" width="15%"><?php echo JHTML::_('grid.sort' , jtext::_('JCM_PUBLISHED') , 's.published' , $this->lists['order_dir'] , $this->lists['order']) ?></th>
					<th><?php echo jtext::_('JCM_SYNC_LOG') ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if(is_array($this->items) && count($this->items)) : ?>
				<?php
					$k=0; $i=0;
					$states = array(
							1 => array(
								'sync.unpublish',
								'JYES',
								'JYES','JYES',
								false,
								'publish',
							),
							0 => array(
								'sync.publish',
								'JNO',
								'JNO','JNO',
								false,
								'unpublish',
							),
						);
					foreach($this->items as $item):
						$checked = JHtml::_('grid.id' , $i , $item->id);
						$published = JHtml::_('jgrid.state', $states, $item->published, $i);
				?>
				<tr>
					<td align="center"><?php echo $checked ?></td>
					<td>
						<a href="<?php echo $item->url2edit ?>"><?php echo $item->object ?></a>
					</td>
					<td><?php echo JHtml::date($item->created_time, 'Y-m-d H:i:s') ?></td>
					<td class="center"><?php echo $published ?></td>
					<td align="center"><?php echo $item->latest_log ?></td>
				</tr>
				<?php
					$k=1-$k;
					$i++ ;
					endforeach;
				?>
			<?php else: ?>
			<tr>
				<td colspan="5" class="center">No item.</td>
			</tr>
			<?php endif; ?>
			</tbody>
		</table>

		<div class="center" style="margin-top:15px"><?php echo $this->pagination->getListFooter(); ?></div>

		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order'] ; ?>">
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_dir'] ; ?>">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
	</form>
</div>