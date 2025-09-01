<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

$order_dir = $this->state->get('cr.sortdir');
$order_by = $this->state->get('cr.sortby');

$filter_query = $this->state->get('cr.query');
$filter_userid = $this->state->get('cr.userid');

?>

<form action="<?=JRoute::_('index.php?option=com_comment&view=report',false)?>" method="post" name="adminForm" id="adminForm">
	<div id="j-filter" class="form-inline" style="margin-bottom:20px">
		<input type="text" name="query" placeholder="IP address" value="<?=$filter_query?>" />
		<input type="number" name="userid" placeholder="User ID" value="<?=$filter_userid?>" />
		<button type="submit" class="btn btn-success">Search</button>

		<div class="pull-right">
			<ul class="unstyled inline">
				<li><a href="<?=JRoute::_('index.php?option=com_comment&view=items',false)?>">Comments</a></li>
				<li><a href="<?=JRoute::_('index.php?option=com_comment&view=report',false)?>"><b>Reporting</b></a></li>
			</ul>
		</div>
	</div>
	<div id="j-main-container">
		<?php if (empty($this->reportings)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="itemList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">ID</th>
						<th class="center">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
						</th>
						<th width="15%">
							<?php echo JHtml::_('grid.sort' , JText::_("JCM_REASON") , 'report.reason_code' , $order_dir , $order_by) ?>
						</th>
						<th class="center">
							<?php echo JHtml::_('grid.sort' , JText::_("JCM_REPORTER") , 'report.created_by' , $order_dir , $order_by) ?>
						</th>
						<th><?php echo JText::_("JCM_COMMENT"); ?></th>
						<th class="center">
							<?php echo JHtml::_('grid.sort' , JText::_("JCM_CREATED_TIME") , 'report.created_time' , $order_dir , $order_by) ?>
						</th>
						<th class="center">
							<?php echo JHtml::_('grid.sort' , JText::_("JCM_IP_ADDRESS") , 'report.ip_address' , $order_dir , $order_by) ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->reportings as $i => $report) :
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="order nowrap center hidden-phone">
							<?=$report->id?>
						</td>
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $report->id); ?>
						</td>
						<td>
							<?=$report->reason?>
						</td>

						<td class="center">
							<a href="<?=$report->link2author?>" target="_blank"><?=$report->username?></a>
						</td>

						<td class="small">
							<div class="content-p line-clamp small">
								<p class="desc"><?=$report->comment?></p>
							</div>
							<a href="<?=$report->link2comment?>">...See more</a>
						</td>

						<td class="center small">
							<b><?=$report->created_time?></b>
						</td>
				
						<td class="center">
							<?=$report->ip_address?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif;?>

		<div class="center">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />

		<input type="hidden" name="filter_order" value="<?=$order_by?>" />
		<input type="hidden" name="filter_order_Dir" value="<?=$order_dir?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>

	<div class="clearfix"></div>
</form>