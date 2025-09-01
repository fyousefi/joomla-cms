<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentViewItems extends JViewLegacy
{
    protected   $data       = null;

    protected   $comments   = null;

    protected   $user       = null;

    protected   $totalComment = 0;

    protected   $peoples    = null;

    protected   $peopleCount = 0;

    public      $cm         = null;

    protected   $config     = null;

    protected   $styles     = null;

    public      $js         = [];

    public      $amp        = false;

    protected function loadComments()
    {
        $this->data = $this->get('comments');
        return $this->data;
    }

    public function render( $tpl = null )
    {
        $app = JFactory::getApplication();
        $this->config = JLexCommentHelper::getConfig();
        $this->data   = $this->get('comments');
        $this->user   = $this->get('user');
        $this->comments = $this->data->comments;

        if($this->getLayout()=='default')
        {
            // get list user in this conversation
            $this->totalComment = $this->get ('TotalComment');
            
            $this->peoples      = $this->get('peoples');
            $this->peopleCount  = $this->get('morePeople');
            $this->styles       = $this->config->get('cm_style',1)?$this->get('styles'):[];

            if($this->config->get('oauth_sign_up',1)==1)
            {
                if(JE_JVERSION=="J3")
                {
                    require_once JPATH_SITE . '/components/com_users/helpers/route.php';
                    $url2signup = JRoute::_('index.php?option=com_users&view=registration&Itemid=' . UsersHelperRoute::getRegistrationRoute());
                } else {
                    $url2signup = JRoute::_('index.php?option=com_users&view=registration');
                }
                
                $this->set('url2signup', $url2signup);
            }

            if($this->config->get('rss',1)==1)
            {
                $this->data->url2rss = JRoute::_('index.php?option=com_jlexcomment&view=items&com_name='.$this->data->object->com_name.'&com_key='.$this->data->object->com_key.'&format=feed',false);
            }

            $this->data->hl = $app->input->getInt("comment_id", 0);

            // reaction
            $reaction = null;
            if($this->config->get('jcm_reaction',0)!=0)
            {
                $reaction = $this->config->get('jcm_reaction_data',null);
                $reaction = json_decode($reaction);

                if($reaction!=null && count($reaction))
                {
                    $dt = array();
                    foreach ($reaction as $k=>$v)
                    {
                        $v->label = JText::_($v->label);
                        $v->icon = preg_match('/^https?:\/\//', $v->icon)?$v->icon:(JUri::root(true).'/'.$v->icon);
                        $dt[$v->id] = $v;
                    }

                    $reaction = $dt;
                    $dt = null;
                }
            }

            // js part
            // - language
            $langs = [   
                "cancel" => jtext::_("JCM_CANCEL"),
                "submit" => jtext::_("JCM_SUBMIT"),
                "dialog" => jtext::_("JCM_DIALOG"),
                "remove" => jtext::_("JCM_REMOVE"),
                "caption" => jtext::_("JCM_CAPTION"),
                "description" => jtext::_("JCM_DESCRIPTION"),
                "update" => jtext::_("JCM_UPDATE"),
                "edit" => jtext::_("JCM_EDIT"),
                "upgrade_client_browser" => jtext::_("JMC_UPGRADE_CLIENT_BROWSER"),
                "sticker" => jtext::_("JCM_STICKER"),
                "search_sticker" => jtext::_("JCM_SEARCH_STICKER"),
                "home" => jtext::_("JCM_HOME"),
                "please_wait" => jtext::_("JCM_PLEASE_WAIT"),
                "no_sticker_found" => jtext::_("JCM_NO_STICKER_FOUND"),
                "embed_location" => jtext::_("JCM_EMBED_LOCATION"),
                "add" => jtext::_("JCM_ADD"),
                "insert" => jtext::_("JCM_INSERT"),
                "emoticons" => jtext::_("JCM_EMOTICONS"),
                "no_match" => jtext::_("JCM_NO_MATCH"),
                "mark_all_read" => jtext::_("JCM_MARK_ALL_READ"),
                "no_notification" => jtext::_("JCM_NO_NOTIFICATION"),
                "all_notification" => jtext::_("JCM_ALL_NOTIFICATION"),
                "notification" => jtext::_("JCM_NOTIFICATIONS"),
                "see_more" => jtext::_("JCM_SEE_MORE"),
                "warning" => jtext::_("JCM_WARNING"),
                "delete" => jtext::_("JCM_DELETE"),
                "delete_confirm" => jtext::_("JCM_DELETE_CONFIRM"),
                "reporting" => jtext::_("JCM_REPORTING"),
                "cancel_reporting" => jtext::_("JCM_CANCEL_REPORTING"),
                "ignore" => jtext::_("JCM_IGNORE"),
                "report_reason" => jtext::_("JCM_PLEASE_TELL_US_REASON"),
                "your_name" => jtext::_("JCM_YOUR_NAME"),
                "your_email" => jtext::_("JCM_YOUR_EMAIL"),
                "fill_your_report" => jtext::_("JCM_FILL_YOUR_REPORTING"),
                "report" => jtext::_("JCM_REPORT"),
                "unsubscribe" => jtext::_("JCM_UNSUBSCRIBE"),
                "unsubscribe_msg" => jtext::_("JCM_UNSUBSCRIBE_MSG"),
                "unsubscribe_confirm" => jtext::_("JCM_UNSUBSCRIBE_CONFIRM"),
                "subscribe" => jtext::_("JCM_SUBSCRIBE"),
                "followed_status" => jtext::_("JCM_FOLLOWED_CONVERSATION"),
                "fill_your_info" => jtext::_("JCM_FILL_YOUR_INFO"),
                "show_previous_comments" => jtext::_("JCM_SHOW_PREVIOUS_COMMENT"),
                "show_more_reply" => jtext::_("JCM_SHOW_MORE_REPLY"),
                "permalink" => jtext::_("JCM_PERMALINK"),
                "thumb_remove_desc" => jtext::_("JCM_THUMB_REMOVE_INTRO"),
                "upload_image" => jtext::_("JCM_UPLOAD_IMAGE"),
                "thumb_editing" => jtext::_("JCM_THUMB_EDITING"),
                "file_must_img" => jtext::_("JCM_FILE_MUST_IMAGE"),
                "load_more_cm" => jtext::_("JCM_LOAD_MORE_COMMENT"),
                "comment" => jtext::_("JCM_COMMENT"),
                "comments" => jtext::_("JCM_COMMENTS"),
                "replied_comment" => jtext::_("JCM_REPLIED_A_COMMENT"),
                "username" => jtext::_("JCM_USERNAME"),
                "password" => jtext::_("JCM_PASSWORD"),
                "login" => jtext::_("JCM_LOGIN"),
                "username_not_blank" => jtext::_("JCM_USERNAME_NOT_BLANK"),
                "pw_not_blank" => jtext::_("JCM_PASSWORD_NOT_BLANK"),
                "success" => jtext::_("JCM_SUCCESS"),
                "no_comment" => jtext::_("JCM_NO_COMMENT"),
                "email_incorrect" => jtext::_("JCM_EMAIL_INCORRECT"),
                "comment_empty" => jtext::_("JCM_COMMENT_NOT_EMPTY"),
                "comment_too_long" => jtext::_("JCM_COMMENT_TOO_LONG"),
                "fill_your_name" => jtext::_("JCM_YOU_MUST_FILL_YOUR_NAME"),
                "min_comment_length" => jtext::_("JCM_MIN_COMMENT_LENGTH_ERROR"),
                "subscribe_verify" => jtext::_("JCM_SUBSCRIBE_EMAIL_VERIFY"),
                "readmore" => jtext::_("JCM_READMORE"),
                "close" => jtext::_("JCM_CLOSE"),
                "cm_style" => jtext::_("JCM_COMMENT_STYLE"),
                "apply" => jtext::_("JCM_APPLY"),
                "no_style" => jtext::_("JCM_NO_STYLE"),
                "you" => jtext::_("JCM_YOU"),
                "more_one_pp" => jtext::_("JCM_REACTION_ONE_PEOPLE"),
                "more_many_pp" => jtext::_("JCM_REACTION_MORE_PEOPLE"),
                "more_one_pp_line" => jtext::_("JCM_REACTION_ONE_PEOPLE_ONE_LINE"),
                "more_many_pp_line" => jtext::_("JCM_REACTION_MORE_PEOPLE_ONE_LINE"),
                "ok" => jtext::_("JCM_OK"),
                "subscribe_email_require" => jtext::_("JCM_SUBSCRIBE_EMAIL_REQUIRE"),
                "insert_your_link" => jtext::_("JCM_INSERT_YOUR_LINK"),
                "link_format_incorrect" => jtext::_("JCM_LINK_FORMAT_INCORRECT"),
                "in" => jtext::_("JCM_IN_LOWER"),
                "giphy" => jtext::_("JCM_GIF_IMAGE"),
                "find_gif_image" => jtext::_("JCM_FIND_GIF_IMAGE"),
                "gif_empty_result" => jtext::_("JCM_NO_RESULTS"),
                "cm_terms" => jtext::_("JCM_TERMS"),
                "use_your_image" => jtext::_("JCM_USE_YOUR_IMAGE"),
                "term_checkbox_err" => jtext::_("JCM_RULE_CHECKBOX_TEXT_ERROR"),
                "thanks_feedback" => jtext::_("JCM_THANKS_FEEDBACK"),
                "copied" => jtext::_('JCM_COPIED'),
                "comment_title" => jtext::_('JCM_COMMENT_DIALOG_TITLE'),
                "comment_verb" => jtext::_('JCM_COMMENT_VERB'),

                // v2.7.4
                'notice' => jtext::_('JCM_NOTICE'),
                'select_files' => jtext::_('JCM_SELECT_FILES'),

                // v3.2.9
                'or' => jtext::_('JCM_OR'),
                'drag_drop' => jtext::_('JCM_DRAG_DROP_BOX')
            ];

            // media
            if($this->config->get('jcm_layout_media',1))
            {
                $mediaMsg = [
                    jtext::sprintf('JCM_MEDIA_DIALOG_FILETYPE', '<strong>'.$this->config->get('media_filetype').'</strong>'),
                    jtext::sprintf('JCM_MEDIA_DIALOG_FILESIZE', $this->config->get('media_filesize'))
                ];

                if((int) $this->config->get('maxfilenum', 0)>0)
                {
                    $mediaMsg[] = jtext::sprintf('JCM_MEDIA_DIALOG_FILENUM', $this->config->get('maxfilenum'));
                }

                $langs['media_msg'] = '<ul><li>'.implode('</li><li>', $mediaMsg).'</li></ul>';
                $langs['maxfilenum_alert']=jtext::sprintf('JCM_MEDIA_DIALOG_FILENUM', $this->config->get('maxfilenum'));
            }

            // - config
            $this->js["cf"] = [
                "key" => $this->cmConfig->key,
                "sort" => $this->config->get("sort", "best"),
                "box" => "#jlexcomment",
                "timestamp" => $this->cmConfig->timestamp,
                "request" => $this->cmConfig->request,
                "id" => $this->data->hl,
                "page_comment" => $this->data->page,
                "awe" => [
                    "src" => $this->config->get("fontawesome", 1)=="1"?"cdn":"local",
                    "v" => $this->config->get("fontawesome_v", "v5")
                ]
            ];

            // - settings
            $this->js["settings"] = [
                "peoples" => $this->config->get("u_mention")==true?$this->peoples:[],
                "member" => $this->user->guest?0:1,
                "url" => JUri::base().$this->data->object->url,
                "cm_min_length" => (int) $this->config->get("min_cm_length",0),
                "cm_max_length" => (int) $this->config->get("max_cm_length",0),
                "child_style" => (int) $this->config->get("child_style", 1),
                "profile" => $this->config->get("profile_3rd","jlexcomment")=="jlexcomment"?1:0,
                "translate" => $langs,
                "email_verify" => (int) $this->config->get("email_verify",0),
                "refresh" => $this->data->object->get("live",0)*1,
                "sticker_autopost" => (int) $this->config->get("sticker_auto",1),
                "sticker_photo" => (int) $this->config->get("sticker_photo", 1),
                "readmore" => (int) $this->config->get("cm_collapse",1),
                "reactions" => $reaction,
                "styles" => $this->styles,
                "author_name" => $this->config->get('author_name','screen_name')=='screen_name'?1:0,
                "login_type" => $this->config->get('login_type','dialog'),
                "login_url" => $this->config->get('login_url',''),
                "url_base" => JUri::base(true),
                "url_return" => base64_encode($this->data->object->url),
                "alert" => (int) $this->config->get("nof_alert",0),
                "giphy" => $this->config->get("giphy_key", ""),
                "cache" => $this->config->get('cache',1)==1,
                "sort" => $this->config->get('sort', 'best'),
                "sortC" => $this->config->get('sort_child', 'desc'),
                "openform" => $this->config->get('openform',0)==1?$this->config->get('openformpos','top'):'0',
                "amp" => $this->amp,
                "maxfilenum" => $this->config->get('jcm_layout_media',1)==1 ? (int) $this->config->get('maxfilenum'):0,
                "linkshare" => $this->config->get('jcm_share_link', 'query'),
                "preview" => (int) $this->config->get('reply_in_cm', 5),
                "dis_scroll" => $this->config->get("dis_scroll", "0")=="1",
            ];

            $this->data->translate = json_encode($langs,JSON_UNESCAPED_UNICODE);
            $langs = null;
        }

        return parent::loadTemplate( $tpl );
    }

    public function renderCm ( $tpl = null )
    {
        $this->config = JLexCommentHelper::getConfig ();
        $this->setLayout('default_comment_flex');
        return parent::loadTemplate( $tpl );
    }

    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }
}
