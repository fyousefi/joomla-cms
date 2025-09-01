<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();
$function = JFactory::getApplication()->input->getString ("function","");
?>
<div id="jlexcomment">
	<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&view=sticker&layout=modal',false) ?>" method="post" name="adminForm" id="adminForm">
		<div class="btn-group pull-right hidden-phone">
			<span><?php echo $this->lists['publish'] ; ?></span>
			<span style="margin-left:7px"><?php echo $this->lists['group'] ; ?></span>
		</div>
		<!--
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="Search" value="<?php echo $this->lists['query']; ?>" class="hasTooltip" title="" data-original-title="Search item">
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="" data-original-title="Search"><span class="icon-search"></span></button>
				<button type="button" class="btn hasTooltip" title="" onclick="document.getElementById('filter_search').value='';this.form.submit();" data-original-title="Clear"><span class="icon-remove"></span></button>
			</div>
		</div>
		-->
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="15px" class="center">
						<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip" title="" onclick="Joomla.checkAll(this)" data-original-title="Check All">
					</th>
					<th class="nowrap">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_CAPTION") , 's.caption' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JText::_("JCM_PREVIEW"); ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_CREATED_BY") , 's.created_by' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_CREATED_TIME") , 's.created_time' , $this->lists['order_Dir'] , $this->lists['order']) ?>
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
							$published = JHTML::_('grid.published' , $item , $i , 'tick.png', 'publish_x.png', 'sticker.' );
							$js = "";
							if (! empty($function))
							{
								$js = "if (window.parent) window.parent." . $function . "('{$item->id}', '{$item->preview}');";
							}
					?>
					<tr class="row<?php echo $k ?>">
						<td class="center"><?php echo $checked; ?></td>

						<td class="has-context">
							<a href="javascript:void(0)" onclick="<?php echo $js; ?>"><?php echo $item->caption ?></a>
						</td>

						<td class="center has-context">
							<img src="<?php echo $item->preview ?>" style="max-height:100px;max-width:100px;" />
						</td>

						<td class="center">
							<?php if ($item->created_by > 0): ?>
							<a href="<?php echo $item->url2author ?>"><?php echo $item->author ?></a>
							<?php else: ?>
							<span>Guest</span>
							<?php endif; ?>
						</td>

						<td class="center">
							<?php echo $item->created_time; ?>
						</td>
					</tr>
					<?php
						$k = 1- $k;
						$i++ ;
						endforeach; ?>
				<?php else: ?>
				<tr>
					<td class="center" colspan="7">
					No item.
					</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<div class="center" style="padding-top:15px">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>

		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order'] ; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir'] ; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="function" value="<?php echo $function; ?>" />
	</form>
</div>