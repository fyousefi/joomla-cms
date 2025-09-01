<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelItem extends JModelLegacy
{
    public $id = 0;

    public $com_name  = "";

    public $com_key   = "";

    public function getItem()
    {
        if($this->id<1)
        {
            $this->setError(jtext::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        $app    = JFactory::getApplication();
        $user   = JFactory::getUser();
        $config = JLexCommentHelper::getConfig();
        $ip_address = JLexCommentHelper::ip_address();
        
        $query = $this->getDbo()->getQuery (true);
        $query->select ("cm.*")
              ->from ("#__jlexcomment cm")
              ->where ("cm.id=" . $this->id);

        // user table - created
        $query->select ('u1.name as created_name')
              ->leftJoin ('#__users u1 ON cm.created_by=u1.id');

        // user table - modified
        $query->select ('u2.name as modified_name')
              ->leftJoin ('#__users u2 ON cm.modified_by=u2.id');

        // sticker
        $query->select ('sticker.path2file sk_path2file')
              ->leftJoin ('#__jlexcomment_sticker AS sticker ON(cm.sticker_id=sticker.id AND sticker.published=1) ');

        $row = $this->getDbo()->setQuery ($query)->loadObject();

        if (! $row)
        {
            $this->setError (JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        // check permission
        $owner = $row->created_by>0 ? $row->created_by==$user->id : $row->ip_address==$ip_address;
        
        if ($config->get("u_edit_any_comment",false)==true)
        {
            $owner = true;
        } else {
            if ( $config->get("u_edit_own_comment",false)==true && $owner)
            {
                $owner = true;
            } else {
                $owner = false;
            }
        }

        // special
        if($app->isClient('administrator')) $owner=true;

        if(!$owner)
        {
            $this->setError(JText::_("JCM_PERMISSION_DENIED"));
            return false;
        }

        $row->media = $this->_getMedia ();
        $row->params = json_decode ($row->params);

        $row->comment = html_entity_decode($row->comment);

        return $row;
    }

    private function _getMedia()
    {
        $session = JFactory::getSession();
        $query = $this->_db->getQuery(true);
        $query->select("*")
              ->from("#__jlexcomment_media")
              ->where("comment_id=" . $this->id);

        $rows = $this->_db->setQuery($query)->loadObjectList();

        if(!$rows) return null;

        foreach($rows as $k=>&$row)
        {
            $session->set('item_' . $row->id, true, 'jcm_media');
            if($row->fileType=='image')
            {
                $row->preview = JUri::root(true).'/'.str_replace('og/', 'thumb/', $row->path);
            } else {
                $row->download = JUri::base(true).'/index.php?option=com_jlexcomment&task=media.download&id='.$row->id;
            }
        }

        return $rows;
    }

    public function getForm()
    {
        $path = dirname (__FILE__) . "/forms/comment.xml";
        $form = JForm::getInstance("jcm_comment", $path);
        $app = JFactory::getApplication();

        if($this->id>0)
        {
            $comment = $this->getItem();
            if(!$comment)
            {
                throw new Exception(jtext::_("JCM_PAGE_NOT_FOUND"), 404);
            }

            $comment->comment = htmlspecialchars_decode($comment->comment);

            $form->bind($comment);
        } else {
            // try get tmp data
            $session = JFactory::getSession();
            $data = $session->get("jcm_comment_tmp",null);

            if(is_array($data)) $form->bind($data);
        }

        return $form;
    }

    // only use for back-end
    public function getMedia()
    {
        if($this->id<1) return null;
        return $this->_getMedia();
    }

    protected $mention_id = [];
    
    public function save()
    {
        $app     = JFactory::getApplication();
        $config  = JLexCommentHelper::getConfig();
        $row     = $this->getTable('comment', 'TableCm');
           
        $session = JFactory::getSession();
        $user    = JFactory::getUser();

        $isNew   = true;
        $post    = $app->input->post;
        $query   = $this->_db->getQuery(true);
        $now     = JFactory::getDate()->toSql();
        $ignore  = [];

        // check new or modify
        if($this->id>0)
        {
            $row->load($this->id);
            if($row->id>0)
                $isNew = false;
        }

        if($isNew==true)
        {
            // find obj_id parameter
            if($app->isClient('site'))
            {
                $wClauses = [
                        'published=1',
                        'com_name=' . $this->_db->quote($this->com_name),
                        'com_key=' . $this->_db->quote($this->com_key)
                    ];

                
                $query->clear()
                      ->select("*")
                      ->from("#__jlexcomment_obj")
                      ->where($wClauses);

                $entry = $this->_db->setQuery($query)->loadObject();

                if(!$entry)
                {
                    $this->setError(jtext::_("JCM_PAGE_NOT_FOUND"));
                    return false;
                }

                $row->set("obj_id", $entry->id);
            }
        }

        if($isNew)
        {
            if($config->get('u_post_comment',true)==false)
            {
                $this->setError(jtext::_("JCM_YOU_DONT_PERMISSION_TO_POST_COMMENT"));
                return false;
            }
            
            if($post->getInt("parent_id",0)>0 && $config->get('u_reply_comment',true)==false)
            {
                $this->setError(jtext::_("JCM_YOU_DONT_PERMISSION_TO_POST_REPLY"));
                return false;
            }
        }

        // process parent ID
        $parent_id = $post->getInt("parent_id",0);
        $root_parent_id = 0;

        if($parent_id>0)
        {
            // check parent_id
            $query->clear()
                  ->select("COUNT(*)")
                  ->from("#__jlexcomment")
                  ->where([
                        "id=".$parent_id,
                        "published=1"
                    ]);

            $parent_found = $this->_db->setQuery($query)->loadResult();

            if($parent_found<1)
            {
                $this->setError(jtext::_("JCM_PAGE_NOT_FOUND"));
                return false;
            }

            $root_parent_id = $this->__find_root($parent_id);

            $row->set("parent_id", $parent_id);
            
            if(!$isNew && $parent_id!=$row->parent_id)
                $this->__move_child($row->id, $root_parent_id);
        } else {
            $row->set("parent_id", 0);
            
            if(!$isNew && $parent_id!=$row->parent_id)
                $this->__move_child($row->id, $row->id);
        }

        $row->set("root_parent_id", $root_parent_id);


        if(!$isNew)
        {
            $ip_address = JLexCommentHelper::ip_address();
            $owner = !$row->created_by ? $row->created_by==$user->id : $ip_address==$row->ip_address;
        
            if($config->get("u_edit_any_comment",false)==true || 
                ($config->get("u_edit_own_comment", false)==true && $owner))
            {
                $owner = true;
            } else {
                $owner = false;
            }

            if(!$owner)
            {
                $this->setError(jtext::_("JCM_YOU_DONT_PERMISSION_TO_EDIT_COMMENT"));
                return false;
            }

            if($app->isClient('site') && !in_array(3, $user->getAuthorisedViewLevels()))
            {
                if($config->get('deny_edit_when_child',0)==1 && $row->get('child_count')>0)
                {
                    $this->setError(jtext::_("JCM_YOU_DONT_PERMISSION_TO_EDIT_COMMENT_WHEN_HAVE_CHILD"));
                    return false;
                }

                if((int) $config->get('edit_after',0)>0)
                {
                    $delta = JFactory::getDate()->toUnix() - JFactory::getDate($row->get('created_time'))->toUnix();
                    if($delta<=(int) $config->get('edit_after'))
                    {
                        $this->setError(jtext::sprintf("JCM_YOU_DONT_PERMISSION_TO_EDIT_COMMENT_AFTER_TIME", (int) $config->get('edit_after')));
                        return false;
                    }
                }
            }
        }

        // giphy
        $giphy = $post->getString('giphy_id', '');
        if(preg_match("/^([A-z0-9]+),([1-9][0-9]*),([1-9][0-9]*)$/", $giphy) && $config->get("giphy",0)==1)
        {
            $row->set("giphy_id", $giphy);
        } else {
            $row->set("giphy_id", NULL);
        }

        // sticker
        $sticker_id = $post->getInt("sticker_id", 0);
        if($sticker_id>0)
        {
            $query->clear()
                  ->select('s.id, s.group_id')
                  ->from('#__jlexcomment_sticker s')
                  ->leftJoin ('#__jlexcomment_sticker_group sg ON s.group_id=sg.id')
                  ->where ('s.published=1 AND s.id=' . $sticker_id)
                  ->where ('(sg.id IS NULL OR (sg.id IS NOT NULL AND sg.published=1))');

            $sticker_found = $this->_db->setQuery($query)->loadObject();
            if(!$sticker_found) $sticker_id=0;
        }

        $row->set('sticker_id', ($sticker_id>0?$sticker_id:0));

        // style
        $style_id = $post->getInt("style_id", 0);
        if($style_id>0)
        {
            $query->clear()
                  ->select("COUNT(*)")
                  ->from("#__jlexcomment_style")
                  ->where([
                        "published=1",
                        "id=" . $style_id
                    ]);

            $style_found = $this->_db->setQuery($query)->loadResult();
            if($style_found<1) $style_id = 0;
        }

        $row->set("style_id", ($style_id>0?$style_id:0));

        // private comment?
        $private = $post->getInt('locked', 0);
        $row->set("locked", ($private>0?1:0));


        // comment body
        $comment = array_key_exists("comment", $_POST)?$_POST["comment"]:"";
        $cmClone = $comment;

        // mention
        if(!preg_match("/^\s*$/", $comment))
        {
        
            $pattern = '/<span .*?class="(?:.*?jcm-mention.*?)" .*?data-id="([1-9][0-9]*)"(?:\scontenteditable="false")?>(.*?)<\/span>/';
            $comment = preg_replace_callback($pattern, function ($matches){
                $this->mention_id [] = $matches[1];

                return '{u-' .$matches[1]. ','.$matches[2].'}';
            }, $comment);
        }

        if($post->getInt('plaintext',0)==0)
        {
            // strip all break line
            if($app->isClient('site') && $config->get('text_format',1)==0){
                $comment = preg_replace("/\r|\n/", "", $comment);
                $comment = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $comment);
                $comment = strip_tags($comment);
            } else {
                $comment = $this->stripUnwantedTagsAndAttrs($comment);
                $comment = preg_replace('/^\<div\>/i', '', $comment);
                $comment = preg_replace('/\<\/div\>$/i', '', $comment);
            }
        } else {
            $comment = htmlspecialchars($comment);
        }

        $comment = trim($comment);

        // check length of comment
        if(!preg_match('/^\s*$/', $comment))
        {
            $cmClone = strip_tags($cmClone);

            $min = $config->get('min_cm_length',0)*1;
            $max = $config->get('max_cm_length',0)*1;

            $cmClone = html_entity_decode($cmClone);
            $cmClone = $this->removeEmoji($cmClone);

            if($min>0 && mb_strlen($cmClone)<$min)
            {
                $this->setError(JText::sprintf("JCM_MIN_COMMENT_LENGTH_ERROR", $min));
                return false;
            }

            if($max>0 && mb_strlen($cmClone)>$max)
            {
                $this->setError(JText::_("JCM_COMMENT_TOO_LONG"));
                return false;
            }
        }

        $row->set('comment', $comment);

        $allows = [
                'guest_name' => 'string',
                'guest_email' => 'string'
            ];

        if($app->isClient('site'))
        {
            // auto publish
            if($config->get("u_auto_publish",false)==true)
            {
                $row->set('published', 1);
            } else {
                $row->set('published', 0);
            }

            // time
            $row->set(($isNew?"created_time":"modified_time"), $now);

            // author
            $row->set(($isNew?"created_by":"modified_by"), $user->id);

            // sticker id
            if(!$config->get("sticker", "1"))
            {
                $row->set("sticker_id", 0);
            }

            $row->set("language", JFactory::getLanguage()->getTag());
        } else {
            // publish any comment
            if($config->get("u_state_any_comment", true)==false)
            {
                $ignore[] = "published";
            }

            // featured any comment
            if($config->get("u_feature_any_comment", true)==false)
            {
                $ignore[] = "featured";
            }

            $allows['obj_id']           = 'int';
            $allows['created_by']       = 'int';
            $allows['created_time']     = 'string';
            $allows['modified_by']      = 'int';
            $allows['modified_time']    = 'string';
            $allows['language']         = 'string';
        }

        // bad words
        $bad_words = $config->get('bad_words','');
        if($app->isClient('site') && $config->get('filter_bad_word',1)==1 && !empty($bad_words) && $config->get('bw_next',0)!=0)
        {
            $haveBadWord = false;
            $detectBadWord = '';
            $cmClone2 = strip_tags($cmClone);
            $bad_words = explode("," , $bad_words);

            if(count($bad_words))
            {
                foreach($bad_words as $word)
                {
                    $word = trim($word);
                    if($word=='') continue;

                    if(preg_match("/".preg_quote($word)."/i", $cmClone2))
                    {
                        $detectBadWord = $word;
                        $haveBadWord = true;
                        break;
                    }
                }
            }

            if($haveBadWord && $config->get('bw_next')==1)
            {
                $row->set('published', 0);
            }

            if($haveBadWord && $config->get('bw_next')==2)
            {
                $this->setError(jtext::sprintf('JCM_COMMENT_CONTAIN_BAD_WORD_MSG', $detectBadWord));
                return false;
            }
        }

        if($app->isClient('site') && $config->get('hold_cm_link',0)==1)
        {
            $link_found = preg_match_all("/[a-z]+:\/\/\S+/", $comment, $links);
            if(is_int($link_found) && $link_found>0)
            {
                $links = $links[0];
                foreach($links as $link_item)
                {
                    if(preg_replace('/^www\./','',parse_url($link_item, PHP_URL_HOST))!=preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']))
                    {
                        $row->set('published', 0);
                    }
                }
            }
        }

        if($isNew)
            $row->set("ip_address", JLexCommentHelper::ip_address());

        // params
        if($app->isClient('site'))
        {
            $params = [];

            // permission:location
            if($config->get("location",0) && $post->getString('map_lng', null) != null)
            {
                $params['map'] = [
                    'address' => $post->getString('map_address', ''),
                    'lng' => $post->getString('map_lng', ''),
                    'lat' => $post->getString('map_lat', ''),
                    'name' => $post->getString('map_name', ''),
                    'icon' => $post->getString('map_icon', '')
                ];
            }

            $row->set('params', json_encode($params));
        } else {
            $params = $post->get('params', [], 'array');
            $row->set('params', json_encode($params));
        }

        try {
            $events = JLexCommentHelper::dispatcher("onBeforeSave", array(
                &$row,
                "com_jlexcomment.save"
            ),  "check");
        } catch(Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // bind data
        $row->bind($post->getArray($allows), $ignore);

        if(!$row->check())
        {
            $this->setError($row->getError());
            return false;
        }

        if(!$row->store(true))
        {
            $this->setError(jtext::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER") . $row->getError());
            return false;
        }

        // if it's reply comment, add it to notification
        if($row->parent_id>0 && $isNew==true && $app->isClient('site'))
        {
            $this->__alert_import($row->parent_id, $row, true);
        }

        // permission:import media
        if($config->get("u_upload_file", true)==true)
        {
            if(!$isNew)
            {
                $query->clear()
                      ->update("#__jlexcomment_media")
                      ->set("comment_id=0")
                      ->where("comment_id=".$row->id);

                $this->_db->setQuery($query)->execute();
            }
                
            $media_cid = array_key_exists("media_cid", $_POST)?$_POST["media_cid"]:[];
            $media_cid_safe = [];

            if(count($media_cid))
            {
                foreach($media_cid as $id)
                {
                    if(!is_numeric($id)) continue;

                    $permission = $session->get("item_".$id, false, "jcm_media");

                    if($permission==true)
                    {
                        $media_cid_safe[] = (int) $id;
                        $session->clear("item_".$id, "jcm_media");
                    }
                }

                if(count($media_cid_safe))
                {
                    $query->clear()
                          ->update("#__jlexcomment_media")
                          ->set("comment_id=".$row->id)
                          ->where("id IN (".implode(',', $media_cid_safe).")");

                    $this->_db->setQuery($query)->execute();
                }
            }
        }

        // permission:mention
        if($app->isClient('site') && $config->get("u_mention",false)==true)
        {
            $this->_notification_remove($row->id, $row->created_by, 'MENTION');

            if(count($this->mention_id)>0)
            {
                $data = [
                    'obj_id'        => $row->obj_id,
                    'comment_id'    => $row->id,
                    'action_type'   => 'MENTION',
                    'created_time'  => $now
                ];

                if($row->created_by>0)
                {
                    $data['created_by'] = $row->created_by;
                } else {
                    $data['guest_name'] = $row->guest_name;
                }

                // unique array
                $remind_cid_safe = [];
                foreach($this->mention_id as $remind_id)
                {
                    if(is_numeric($remind_id))
                    {
                        $remind_cid_safe[] = $remind_id*1;
                    }
                }
                
                $remind_cid = array_unique($remind_cid_safe);

                if(count($remind_cid))
                {
                    foreach($remind_cid as $remind_id)
                    {
                        $data ['user_remind'] = $remind_id;
                        $this->_notification_import($data);
                    }
                }
            }
        }

        // mailer notification
        if($app->isClient('site') && $isNew && $config->get("notification",0)==1 && $config->get("alert_method",0)==1
            && filter_var($config->get("alert_email",''), FILTER_VALIDATE_EMAIL)!==false
            && ($row->parent_id==0 || ($row->parent_id>0 && $config->get("alert_reply",0)==1)))
        {
            $row->author = !$row->created_by ? $row->guest_name : ($config->get('author_name','screen_name')=='screen_name'?$user->name:$user->username);
            $row->email_owner = $row->created_by==0?$row->guest_email:$user->email;
            
            if($config->get("cm_link",0)==1)
            {
                $up = CommentHelperAdmin::getObjDetail($entry->com_name, $entry->com_key, $entry->title, $entry->url);
                $entry->url = $up->url;
                $entry->url = str_replace(JUri::root(true), "", $entry->url);
                $entry->title = $up->title;
            }

            if(!preg_match('/^https?:\/\//', $entry->url))
            {
            	$entry->url = JUri::root() . trim($entry->url,"/");
            }

            $row->entry_name = $entry->title;
            $row->url = $entry->url . (stripos($entry->url, '?') !== false ? '&' : '?') . 'comment_id=' . $row->id;

            $title = JText::_('JCM_NEW_COMMENT_ADD_YOUR_SITE');

            // import attachment
            $row->attachments = CommentHelperAdmin::cmAttachment($row->id);

            JLexCommentHelper::mail2admin ($config->get("alert_email"),$config->get("alert_quick_task",1)==1,
                array($row)
                , $title);
        }

        // clear cache
        if($config->get("cache",1)==1)
            CommentHelperAdmin::clearCache();

        // trigger event after comment saved
        $events = JLexCommentHelper::dispatcher("onAfterSave", array(
            $row,
            "com_jlexcomment.save"
        ),  "check");
        
        if ($events && isset($events["status"])) {
            if (! $events["status"]) {
                $this->setError($events["msg"]);
                return false;
            }
        }

        return $row->id;
    }

    protected function stripUnwantedTagsAndAttrs($html_str)
    {
        $xml = new DOMDocument();
        libxml_use_internal_errors(true);
        $allowed_tags = array("div", "b", "br", "i", "a", "u", "strike");
        $allowed_attrs = array("href", "data-tag", "data-area");

        if(!strlen($html_str)) return "";
        $html_str='<div>'.$html_str.'</div>'; //wrap - required

        if($xml->loadHTML(mb_convert_encoding($html_str, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD))
        {
            foreach($xml->getElementsByTagName("*") as $tag)
            {
                if(!in_array($tag->tagName, $allowed_tags)){
                    $tag->parentNode->removeChild($tag);
                } else {
                    foreach ($tag->attributes as $attr){
                        if (!in_array($attr->nodeName, $allowed_attrs)){
                            $tag->removeAttribute($attr->nodeName);
                        }
                    }
                }
            }
        }
        return $xml->saveHTML($xml->documentElement);
    }

    /** Contribute: Nikita **/
    /**
    protected function stripUnwantedTagsAndAttrs($html_str)
    {
        $xml = new DOMDocument();
            libxml_use_internal_errors(true);
            $allowed_tags = array("div", "b", "br", "i", "a", "u", "strike");
        $allowed_attrs = array("href", "data-tag", "data-area");

            if(!strlen($html_str)) return "";
            $html_str='<div>'.$html_str.'</div>'; //wrap - required

        // remove &nbsp;
        $html_str=str_replace('&nbsp;','',$html_str);

        // remove whitespaces
        $html_str = preg_replace( '/>(\s)+</m', '><', $html_str );

            if($xml->loadHTML(mb_convert_encoding($html_str, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD))
        {

        $finder = new DOMXPath($xml);   

        // remove all comments
        foreach ($finder->query('//comment()') as $comment ) {
            $comment->parentNode->removeChild($comment);
        }
            // remove all script tags
            foreach( $finder->query('//script') as $script ) {
                $script->parentNode->removeChild($script);
        }
        
        // remove all style tags
        foreach( $finder->query('//style') as $style ){
            $style->parentNode->removeChild($style);
        }

        //remove all non allowed attributes
        foreach ($finder->query('//@*[not(name()="href" or name()="data-tag" or name()="data-area")]') as $attr) {
            $attr->parentNode->removeAttribute($attr->nodeName);
        }

        // remove all divs without attributes but save its children nodes
        $divsWithEmptyAttributes = $finder->query("//div[not(@*) or not(string-length(@*))]");
        while ($divsWithEmptyAttributes->length > 1) {
                $div = $divsWithEmptyAttributes->item(1);
                $fragment = $xml->createDocumentFragment();
                while($div->childNodes->length > 0) {
                $fragment->appendChild($xml->createElement('br'));
                    $fragment->appendChild($div->childNodes->item(0));
            }
                $div->parentNode->replaceChild($fragment, $div);
            $divsWithEmptyAttributes = $finder->query("//div[not(@*) or not(string-length(@*))]");
        }

        // remove all non allowed tags but save its children nodes
        $allowedTagsWithSelfPrefix =substr_replace($allowed_tags, 'self::', 0, 0);
        $allowedTags = implode('|', $allowedTagsWithSelfPrefix);
        $notAllowedTagsAsXPathQuery = '//*[not('.$allowedTags.')]';
        $notAllowedTags = $finder->query($notAllowedTagsAsXPathQuery);  
        while ($notAllowedTags->length > 0) {
                $notAllowedTag = $notAllowedTags->item(0);
                $fragment = $xml->createDocumentFragment();
                while($notAllowedTag->childNodes->length > 0) {
                $fragment->appendChild($xml->createElement('br'));
                $fragment->appendChild($notAllowedTag->childNodes->item(0));
            }
                $notAllowedTag->parentNode->replaceChild($fragment, $notAllowedTag);
            $notAllowedTags = $finder->query($notAllowedTagsAsXPathQuery);
        }

        // remove all empty tags
        foreach( $finder->query('//*[not(node() or text() or self::br)]') as $emptyTag ) {
            $emptyTag->parentNode->removeChild($emptyTag);
        }

        // remove all unnecessary br tags
        $tags = $xml->getElementsByTagName("*");
        for ($i = $tags->length-1; $i >= 0; $i-- ) {
            $tag = $tags->item($i);
                if(@$tag->nodeName !== 'br') {
                continue;
            }
                if (!$tag->nextSibling) {
                $tag->parentNode->removeChild($tag); 
            continue;
            }
                if (!$tag->previousSibling) {
                $tag->parentNode->removeChild($tag); 
            $i++;
            continue;
            }
            if ($tag->nextSibling->nodeName === 'br' && $tag->previousSibling->nodeName === 'br') {
                $tag->parentNode->removeChild($tag); 
            }
        }

        }
        
        return $xml->saveHTML($xml->documentElement);
    }
    **/

    protected function removeEmoji($string)
    {
        return preg_replace('%(?:
          \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
    )%xs', '-', $string); 
    }

    protected $alertCid = array(
                'cid'       => array(),
                'emails'    => array()
            );

    private function __alert_import($pid, $row, $loop=true)
    {
        $query = $this->_db->getQuery(true);
        $query->select('c.parent_id,c.created_by')
              ->select('IF(u.id IS NULL,c.guest_name,u.name) rep_name')
              ->select('IF(u.id IS NULL,c.guest_email,u.email) rep_email')
              ->from('#__jlexcomment c')
              ->leftJoin('#__users u ON c.created_by=u.id')
              ->where(array(
                    'c.id=' . $pid,
                    'c.published=1'
                ));

        $parent_uid = $this->_db->setQuery($query)->loadObject();

        if ($parent_uid)
        {
            if(!(
                ($row->created_by>0 && $row->created_by==$parent_uid->created_by) ||
                ($row->created_by<1 && strtolower($row->guest_email)==strtolower($parent_uid->rep_email)) ||
                ($row->created_by>0 && in_array($parent_uid->created_by, $this->alertCid['cid'])) || 
                ($row->created_by<1 && in_array($parent_uid->rep_email, $this->alertCid['emails']))
            ))
            {
                $data = array (
                    'obj_id'        => $row->obj_id,
                    'comment_id'    => $row->id,
                    'action_type'   => 'REPLY',
                    'created_time'  => JFactory::getDate()->toSql()
                );

                if ($row->created_by > 0)
                {
                    $data ['created_by'] = $row->created_by;
                } else {
                    $data ['guest_name'] = $row->guest_name;
                }

                if($parent_uid->created_by>0)
                {
                    $data ['user_remind']       = $parent_uid->created_by;
                    $this->alertCid['cid'][]    = $parent_uid->created_by;
                } else {
                    $data ['guest_remind_name']     = $parent_uid->rep_name;
                    $data ['guest_remind_email']    = $parent_uid->rep_email;
                }

                $this->alertCid['emails'][]    = strtolower($parent_uid->rep_email);

                $this->_notification_import ($data);
            }

            if($loop && $parent_uid->parent_id>0)
            {
                // continue;
                $this->__alert_import($parent_uid->parent_id, $row, $loop);
            }
        }
    }

    private function __find_root($parent_id)
    {
        $query = $this->_db->getQuery(true);
        $query->select('parent_id')
              ->from('#__jlexcomment')
              ->where('id='.$this->_db->quote($parent_id));

        $id = $this->_db->setQuery($query)->loadResult();

        // row not found
        if(is_null($id)) return 0;

        if(!$id) return $parent_id;

        return $this->__find_root($id);
    }

    private function __move_child($id, $rootId, $offset=0)
    {
        $query = $this->_db->getQuery(true);
        $query->select('id')
              ->from('#__jlexcomment')
              ->where('parent_id='.$this->_db->quote($id))
              ->order('id ASC');

        $cid = $this->_db->setQuery($query, $offset, 30)->loadColumn();

        if(!$cid) return;

        // update rootId
        $query->clear()
              ->update('#__jlexcomment')
              ->set('root_parent_id='.$this->_db->quote($rootId))
              ->where('id IN('.implode(',', $cid).')');

        $this->_db->setQuery($query)->execute();

        foreach($cid as $cmId)
        {
            $this->__move_child($cmId, $rootId);
        }

        $this->__move_child($id, $rootId, $offset+30);
    }

    private function _notification_import ($data)
    {
        $row = $this->getTable ('notification', 'TableCm');
        $row->bind ($data);

        if (! $row->check())
            return false;

        if (! $row->store())
            return false;

        return true;
    }

    private function _notification_remove ($comment_id, $user_id, $action = null)
    {
        $query = $this->_db->getQuery(true);
        $query->delete("#__jlexcomment_notification")
              ->where([
                    "comment_id=".$this->_db->quote($comment_id),
                    "created_by=".$this->_db->quote($user_id)
                ]);

        if($action!=null)
        {
            $query->where("action_type=".$this->_db->quote($action));
        }

        $this->_db->setQuery($query)->execute();
    }

    public function vote()
    {
        $app        = JFactory::getApplication();
        $now        = JFactory::getDate()->toSql();
        $user       = JFactory::getUser ();
        $ip_address = $_SERVER ["REMOTE_ADDR"];
        $row        = $this->getTable ('Comment','TableCm');
        $request    = $app->input;
        $config     = JLexCommentHelper::getConfig ();

        if ( $config->get("u_vote_comment",false)==false)
        {
            $this->setError(JText::_("JCM_YOU_DONT_PERMISSION_TO_VOTE_COMMENT") );
            return false;
        }

        if ( $this->id < 1 )
        {
            $this->setError(JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        $row->load ($this->id);

        if ( !$row->id )
        {
            $this->setError(JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        if ( (!$user->guest && $user->id==$row->created_by) || ($user->guest && $ip_address==$row->ip_address) )
        {
            if ($config->get("vote_own_cm",0)==0)
            {
                $this->setError ( JText::_("JCM_CAN_VOTE_YOURSELF_COMMENT") );
                return false;
            }
        }

        // check if user voted.
        $confirm = $user->guest ? array (
                'comment_id' => $this->id,
                'ip_address' => $ip_address
            ) : array (
                'comment_id' => $this->id,
                'created_by' => $user->id
            );

        $voteRow = $this->getTable ( 'vote', 'TableCm' );
        $voteRow->load ($confirm);

        $oldPoint = $voteRow->point;

        /*if ( $voteRow->id > 0 && $voteRow->change_times > 0 )
        {
            if ( strtotime($now) - strtotime($voteRow->created_time) < 24*3600 )
            {
                // anti spam
                $this->setError ( JText::sprintf("JCM_BACK_HOURS_TO_CONTINUE_TASKING",24) );
                return false;
            }
        }*/

        // retrieve vote
        $vote_point = $request->getInt ('point', 0);

        if (($config->get('jcm_reaction',0)==0&&!in_array($vote_point,array(-1,0,1)))||($config->get('jcm_reaction',0)!=0&&$vote_point<0))
        {
            throw new Exception("Request incorrect.", 500);
            return false;
        }

        if (($voteRow->id>0 && $voteRow->point==$vote_point) || ($voteRow->id<1 && $vote_point==0) )
        {
            // do noting
            return null;
        }

        $voteRow->set('point' ,$vote_point);

        if( $voteRow->id < 1)
        {
            // new vote
            $voteRow->bind (array(
                'comment_id'    => $this->id,
                'created_by'    => $user->id,
                'created_time'  => $now,
                'ip_address'    => $ip_address
                ));

        } else {
            // exist vote
            if ($voteRow->change_times > 0)
            {
                $voteRow->set ('change_times',0);
                $voteRow->set ('created_time', $now);
            } else {
                $voteRow->set ('change_times', 1);
            }
        }

        if ( !$voteRow->store() ) 
        {
            $this->setError ( JText::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER") );
            return false;
        }

        if ($config->get('jcm_reaction',0)==0)
        {
            $deltaUp = 0;
            $deltaDown = 0;

            if ($oldPoint==1)
            {
                if ($vote_point==-1)
                {
                    $deltaUp-=1;
                    $deltaDown+=1;
                } else {
                    $deltaUp-=1;
                }
            } elseif ($oldPoint==-1) {
                if ($vote_point==1)
                {
                    $deltaUp+=1;
                    $deltaDown-=1;
                } else {
                    $deltaDown-=1;
                }
            } else {
                if ($vote_point==1)
                {
                    $deltaUp = 1;
                } elseif ($vote_point==-1) {
                    $deltaDown = 1;
                }
            }

            $query = "UPDATE #__jlexcomment SET up_point=up_point+{$deltaUp}, down_point=down_point+{$deltaDown} WHERE id=".$this->id;

            $this->getDbo()->setQuery($query)->execute();

            return array (
                'vote'  => $vote_point,
                'up'    => $deltaUp,
                'down'  => $deltaDown
            );
        } else {
            $oldPoint = $oldPoint>1||$oldPoint==0 ? $oldPoint : -1;
            $delta = 0;
            $reaction_data = json_decode($row->reaction_data);
            if (!$reaction_data) $reaction_data = new stdClass();

            if ($oldPoint>1)
            {
                if (isset($reaction_data->$oldPoint))
                {
                    $reaction_data->$oldPoint-=1;
                }

                if ($vote_point==0)
                {
                    $delta=-1;
                } else {
                    if (isset($reaction_data->$vote_point))
                    {
                        $reaction_data->$vote_point+=1;
                    } else {
                        $reaction_data->$vote_point=1;
                    }
                }
            } else {
                if ($vote_point>0)
                {
                    if (isset($reaction_data->$vote_point))
                    {
                        $reaction_data->$vote_point+=1;
                    } else {
                        $reaction_data->$vote_point=1;
                    }
                    $delta=1;
                }
            }

            $reaction_data = json_encode($reaction_data);
            $reaction_data = $this->_db->quote($reaction_data);

            $query = "UPDATE #__jlexcomment SET reaction_count=reaction_count+{$delta},reaction_data={$reaction_data} WHERE id=".$this->id;

            $this->getDbo()->setQuery($query)->execute();

            return array (
                'vote'  => $vote_point
            );
        }
    }

    public function remove ()
    {
        $app    = JFactory::getApplication ();
        $user   = JFactory::getUser ();
        $config = JLexCommentHelper::getConfig ();

        $permission = true;

        $row = $this->getTable ('Comment','TableCm');
        $row->load ($this->id);

        if (! $row->id)
        {
            $this->setError (JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        if ( $config->get("u_del_any_comment",false)==true)
        {
            $permission = true;
        } elseif ( $config->get("u_del_own_comment",false)==true)
        {
            $permission = JLexCommentHelper::ip_address ();
            $owner = $row->created_by > 0 ? $row->created_by==$user->id : $row->ip_address = $ip_address;
            if ($owner)
            {
                $permission = true;
            }
        }

        if ( $permission==false )
        {
            $this->setError (JText::_("JCM_PERMISSION_DENIED"));
            return false;
        }

        $row->delete ();

        if($config->get("cache",1)==1)
            CommentHelperAdmin::clearCache();

        return true;
    }

    public function state ()
    {
        // publish/unpublish
        $app    = JFactory::getApplication ();
        $user   = JFactory::getUser ();
        $config = JLexCommentHelper::getConfig ();

        $row = $this->getTable ('Comment','TableCm');
        $row->load ($this->id);

        if (! $row->id)
        {
            $this->setError (JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        if ( $config->get("u_state_any_comment",false)==false )
        {
            $this->setError (JText::_("JCM_PERMISSION_DENIED"));
            return false;
        }

        $state = $app->input->getInt ('state', 1) == 1 ? 1 : 0;
        $row->do_publish ( $state );

        if($config->get("cache",1)==1)
            CommentHelperAdmin::clearCache();

        return true;
    }

    public function feature ()
    {
        // publish/unpublish
        $app = JFactory::getApplication ();
        $user = JFactory::getUser ();
        $config = JLexCommentHelper::getConfig ();

        $row = $this->getTable ('Comment','TableCm');
        $row->load ($this->id);

        if (! $row->id)
        {
            $this->setError (JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        if ( $config->get("u_feature_any_comment",false)==false )
        {
            $this->setError (JText::_("JCM_PERMISSION_DENIED"));
            return false;
        }

        $state = $app->input->getInt ('state', 1) == 1 ? 1 : 0;
        $query = "UPDATE #__jlexcomment SET featured={$state} WHERE id=" . $this->id;
        $this->getDbo()->setQuery ($query)->execute();

        // clear cache
        if($config->get("cache",1)==1)
            CommentHelperAdmin::clearCache();

        return true;
    }
}
