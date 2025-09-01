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
	<form action="<?=JUri::base(true).'/index.php?option=com_jlexcomment&view=stickergroup'?>" method="post" name="adminForm" id="adminForm">
		<?php if(JE_JVERSION=="J3"):?>
		<div id="j-main-container">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="Search" value="<?php echo $this->lists['query']; ?>">
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn"><span class="icon-search"></span></button>
					<button type="button" class="btn" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
				</div>
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
						<?php echo JHtml::_('grid.sort' , JText::_("JCM_NAME") , 's.name' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap">
						<?php echo JText::_("JCM_DESCRIPTION"); ?>
					</th>
					</th>
					<th class="nowrap center">
						<?php echo JHtml::_('grid.sort' , JText::_("JCM_PUBLISHED") , 's.published' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JHtml::_('grid.sort' , JText::_("JCM_ITEM_COUNT") , 'gc.count_sticker' , $this->lists['order_Dir'] , $this->lists['order']) ?>
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
								'stickergroup.unpublish',
								'JYES',
								'JYES','JYES',
								false,
								'publish',
							),
							0 => array(
								'stickergroup.publish',
								'JNO',
								'JNO','JNO',
								false,
								'unpublish',
							),
						);
						foreach ($this->items as $k=>$item):
							$checked = JHTML::_('grid.id' , $i , $item->id);
							$published = JHtml::_('jgrid.state', $states, $item->published, $i);
					?>
					<tr class="row<?php echo $k ?>">
						<td class="center"><?php echo $checked; ?></td>

						<td class="has-context">
							<a href="<?php echo $item->url2edit ?>"><?php echo $item->name ?></a>
						</td>

						<td class="has-context">
							<?=empty($item->description)?'n/a':$item->description?>
						</td>

						<td class="center">
							<?php echo $published; ?>
						</td>

						<td class="center">
							<span class="badge badge-success"><?php echo $item->count_sticker ?></span>
						</td>
					</tr>
					<?php
						$k = 1- $k;
						$i++ ;
						endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="5"><?=jtext::_("JCM_NO_ITEM")?></td>
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