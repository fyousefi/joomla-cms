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
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment&view=mailq'?>" method="post" name="adminForm" id="adminForm">
		<?php if($this->config->get("nof_user",0)==0): ?>
		<div class="alert alert-danger">
			The mailing progress is paused. Please enable <b><a href="<?php echo JUri::base(true) . '/index.php?option=com_jlexcomment&view=settings'; ?>">User Notification</a></b> to run it.
		</div>
		<?php endif; ?>

		<table class="table table-striped">
			<thead>
				<tr>
					<th width="15px" class="center">
						<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip" title="" onclick="Joomla.checkAll(this)" data-original-title="Check All">
					</th>
					<th class="nowrap">
						<?php echo JHTML::_('grid.sort' , "Name" , 'rep_name' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap">
						Detail
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , "Email" , 'rep_email' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , "Created time" , 'nof.created_time' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , "Status" , 'nof.sent' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if(is_array($this->items) && count($this->items)): ?>
					<?php
						$k = 0 ;
						$i = 0 ;
						foreach ($this->items as $k=>$item):
							$checked = JHTML::_('grid.id' , $i , $item->id);
					?>
					<tr class="row<?php echo $k ?>">
						<td class="center"><?php echo $checked; ?></td>

						<td class="has-context">
							<?php echo $item->rep_name ?>
						</td>

						<td class="has-context">
							<?php echo $item->caption ?>
							<span> - </span>
							<a href="<?php echo $item->url ?>" target="_blank">See</a>
						</td>

						<td class="has-context">
							<a href="mailto:<?php echo $item->rep_email; ?>"><?php echo $item->rep_email; ?></a>
						</td>

						<td class="center">
							<?=JHtml::date($item->created_time,'Y-m-d H:i')?>
						</td>

						<td class="center">
							<?php echo $item->sent==1 ? '<b style="color:green">Sent</b>' : '<span style="color:#999">Pending</span>'; ?>
						</td>
					</tr>
					<?php
						$k = 1- $k;
						$i++ ;
						endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="6" class="center">
							<?php echo JText::_("JCM_NO_ROW_FOUND"); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<div class="center" style="padding-top:15px">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>

		<div class="clearfix"></div>

		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order'] ; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir'] ; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
	</form>
</div>