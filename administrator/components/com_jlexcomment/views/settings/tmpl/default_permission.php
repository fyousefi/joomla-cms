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

<?php if (count($this->groups)): ?>
	<div id="permissions-sliders" class="tabbable tabs-left">
		<ul class="nav nav-tabs">
			<?php foreach ($this->groups as $group):
				$active = ($group->value == 1) ? "active" : "";
				?>

				<li class="<?=$active?>">
					<a href="#permission-<?php echo $group->value; ?>" class="<?=$active?>" data-toggle="tab">
						<?php echo str_repeat('<span class="level">&ndash; ', $curLevel = $group->level) . $group->text; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="tab-content">
			<?php foreach ($this->groups as $group):
				$active = ($group->value == 1) ? "active" : "";
				$form = $this->permissionForms[$group->value];
				?>
				<div class="tab-pane <?php echo $active; ?>" id="permission-<?php echo $group->value; ?>">
					<div class="<?=JE_JVERSION=="J3"?"row-fluid":"row"?>">
						<div class="span6 col-md-6">
							<fieldset class="form-horizontal">
								<legend><?php echo JText::_('JCM_RIGHTS_POST'); ?></legend>
								<?php foreach ($form->getFieldset('post') as $field) : ?>
									<div class="control-group">
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</fieldset>
						</div>

						<div class="span6 col-md-6">
							<fieldset class="form-horizontal">
								<legend><?php echo JText::_('JCM_RIGHTS_ADMINISTRATION'); ?></legend>
								<?php foreach ($form->getFieldset('administration') as $field) : ?>
									<div class="control-group">
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</fieldset>

						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>

