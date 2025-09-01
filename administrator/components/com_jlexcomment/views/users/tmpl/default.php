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
	<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&view=users',false) ?>" method="post"
	name="adminForm" id="adminForm" enctype="multipart/form-data">
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
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_NAME") , 'u.name' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JText::_("JCM_THUMBNAIL"); ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_COMMENT_COUNT") , 'ucount.count_cm' , $this->lists['order_Dir'] , $this->lists['order']) ?>
					</th>
					<th class="nowrap center">
						<?php echo JText::_("JCM_MORE_INFO"); ?>
					</th>
					<th class="nowrap center">
						<?php echo JHTML::_('grid.sort' , JText::_("JCM_MN_BLACKLIST") , 'bls.id' , $this->lists['order_Dir'] , $this->lists['order']) ?>
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
							<a href="<?php echo $item->url2edit; ?>"><?php echo $item->name; ?></a>
						</td>

						<td class="has-context center">
							<?php
							if (!empty($item->thumbnail))
							{
								echo '<img class="jcm-thumb-avatar" src="'.$item->thumbnail.'" />';
							}
							?>
							<button type="button" data-uid="<?=$item->id?>" class="jcm-change-thumb btn btn-small btn-primary btn-sm"><?=jtext::_(empty($item->thumbnail)?'JCM_ADD_THUMB':'JCM_CHANGE_THUMB')?></button>
						</td>

						<td class="center">
							<b><?php echo $item->count_cm ; ?></b>
						</td>

						<td class="center">
							<?php
								if ($item->auth=='fb' || $item->auth=='google' || $item->auth=='twitter'):
								$textSocl = array (
									'fb'=>'Facebook',
									'google'=>'Google',
									'twitter'=>'Twitter'
								);
							?>
								<span>Register by:</span>
								<a href="<?php echo $item->auth_url; ?>" target="_blank"><?php echo $textSocl[$item->auth]; ?></a>
							<?php else: ?>
								No information
							<?php endif; ?>
						</td>

						<td class="center">
							<?php echo $item->blocked==1 ? '<span style="color:#942a25">'.JText::_("JYES").'</span>' : '<span style="color:#378137">'.JText::_("JNO").'</span>'; ?>
						</td>
					</tr>
					<?php
						$k = 1- $k;
						$i++ ;
						endforeach; ?>
				<?php else: ?>
				<tr>
					<td class="center" colspan="5">
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