<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined( '_JEXEC' ) or die ();
$rtl = $this->config->get('rtl',0)==1;
?>

<ul class="jcm-top jcm-inline">
	<li>
		<span>
			<?php echo $this->totalComment>1 ? JText::sprintf("JCM_TOTAL_COMMENTS",$this->totalComment) : JText::_("JCM_COMMENT") ?>
		</span>
	</li>

	<?php if($this->config->get('cm_rule',0)==1):?>
	<li style="border-<?php echo $rtl?'right':'left' ?>: 1px solid #ddd;padding-<?php echo $rtl?'right':'left' ?>:6px">
		<a class="jcm-terms" href="#"><?=jtext::_("JCM_TERMS")?></a>
	</li>	
	<?php endif;?>

	<?php if($this->config->get('rss',1)==1):?>
	<li style="border-<?php echo $rtl?'right':'left' ?>: 1px solid #ddd;padding-<?php echo $rtl?'right':'left' ?>:6px">
		<a href="<?php echo $this->data->url2rss; ?>">RSS</a>
	</li>
	<?php endif; ?>

	<li class="jcm-pull-right" style="position:relative;z-index:20">
	<?php if ($this->user->guest): ?>
		<?php if (
			$this->config->get("oauth_fb",0)==1 ||
			$this->config->get("oauth_tw",0)==1 ||
			$this->config->get("oauth_gg",0)==1 ||
			$this->config->get("oauth_vk",0)==1
		): ?>
		<div class="jcm-dropdown onRight">	
			<a href="#" class="jcm-dropdown-toggle">
				<span><?php echo JText::_("JCM_LOGIN"); ?></span>
			</a>
			<ul class="jcm-dropdown-menu">
				<?php if($this->config->get("login",1)):?>
				<li>
					<a href="#" class="_dx jcm-u-login">
						<i class="fas fa-user"></i>
						<span><?php echo JText::_("JCM_THIS_SITE"); ?></span>
					</a>
				</li>
				<?php endif;?>

				<?php if ($this->config->get("oauth_fb",0)==1): ?>
				<li>
					<a href="#" class="_dx jcm-u-oauth" data-action="facebook">
						<i class="fab fa-facebook-square"></i>
						<span><?php echo JText::_("JCM_FACEBOOK"); ?></span>
					</a>
				</li>
				<?php endif; ?>

				<?php if ($this->config->get("oauth_gg",0)==1): ?>
				<li>
					<a href="#" class="_dx jcm-u-oauth" data-action="google">
					 	<i class="fab fa-google"></i>
						<span><?php echo JText::_("JCM_GOOGLE") ; ?></span>
					</a>
				</li>
				<?php endif; ?>

				<?php if ($this->config->get("oauth_tw",0)==1): ?>
				<li>
					<a href="#" class="_dx jcm-u-oauth" data-action="twitter">
						<i class="fab fa-twitter"></i>
						<span><?php echo JText::_("JCM_TWITTER") ; ?></span>
					</a>
				</li>
				<?php endif; ?>

				<?php if ($this->config->get("oauth_vk",0)==1): ?>
				<li>
					<a href="#" class="_dx jcm-u-oauth" data-action="vk">
						<i class="fab fa-vk"></i>
						<span><?php echo JText::_("JCM_VK") ; ?></span>
					</a>
				</li>
				<?php endif; ?>
			</ul>
		</div>
		<?php elseif($this->config->get("login",1)): ?>
		<a href="#" class="jcm-u-login">
			<span><?php echo JText::_("JCM_LOGIN"); ?></span>
		</a>
		<?php endif; ?>
	<?php else: ?>
		<div class="jcm-dropdown onRight">
			<a href="#" class="jcm-dropdown-toggle">
				<span>
					<span class="jcm-count-nof"></span>
					<span><?php echo JText::sprintf("JCM_HELLO_USER",$this->user->name); ?></span></span>
			</a>
			<ul class="jcm-dropdown-menu">
				<li><a href="#" class="_dx" id="jcm-notifications"><?php echo JText::_("JCM_NOTIFICATIONS"); ?> <span class="jcm-count-nof"></span></a></li>
				<li><a class="_dx" href="<?php echo JRoute::_("index.php?option=com_users&view=profile",false); ?>"><?php echo JText::_("JCM_SETTING"); ?></a></li>

				<?php if($this->config->def("profile_3rd","jlexcomment")=="jlexcomment"): ?>
				<li><a href="#" class="_dx" id="jcm-thumb-edit"><?php echo JText::_("JCM_THUMB_EDITING"); ?></a></li>
				<?php endif; ?>

				<li><a href="#" class="_dx jcm-user-cm" data-id="<?php echo $this->user->id; ?>"><?php echo JText::_("JCM_COMMENTS"); ?></a></li>
				<li><a href="#" class="_dx" id="jcm-u-logout"><?php echo JText::_("JCM_LOGOUT"); ?></a></li>
			</ul>
		</div>
	<?php endif; ?>
	</li>

	<?php if($this->user->guest&&$this->config->get('oauth_sign_up',1)==1):?>
	<li class="jcm-pull-right" style="border-<?php echo $rtl?'left':'right' ?>:1px solid #ddd;padding-<?php echo $rtl?'left':'right' ?>:6px">
		<a href="<?php echo $this->url2signup; ?>"><?php echo JText::_("JREGISTER"); ?></a>
	</li>
	<?php endif;?>
</ul>