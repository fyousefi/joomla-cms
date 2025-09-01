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
	<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&view=items',false) ?>" method="post" name="adminForm" id="adminForm">
		<?php if(JE_JVERSION=="J3"):?>
		<div id="j-main-container">
			<div id="filter-bar" class="btn-toolbar">
				<div class="btn-group pull-right hidden-phone">
					<?=$this->lists["comps"]?>
				</div>
				<div class="filter-search btn-group pull-left">
					<input type="text" name="q" id="filter_search" placeholder="Search" value="<?=$this->lists["q"]?>">
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn hasTooltip" title="" data-original-title="Search"><span class="icon-search"></span></button>
					<button type="button" class="btn hasTooltip" title="" onclick="document.getElementById('filter_search').value='';this.form.submit();" data-original-title="Clear"><span class="icon-remove"></span></button>
				</div>
			</div>
		<?php else:?>
			<div class="btn-toolbar" style="margin-bottom:10px">
				<div class="btn-group">
					<div class="input-group">
						<input type="text" name="q" id="filter_search" class="form-control" placeholder="Search" value="<?=$this->lists["q"]?>">
						<span class="input-group-append">
							<button type="submit" class="btn btn-primary"><span class="fas fa-search" aria-hidden="true"></span></button>
						</span>
					</div>
					<div class="ordering-select" style="margin-left:8px">
					<?php echo $this->lists['comps'] ; ?>
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
							<?php echo JHTML::_('grid.sort' , JText::_("JCM_TITLE") , 'obj.title' , $this->lists['order_Dir'] , $this->lists['order']) ?>
						</th>
						<th class="nowrap center">
							<?php echo JHTML::_('grid.sort' , JText::_("JCM_CREATED_BY") , 'obj.created_by' , $this->lists['order_Dir'] , $this->lists['order']) ?>
						</th>
						<th class="nowrap">
							<?php echo JHTML::_('grid.sort' , JText::_("JCM_COMMENT_COUNT") , 'obj.cm_count' , $this->lists['order_Dir'] , $this->lists['order']) ?>
						</th>
						<th class="nowrap center">
							<?php echo JHTML::_('grid.sort' , "URL" , 'obj.url' , $this->lists['order_Dir'] , $this->lists['order']) ?>
						</th>
						<th class="nowrap center">
							<?php echo JHTML::_('grid.sort' , JText::_("JCM_PUBLISHED") , 'obj.published' , $this->lists['order_Dir'] , $this->lists['order']) ?>
						</th>
						<th class="nowrap center">
							<?php echo JText::_("JCM_LIVE"); ?>
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
									'items.unpublish',
									'JYES',
									'JYES','JYES',
									false,
									'publish',
								),
								0 => array(
									'items.publish',
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
								<a href="<?php echo $item->url2edit ?>"><?php echo $item->title ?></a>
							</td>

							<td class="center">
								<?php echo $item->author ?>
							</td>

							<td>
								<a href="<?php echo $item->url2cms ?>">
									<span><?php echo JText::_("JCM_COMMENTS"); ?>:</span> <b><?php echo $item->cm_i_count ?></b>
									<br/>
									<span><?php echo JText::_("JCM_REPLIES"); ?>:</span> <b><?php echo ($item->cm_count-$item->cm_i_count) ?></b>
								</a>
							</td>

							<td>
								<a href="<?php echo $item->url_preview ?>" target="_blank"><?php echo $item->url ?></a>
							</td>

							<td class="center">
								<?php echo $published; ?>
							</td>

							<td class="center">
								<div>
									<b><?php echo $item->params->def ('live',0)==1 ? "Enabled" : "Disabled"; ?></b>
								</div>
								<?php if ($item->params->def ('live',0)==1): ?>
									<small>Auto refresh: <?php echo $item->params->def ('live_refresh',5) ?> min</small>
								<?php endif; ?>
							</td>
						</tr>
						<?php
							$k = 1- $k;
							$i++ ;
							endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="7" class="center">
								<?php echo JText::_("JCM_NO_ROW_FOUND"); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<div class="center" style="padding-top:15px">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
		<?php if(JE_JVERSION=="J3"):?>
		</div>
		<?php endif;?>

		<div class="clearfix"></div>

		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order'] ; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir'] ; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
	</form>
</div>