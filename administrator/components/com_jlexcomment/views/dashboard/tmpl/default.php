<?php
/**
 * @package     JLex Comment
 * @version     2.0.0
 * @copyright   Copyright (C) 2013-2020 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;
?>
<div id="jlexcomment">
	<div id="jcm-db" class="jcm-box nospace">
		<div class="o">
			<div class="jcm-chart-container">
				<canvas id="jcm-chart"></canvas>
			</div>
			<div>
				<ul class="jcm-inline" style="margin-top:10px; text-align:center;">
					<li><a class="jcm-db-stic-reload jcm-but active" data-val="7"><?php echo JText::_("JCM_A_WEEK"); ?></a></li>
					<li><a class="jcm-db-stic-reload jcm-but" data-val="30"><?php echo JText::_("JCM_A_MONTH"); ?></a></li>
					<li><a class="jcm-db-stic-reload jcm-but" data-val="y"><?php echo JText::_("JCM_A_YEAR"); ?></a></li>
				</ul>
			</div>
		</div>

		<div class="v">
			<span class="_1"><?=jtext::_("JCM_YOUR_VERSION")?>: <strong id="jcm-version-local">Loading...</strong></span>
			<span class="_2">
				<span><?=jtext::_("JCM_LATEST_VERSION")?>: <strong id="jcm-version-latest">Loading...</strong></span>
			</span>
			<span class="_msg"></span>
		</div>

		<div class="m">
			<div class="jcm-tabs-box">
				<div class="jcm-tab-menu unsl" style="position:relative;">
					<ul class="jcm-inline list-menu" style="margin-left:10px">
						<li class="ie active" data-id="jcm-dt-comments">
							<span><?php echo JText::_("JCM_LATEST_COMMENTS"); ?></span>
						</li>
						<li class="ie" data-id="jcm-dt-topitems">
							<span><?php echo JText::_("JCM_TOP_ITEMS"); ?></span>
						</li>
						<li class="ie" data-id="jcm-dt-newitems">
							<span><?php echo JText::_("JCM_NEW_ITEMS"); ?></span>
						</li>
					</ul>

					<ul class="jcm-inline" style="position: absolute;right: 0;top: 0;height: 30px;line-height: 30px;">
						<li><a class="jcm-db-others-refresh jcm-but active" data-val="7"><?php echo JText::_("JCM_A_WEEK"); ?></a></li>
						<li><a class="jcm-db-others-refresh jcm-but" data-val="30"><?php echo JText::_("JCM_A_MONTH"); ?></a></li>
					</ul>
				</div>

				<div class="jcm-tab-content">
					<div class="jcm-tab-item active" id="jcm-dt-comments"></div>
					<div class="jcm-tab-item" id="jcm-dt-topitems"></div>
					<div class="jcm-tab-item" id="jcm-dt-newitems"></div>
				</div>
			</div>
		</div>
	</div>
</div>