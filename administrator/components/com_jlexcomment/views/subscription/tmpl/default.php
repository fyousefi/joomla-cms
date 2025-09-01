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
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment&view=subscription'?>" method="post" name="adminForm" id="adminForm">
		<?php if(JE_JVERSION=="J3"):?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="Search" value="<?php echo $this->lists['query']; ?>">
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn"><span class="icon-search"></span></button>
				<button type="button" class="btn" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
		</div>
		<?php else:?>
			<div class="btn-toolbar" style="margin-bottom:10px">
				<div class="btn-group">
					<div class="input-group">
						<input type="text" name="filter_search" id="filter_search" class="form-control" placeholder="Search" value="<?php echo $this->lists['query']; ?>">
						<span class="input-group-append">
							<button type="submit" class="btn btn-primary"><span class="fas fa-search" aria-hidden="true"></span></button>
						</span>
					</div>
				</div>
			</div>
		<?php endif;?>

		<table class="table table-striped">
			<thead>
				<tr>
					<th width="15px" class="center">
						<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip" title="" onclick="Joomla.checkAll(this)" data-original-title="Check All">
					</th>
					<th class="nowrap">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_NAME") , 'sub.name' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_ENTRY") , 'sub.obj_id' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_CREATED_BY") , 'sub.created_by' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_CREATED_TIME") , 'sub.created_time' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_EMAIL_VERIFIED") , 'sub.email_confirmed' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo jtext::_('JCM_SUBSCRIBED') ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if(is_array($this->items) && count($this->items)): ?>
					<?php
						$k = 0 ;
						$i = 0 ;
						$states = array(
							1 => array(
								'subscription.disable',
								'JYES',
								'JYES',
								'JYES',
								false,
								'publish',
							),
							0 => array(
								'subscription.enable',
								'JNO',
								'JNO',
								'JNO',
								false,
								'unpublish',
							),
						);

						$states_2 = array(
							1 => array(
								'subscription.unpublish',
								'JYES',
								'JYES',
								'JYES',
								false,
								'publish',
							),
							0 => array(
								'subscription.publish',
								'JNO',
								'JNO',
								'JNO',
								false,
								'unpublish',
							),
						);
						foreach ($this->items as $k=>$item):
							$checked = JHTML::_('grid.id' , $i , $item->id);
							$verified = JHtml::_('jgrid.state', $states, $item->email_confirmed, $i);
							$published = JHtml::_('jgrid.state', $states_2, $item->published, $i);
					?>
					<tr class="row<?php echo $k ?>">
						<td class="center"><?php echo $checked; ?></td>

						<td class="has-context">
							<a href="<?php echo $item->url2edit; ?>"><?=$item->name?></a><br>
							<span><?=$item->email?></span>
						</td>

						<td class="has-context">
							<a href="<?php echo $item->url2object; ?>" target="_blank"><?php echo $item->object_name ?></a>
						</td>

						<td class="center">
							<?php if ($item->created_by > 0): ?>
							<a href="<?php echo $item->url2author ?>"><?php echo $item->author ?></a>
							<?php else: ?>
							<span>Guest</span>
							<?php endif; ?>
						</td>

						<td class="center">
							<?=JHtml::date($item->created_time,'Y-m-d H:i')?>
						</td>

						<td class="center text-center">
							<?php echo $verified; ?>
						</td>

						<td class="center text-center">
							<?php echo $published; ?>
						</td>
					</tr>
					<?php
						$k = 1- $k;
						$i++ ;
						endforeach; ?>
				<?php else: ?>
				<tr>
					<td class="center" colspan="8">
					No item.
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