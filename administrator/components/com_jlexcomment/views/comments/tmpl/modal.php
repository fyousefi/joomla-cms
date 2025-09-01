<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();
$function = JFactory::getApplication()->input->getString("function",null);
?>
<div id="jlexcomment">
	<form action="<?php echo JRoute::_('index.php?option=com_jlexcomment&view=comments',false) ?>" method="post" name="adminForm" id="adminForm">
		<?php if(JE_JVERSION=="J3"):?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-right hidden-phone">
				<?php echo $this->lists['sort']; ?>
			</div>
			<div class="filter-search btn-group pull-left">
				<input type="text" name="q" id="q" placeholder="Search" value="<?php echo $this->lists["query"] ?>">
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
						<th class="center"></th>
						<th width="45%">
							<?php echo JText::_("JCM_COMMENT"); ?>
						</th>
						<th width="40%">
							<?php echo JText::_("JCM_DETAIL"); ?>
						</th>
						<th class="nowrap center hidden-phone">
							<?php echo JHTML::_('grid.sort' , "ID" , 'cm.id' , $this->lists['order_Dir'] , $this->lists['order']) ?>
						</th>
					</tr>
				</thead>

				<tbody>
					<?php if(is_array($this->comments) && count($this->comments)): ?>
						<?php
							$k = 0 ;
							$i = 0 ;
							$states = array(
									1 => array(
										'comments.unpublish',
										'JCM_PUBLISHED',
										'','',
										false,
										'publish',
									),
									0 => array(
										'comments.publish',
										'JCM_UNPUBLISHED',
										'','',
										false,
										'unpublish',
									),
								);

							foreach ($this->comments as $k=>$item):
								$published = JHtml::_('jgrid.state', $states, $item->published, $i);
								$js = "";
								if(!empty($function))
								{
									if(empty($item->comment))
									{
										$title = JText::_("JCM_STICKER");
									} else {
										$title = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
						                    return '<span class="jcm-mention-html" data-id="'.$matches[1].'">@'.$matches[2].'</span>';
						                }, $item->comment);

						                $title = substr (strip_tags(preg_replace("/\r|\n/", "", $title)), 0, 20) . "...";
									}

									$js = "if (window.parent) window.parent." . $function . "('{$item->id}', '{$title}');";
								}
						?>
						<tr class="row<?php echo $k ?>">
							<td class="center">
								<a class="btn btn-primary" href="javascript:void(0)" 
									onclick="<?=$js?>">Select</a>
							</td>

							<td class="has-context hasComment">
								<?php
									$this->comment = $item;
									echo $this->loadTemplate('comment');
								?>
							</td>

							<td class="has-context jcm-text-small">
								<ul class="jcm-meta">
									<li>
										<span class="n"><?=jtext::_("JCM_ENTRY")?></span>
										<span class="c"><?=$item->object_name?></span>
									</li>

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

									<li>
										<span class="n"><?=jtext::_("JCM_PUBLISHED")?></span>
										<span class="c"><?=$published?></span>
									</li>

									<li>
										<span class="n"><?=jtext::_("JCM_CREATED_TIME")?></span>
										<span class="c"><?=JHtml::date($item->created_time,'Y-m-d H:i')?></span>
									</li>
								</ul>
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
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="center" style="margin-top:15px">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>

		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order'] ; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir'] ; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="layout" value="modal" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="function" value="<?php echo $function; ?>" />
	</form>
</div>