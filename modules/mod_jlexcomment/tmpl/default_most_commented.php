<?php
/**
 * @version		1.0.0
 * @package		JLex Comment
 * @subpackage	Module JLex Comment
 * @copyright	Copyright (C) 2013-2016 JLexArt. All rights reserved.
 * @license		GNU/GPL 2 or later
 * @author		JLexArt
 */
defined ( "_JEXEC" ) or die;
?>

<div class="jcm-mod-entries-box">
	<ul class="jcm-mod-entries">
	<?php foreach ($entries as $k=>$entry): ?>
		<li>
			<span class="_index"><?php echo $entry->index; ?></span>
			<a href="<?php echo $entry->url; ?>">
				<span class="_title"><?php echo $entry->title; ?></span>
				<?php if ($params->def("mc_count_cm",1)==1): ?>
				<span class="_count"><?php echo $entry->cm_count; ?></span>
				<?php endif; ?>
			</a>
		</li>
	<?php endforeach; ?>
	</ul>

<?php
if ($params->def("pagination",0)==1)
{
	echo $helper->getPagination()->getPagesLinks();
}
?>
</div>