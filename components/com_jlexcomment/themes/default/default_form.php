<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;

$hasSocial = $this->config->get("oauth_fb",0)==1 ||
			$this->config->get("oauth_tw",0)==1 ||
			$this->config->get("oauth_gg",0)==1 ||
			$this->config->get("oauth_vk",0)==1;

?>
<div class="jcm-protect-layer"></div>
<div class="_content">
	<?php echo JLexCommentHelper::loadModules("jcm-form-top"); ?>

	<?php if($this->config->get('blocked',false)==true): ?>
		<div class="jcm_form_error active">
			<?php echo $this->config->get('blocked_msg',''); ?>
		</div>
	<?php else: ?>
		<div class="jcm_form_error"></div>
	<?php endif; ?>

	<form class="jcm_form_input" method="post" action="<?php echo $this->url2save; ?>">
		<?php if ( $this->config->def('author_thumb',1)==1 ): ?>
		<div class="avatar hovercard">
		<?php if(!empty($this->user->author_link)): ?>
			<a href="<?php echo $this->user->author_link ?>">
				<img class="jcm-thumb" src="<?php echo $this->user->author_thumb ?>" alt="<?php echo $this->user->author_name ?>" <?php echo preg_match('/jcm/', $this->user->author_thumb)?'width="120" height="120"':'' ?> />
			</a>
		<?php else: ?>
			<img class="jcm-thumb" src="<?php echo $this->user->author_thumb ?>" alt="" <?php echo preg_match('/jcm/', $this->user->author_thumb)?'width="120" height="120"':'' ?> />
		<?php endif; ?>
		</div>
		<?php endif; ?>
	
		<div class="jcm-form">
			<?php if($this->config->get('u_post_comment',true)): ?>
			<div class="jcm-input-cm">
				<div class="jcm-textarea" data-name="comment" tabindex="0" role="textbox" aria-multiline="true" contenteditable="true" data-role="editable" name="comment" title="Comment form"></div>
				<span class="jcm-text-placeholder active"><?php echo JText::_("JCM_SAY_HERE"); ?></span>
			</div>
			<div class="_controller<?php echo $this->user->guest?'':' _member' ?>">
				<?php
					if($this->config->get('text_format',1)==1):
						$tags = array(
								'bold'=>'JCM_FMX_BOLD',
								'italic'=>'JCM_FMX_ITALIC',
								'underline'=>'JCM_FMX_UNDERLINE',
								'strikeThrough'=>'JCM_FMX_STRIKE',
								'code'=>'JCM_FMX_CODE',
								'quote'=>'JCM_FMX_QUOTE',
								'createLink'=>'JCM_FMX_INSERT_LINK',
								'unlink'=>'JCM_FMX_UNLINK'
							);
				?>
				<!-- text format -->
				<div class="jcm-text-fm hide">	
					<button type="button" class="jcm-btn-open" aria-label="font">
						<i class="fas fa-font"></i>
					</button>
					<div class="ls">
						<?php
							foreach($tags as $cmd=>$text)
							{
								$icon = $cmd;
								echo '<button type="button" data-tag="'.$cmd.'" class="jcm-tooltip" aria-label="'.$text.'" title="'.jtext::_($text).'"';
								if($cmd=='unlink')
								{
									echo ' style="display:none"';
								}
								if($cmd=='createLink')
								{
									$icon = 'link';
									echo ' disabled';
								}
								if($cmd=='quote')
								{
									$icon = 'quote-left';
								}
								echo '><i class="fas fa-'.strtolower($icon).'"></i></button>';
							}
						?>
					</div>
				</div>
				<?php endif;?>

				<?php if($this->config->get('giphy', 0)==1): ?>
				<button type="button" class="jcm-btn-open jcm-btn-gif jcm-tooltip" title="<?php echo JText::_("JCM_GIF"); ?>">
					<span class="in">GIF</span>
				</button>
				<?php endif; ?>

				<?php if ( $this->config->get('u_upload_file',false)==true ): ?>
				<button type="button" class="jcm-btn-open jcm-btn-upload jcm-tooltip" title="<?php echo JText::_("JCM_UPLOAD_FILES"); ?>">
					<i class="fas fa-paperclip"></i>
				</button>
				<?php endif; ?>

				<?php if($this->config->get("sticker","1")): ?>
				<button type="button" class="jcm-btn-open jcm-btn-sticker jcm-tooltip" title="<?php echo JText::_("JCM_ADD_STICKER"); ?>">
					<i class="far fa-image"></i>
				</button>
				<?php endif; ?>

				<?php if($this->config->get('location',0)):?>
				<button type="button" class="jcm-btn-open jcm-btn-geo jcm-tooltip" title="<?php echo JText::_("JCM_SHARE_POSITION"); ?>">
					<i class="fas fa-map-marker-alt"></i>
				</button>
				<?php endif; ?>

				<?php if($this->config->get('emoji',1)): ?>
				<button type="button" class="jcm-btn-open jcm-btn-emoticon jcm-tooltip" title="<?php echo JText::_("JCM_INSERT_EMOIJ"); ?>">
					<i class="far fa-grin-hearts"></i>
				</button>
				<?php endif; ?>

				<?php if($this->config->get('cm_style',1)): ?>
				<button type="button" class="jcm-btn-open jcm-btn-style jcm-tooltip" title="<?php echo JText::_("JCM_STYLE"); ?>">
					<i class="fas fa-magic"></i>
				</button>
				<?php endif; ?>

				<?php if (!$this->user->guest): ?>
					<a href="#" class="jcm-btn-cancel"><?php echo JText::_("JCM_CANCEL") ;?></a>
					<button type="submit" class="jcm-btn-post forMember"><?php echo JText::_("JCM_POST"); ?></button>
				<?php endif; ?>
			</div>

			<?php else: ?>

			<div class="jcm-input-cm">
				<span class="jcm-text-placeholder active">
					<?php echo $this->user->guest ? JText::_("JCM_YOU_MUST_LOGIN_TO_POST_COMMENT") : JText::_("JCM_COMMENT_IS_DISABLED"); ?>
				</span>
			</div>
			<?php endif; ?>
		</div>

		<div class="clearfix"></div>

		<?php if($this->config->get("location",0)): ?>
		<div class="jcm-map-status">
			<span>&#8212;</span>
			<a href="#" class="jcm-btn-geo-status"></a>
			<a href="#" class="jcm-empty-geo"><?php echo JText::_("JCM_CANCEL"); ?></a>
		</div>
		<?php endif; ?>

		<?php if ($this->config->def ("max_cm_length") > 0): ?>
		<div class="jcm-symbol-limit">
			<span class="symbol-count" data-max="<?php echo $this->config->def ("max_cm_length"); ?>"></span>
			<span><?php echo JText::_("JCM_PREFIX_SUB_CHAR"); ?></span>
		</div>
		<?php endif; ?>

		<?php if($this->config->get('auto_suggest_sub',1)==1&&$this->config->get("u_sub_comment",false)==true && $this->user->get('jcm_subscribe',false)==false && (!$this->user->guest || ($this->user->guest && $this->config->get('form_email_field',1)>0))):?>
		<div class="clearfix"></div>
		<div class="jcm-suggest-follow jcm-subdiv">
			<label>
				<input type="checkbox" class="__fm" <?php echo $this->config->get('auto_sub',1)==1?'checked':'' ?> name="__sub" value="1" />
				<span><?php echo JText::_('JCM_AUTO_SUB_POST_WHEN_POST_CM')?></span>
			</label>
		</div>
		<?php endif; ?>

		<?php if($this->config->get('private_comment',0)==1):?>
		<div class="clearfix"></div>
		<div class="jcm-subdiv">
			<label>
				<input type="checkbox" class="__fm" name="locked" value="1" />
				<span class="jcm-tooltip" title="<?php echo jtext::_('JCM_MARK_PRIVATE_COMMENT_DESC') ?>"><?php echo JText::_('JCM_MARK_PRIVATE_COMMENT')?></span>
			</label>
		</div>
		<?php endif; ?>

		<?php if($this->config->get('cm_rule',0)&&$this->config->get("cm_rule_checkbox",0)):?>
		<div class="clearfix"></div>
		<div class="jcm-subdiv">
			<label>
				<input type="checkbox" class="__fm" name="__term" value="1">
				<span><?php echo JText::sprintf('JCM_RULE_CHECKBOX_TEXT', '<a class="jcm-terms" href="#">', '</a>')?></span>
			</label>
		</div>
		<?php endif; ?>

		<div class="clearfix"></div>
	</form>

</div>

<?php
if($this->user->guest):
?>
<div class="forGuest jcm-inline-top<?php echo !$hasSocial&&!$this->config->get("login",1)?' nosocl':''?>" <?php echo !$this->config->get('u_post_comment',true)?'style="display:block"':'' ?>>
	<div class="jcm-connect">
		<?php if($hasSocial): ?>
		<div class="jcm-h6">
			<span><?php echo JText::_("JCM_LOGIN_WITH"); ?></span>
			<?php if ($this->config->get('oauth_sign_up',1)==1): ?>
			<span>( <a href="<?php echo $this->url2signup; ?>"><?php echo JText::_("JCM_SIGNUP_CF"); ?></a> )</span>
			<?php endif; ?>
		</div>
		<ul data-role="login-menu" class="jcm-inline login-buttons">
			<?php if($this->config->get("login",1)):?>
			<li class="auth-site">
				<button type="button" class="jcm-u-login" title="<?php echo JText::_("JCM_LOGIN") ?>">
					<i class="fas fa-user"></i>
				</button>
			</li>
			<?php endif;?>

			<?php if ($this->config->get("oauth_fb",0)==1): ?>
			<li class="auth-facebook">
				<button type="button" class="jcm-u-oauth jcm-tooltip" data-action="facebook" title="<?php echo JText::_("JCM_LOGIN_USE_FACEBOOK_ACCOUNT"); ?>">
					<i class="fab fa-facebook-f"></i>
				</button>
			</li>
			<?php endif; ?>

			<?php if ($this->config->get("oauth_tw",0)==1): ?>
			<li class="auth-twitter">
				<button type="button" class="jcm-u-oauth jcm-tooltip" data-action="twitter" title="<?php echo JText::_("JCM_LOGIN_USE_TWITTER_ACCOUNT"); ?>">
					<i class="fab fa-twitter"></i>
				</button>
			</li>
			<?php endif; ?>

			<?php if ($this->config->get("oauth_gg",0)==1): ?>
			<li class="auth-google">
				<button type="button" class="jcm-u-oauth jcm-tooltip" data-action="google" title="<?php echo JText::_("JCM_LOGIN_USE_GOOGLE_ACCOUNT"); ?>">
					<i class="fab fa-google"></i>
				</button>
			</li>
			<?php endif; ?>

			<?php if ($this->config->get("oauth_vk",0)==1): ?>
			<li class="auth-vk">
				<button type="button" class="jcm-u-oauth jcm-tooltip" data-action="vk" title="<?php echo JText::_("JCM_LOGIN_USE_VK_ACCOUNT"); ?>">
					<i class="fab fa-vk"></i>
				</button>
			</li>
			<?php endif; ?>
		</ul>
		<?php elseif($this->config->get("login", 1)): ?>
		<h6>
			<span><?php echo JText::_("JCM_YOU_ARE_GUEST"); ?></span>
			<?php if ($this->config->get('oauth_sign_up',1)==1): ?>
			<span>( <a href="<?php echo $this->url2signup; ?>"><?php echo JText::_("JCM_SIGNUP_CF"); ?></a> )</span>
			<?php endif; ?>
		</h6>
		<ul class="jcm-inline login-buttons oauth-off">
			<li class="auth-site">
				<button type="button" class="jcm-u-login" title="<?php echo JText::_("JCM_LOGIN"); ?>">
					<?php echo JText::_("JCM_LOGIN_NOW"); ?>
				</button>
			</li>
		</ul>
		<?php endif; ?>
	</div>

	<?php if($this->config->get('u_post_comment',true)):?>
	<!-- or post as a guest if allowed. -->
	<div class="_postAsGuest">
		<h6><?php echo JText::_("JCM_POST_AS_GUEST"); ?></h6>
		<input type="text" id="jcm-input-name-field" placeholder="<?php echo JText::_("JCM_YOUR_NAME"); ?>" />

		<?php if ($this->config->get('form_email_field',1)>0): ?>
		<input type="email" 
			id="jcm-input-email-field" 
			data-require="<?php echo $this->config->get('form_email_field',1)==2?0:1 ?>"
			placeholder="<?php echo $this->config->get('form_email_field',1)==2?JText::_('JCM_YOUR_EMAIL_OPTIONAL'):JText::_("JCM_YOUR_EMAIL"); ?>" />
		<?php endif; ?>

		<button class="jcm-btn-submit"><?php echo JText::_("JCM_POST"); ?></button>
	</div>
	<?php endif;?>
</div>
<?php endif; ?>

<?php echo JLexCommentHelper::loadModules("jcm-form-bottom"); ?>
<?php echo isset($this->form_plugin) ? $this->form_plugin : ""; ?>