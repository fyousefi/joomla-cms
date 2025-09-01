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
	<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&view=comments',false) ?>" method="post" name="adminForm" id="adminForm">
		<?php if(JE_JVERSION=="J3"):?>
		<div id="j-main-container">
			<div id="filter-bar" class="btn-toolbar">
				<div class="btn-group pull-right hidden-phone">
					<?php echo $this->lists['sort']; ?>
				</div>
				<div class="filter-search btn-group pull-left">
					<input type="text" name="q" id="q" placeholder="<?php echo jtext::_('JCM_SEARCH_BY_FACTORS'); ?>" value="<?php echo $this->lists["query"] ?>">
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn hasTooltip" title="Search">
						<span class="icon-search"></span>
					</button>
					<button type="button" class="btn hasTooltip" title="Clear" onclick="document.getElementById('q').value='';this.form.submit();">
						<span class="icon-remove"></span>
					</button>
				</div>
			</div>
		<?php else:?>
			<div class="btn-toolbar" style="margin-bottom:10px">
				<div class="btn-group">
					<div class="input-group">
						<input type="text" name="q" id="q" class="form-control" placeholder="Search" value="<?php echo $this->lists['query']; ?>">
						<span class="input-group-append">
							<button type="submit" class="btn btn-primary"><span class="fas fa-search" aria-hidden="true"></span></button>
						</span>
					</div>
					<div class="ordering-select" style="margin-left:8px">
					<?php echo $this->lists['sort'] ; ?>
					</div>
				</div>
			</div>
		<?php endif;?>
			<div class="jcm-table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="15px" class="center">
								<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip" title="" onclick="Joomla.checkAll(this)" data-original-title="Check All">
							</th>
							<th width="30%">
								<?php echo JText::_("JCM_COMMENT"); ?>
							</th>
							<th class="nowrap" width="15%">
								<?php echo JHTML::_('grid.sort' , JText::_("JCM_ENTRY") , 'obj.title' , $this->lists['order_Dir'] , $this->lists['order']) ?>
							</th>
							<th width="20%">
								<?php echo JText::_("JCM_DETAIL"); ?>
							</th>
							<th class="nowrap center">
								<?php echo JHTML::_('grid.sort' , JText::_("JCM_CREATED_TIME") , 'cm.created_time' , $this->lists['order_Dir'] , $this->lists['order']) ?>
							</th>
							<th class="nowrap center hidden-phone">
								<?php echo JHTML::_('grid.sort' , JText::_("JCM_PUBLISHED") , 'cm.published' , $this->lists['order_Dir'] , $this->lists['order']) ?>
							</th>
							<th class="nowrap center hidden-phone">
								<?php echo JHTML::_('grid.sort' , JText::_("JCM_FEATURED") , 'cm.featured' , $this->lists['order_Dir'] , $this->lists['order']) ?>
							</th>
							<th class="nowrap center hidden-phone">
								<?php echo JHTML::_('grid.sort' , "ID" , 'cm.id' , $this->lists['order_Dir'] , $this->lists['order']) ?>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php if(is_array($this->comments) && count ($this->comments)): ?>
							<?php
								$k = 0 ;
								$i = 0 ;
								$states = [
									1 => [
										'comments.unfeature',
										'JCM_FEATURED',
										'JCM_REMOVE_FEATURED_FLAG',
										'JCM_FEATURED',
										false,
										'publish',
									],
									0 => [
										'comments.feature',
										'JCM_NOT_FEATURED',
										'JCM_FLAG_AS_FEATURED',
										'JCM_NOT_FEATURED',
										false,
										'unpublish',
									],
								];

								$pub_states = [
									1 => [
										'comments.unpublish',
										'JCM_PUBLISHED',
										'','',
										false,
										'publish',
									],
									0 => [
										'comments.publish',
										'JCM_UNPUBLISHED',
										'','',
										false,
										'unpublish',
									],
								];

								foreach ($this->comments as $k=>$item):
									$checked = JHTML::_('grid.id' , $i , $item->id);
									$published = JHtml::_('jgrid.state', $pub_states, $item->published, $i);
									
									$featured = JHtml::_('jgrid.state', $states, $item->featured, $i);
							?>
							<tr class="row<?=$k?>">
								<td class="center"><?=$checked?></td>

								<td class="has-context hasComment">
									<?php
										$this->comment = $item;
										echo $this->loadTemplate('comment');
									?>
								</td>

								<td class="has-context">
									<a href="<?=$item->objectUrl?>"><?=$item->object_name?></a>
								</td>

								<td class="has-context jcm-text-small">
									<ul class="jcm-meta">
										<li>
											<span class="n"><?=jtext::_("JCM_AUTHOR")?></span>
											<span class="c">
												<?php if($item->created_by>0):?>
													<a href="#"><?=$item->author_name?></a>
												<?php else: ?>
													<strong><?php echo $item->author_name ?></strong>
													<?php if(!empty($item->guest_email)):?>
														(<?=$item->guest_email?>)
													<?php endif;?>
												<?php endif; ?>
											</span>
										</li>

										<?php if ($item->parent_id>0 && $item->parent_comment): ?>
										<li>
											<span class="n"><?=jtext::_("JCM_REPLY_FOR_CM")?></span>
											<span class="c">
												<a href="<?=$item->parent_comment_url?>"><?=$item->parent_comment?></a>
											</span>
										</li>
										<?php endif; ?>

										<?php if ($item->child_count>0): ?>
										<li>
											<span class="n"><?=jtext::_("JCM_CHILD_CM_COUNT")?></span>
											<span class="c">
												<a href="<?=$item->childUrl?>"><?=$item->child_count?></a>
											</span>
										</li>
										<?php endif; ?>

										<?php if($this->config->def('ip_address',1)==1): ?>
										<li>
											<span class="n"><?=jtext::_("JCM_IP_ADDRESS")?></span>
											<span class="c"><?=$item->ip_address?></span>
										</li>
										<?php endif;?>

										<?php if($item->report_count>0): ?>
										<li>
											<span class="n"><?=jtext::_("JCM_REPORT_COUNT")?></span>
											<span class="c"><?=$item->report_count?></span>
										</li>
										<?php endif; ?>

										<li>
											<span class="n"><?=jtext::_($item->reaction?"JCM_REACTION":"JCM_POINT")?></span>
											<span class="c">
												<?=$item->point?>
												<?php if(!$item->reaction):?>
												 (<span class="point_up">+<?=$item->up_point?></span>/<span class="point_down">-<?=$item->down_point?></span>)
												<?php endif; ?>
											</span>
										</li>
									</ul>
								</td>

								<td class="center">
									<?=JHtml::date($item->created_time,'Y-m-d H:i')?>
								</td>

								<td class="center hidden-phone text-center">
									<?=$published?>
								</td>

								<td class="center hidden-phone text-center">
									<?=$featured?>
								</td>

								<td class="center hidden-phone">
									<?=$item->id?>
								</td>
							</tr>
							<?php
								$k = 1- $k;
								$i++ ;
								endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="8" class="center">
									<?php echo JText::_("JCM_NO_ROW_FOUND"); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

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

<?php
$js="
(function($){
	$(document).ready(function(){
		var abox=function(msg){
			if(!$('#jcmAlertBox').length){
				$('body').append('<div id=\"jcmAlertBox\"><div class=\"inner\"></div></div>');
			}

			$('#jcmAlertBox .inner').empty().append('<span>'+msg+'</span>');
		};

		Joomla.submitbutton=(function(task){
			if(task=='comments.recalculate'){
				var r=confirm('".jtext::_("JCM_CONFIRM_TO_EMPTY_TEMP_FILES")."');
				if(!r) return;

				abox('".jtext::_('JCM_PLEASE_WAIT')."');

				$.get('".JUri::base(true)."/index.php?option=com_jlexcomment&task=media.clean', function(d){
					if(d.status==200){
						abox('".jtext::_('JCM_COUNT_TEMP_FILE_DELETED')."'.replace('%s', '<strong>'+d.count+'</strong>'));
					}

					setTimeout(function(){
						$('#jcmAlertBox').fadeOut('fast', function(){
							$(this).remove();
						});
					}, 3000);
				}, 'json');
				return;
			}

			Joomla.submitform(task);
		});
	});
})(jQuery);
";

JFactory::getDocument()->addScriptDeclaration($js);
?>