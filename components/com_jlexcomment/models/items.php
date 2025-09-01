<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

require_once dirname(__FILE__).'/../libraries/pagination.php';

class JLexCommentModelItems extends JModelLegacy
{
    public $com_url     = '';

    public $com_title   = '';

    public $com_name    = '';

    public $com_key     = '';

    public $comment_id  = 0; // using to find offset

    public $parent_id   = 0;

    public $indent_root = true;

    public $timestamp   = 0;

    public $timestamp_offset = 0;

    public $page        = 1;

    public $sort        = 'best';

    public $amp         = false;

    // sub class
    protected $jcm_user = null;

    protected $jcm_cmid = null;

    public function getComments($loop = false)
    {
        $this->jcm_user = JFactory::getUser();
        $app            = JFactory::getApplication ();
        $session        = JFactory::getSession();
        $config         = JLexCommentHelper::getConfig();
        $query          = $this->_db->getQuery(true);
        $now            = JFactory::getDate()->toSql();
        $ip_address     = JLexCommentHelper::ip_address();
        $format         = $app->input->getCmd ("format", "html");
        
        $this->indent_root = $config->get('child_style', '1')==='1';

        if ($format=="feed")
        {
            $limit = $loop?0:intval($config->get('rss_length',20));
        } else {
            $limit_reply = $config->get('reply_in_cm_more',5) * 1;
            $limit_page  = $config->get('cms_in_page', 20) * 1;

            if ($loop || $this->parent_id>0)
            {
                $limit = $limit_reply;
                $this->sort = $config->get('sort_child', 'desc');
            } else {
                $limit = $limit_page;
            }
        }

        $response = new stdClass();
        $response->page     = $this->page;
        $response->limit    = $limit;
        $response->pid      = $this->parent_id;
        $response->pagination = null;

        // get object comment
        if($loop==false)
        {
            if($config->get('cm_link',0)==2)
            {
                $sync = CommentHelperAdmin::getSync($this->com_name, $this->com_key);
                $sync->action("entry_details");
            }

            // Only work for root comments
            $objectRow = $this->getTable("Object", "TableCm");
            $objectRow->load(array(
                    "com_name" => $this->com_name,
                    "com_key"  => $this->com_key
                ));

            if(!$objectRow->id)
            {
                if(empty($this->com_url))
                {
                    if($config->get("cm_link",0)==1)
                    {
                        $up = CommentHelperAdmin::getObjDetail($this->com_name, $this->com_key,'', $_SERVER["REQUEST_URI"]);  
                        $this->com_url = str_replace(JUri::root (true), "", $up->url);
                        
                        if(preg_match('/^\s*$/', $this->com_title))
                        {
                            $this->com_title = $up->title;
                        }
                    } else {
                        $this->com_url = str_replace(JUri::root (true), "", $_SERVER["REQUEST_URI"]);
                    }

                    $this->com_url = ltrim($this->com_url, "/");
                }

                if(empty($this->com_title))
                {
                    $this->com_title = JFactory::getDocument()->getTitle();
                }

                // no comment
                $objectRow->bind(array(
                    "com_name" => $this->com_name,
                    "com_key"  => $this->com_key,
                    "com_id"   => is_numeric($this->com_key) ? $this->com_key*1 : 0,
                    "url"      => $this->com_url,
                    "created_time" => $now,
                    "title"    => $this->com_title
                ));

                if($objectRow->store())
                {
                    $this->obj_id = $objectRow->id;

                    // sync - author follow
                    $sync = CommentHelperAdmin::getSync($this->com_name, $this->com_key);
                    $sync->action("author_follow", $this->obj_id);
                }
                
                $objectRow->set("live", 0);

                $response->object = $objectRow;
                $response->comments = null;
                $response->total    = 0;

                return $response;
            } else {
                if($config->get('cm_link',0)==1)
                {
                    $up = CommentHelperAdmin::getObjDetail($this->com_name, $this->com_key, $objectRow->title, $objectRow->url);  
                    $up->url = str_replace(JUri::root (true), "", $up->url);
                    $objectRow->url = ltrim($up->url, "/");
                }

                if(!$this->jcm_user->guest && !$config->get('nof_alert',0))
                {
                    $query->clear()
                          ->update("#__jlexcomment_subscribe")
                          ->set("point=".$this->_db->quote(JFactory::getDate()->toUnix()))
                          ->where([
                                "obj_id=".$this->_db->quote($objectRow->id),
                                "created_by=".$this->_db->quote($this->jcm_user->id)
                            ]);

                    $this->_db->setQuery($query)->execute();
                }
            }

            // assign obj_id
            $this->obj_id = $objectRow->id;
            $objectRow->params = @json_decode($objectRow->params);

            if($objectRow->params!=null)
            {
                if(@$objectRow->params->live==1)
                    $objectRow->set("live", $objectRow->params->live_refresh);

                $objectRow->set("readonly", isset($objectRow->params->readonly)?$objectRow->params->readonly:0);
            }

            $response->object = $objectRow;

            // highlight comment
            if($this->comment_id>0)
            {
                $extraWhere = array('t.obj_id='.$this->obj_id);
                $this->jcm_cmid = $this->_findId($this->comment_id, $extraWhere);
            }
        }

        if(is_array($this->jcm_cmid) && array_key_exists('x'.$this->parent_id, $this->jcm_cmid))
        {
            $pagination = new JCmPagination( $this->jcm_cmid['x'.$this->parent_id], $limit );
        } else {
            $pagination = new JCmPagination( $this->page, $limit );
        }

        $pagination->set ('parent_id', $this->parent_id);
        $pagination->set ('total', 0);

        $whereClauses = array (
                'cm.obj_id=' . $this->obj_id
            );

        if($this->comment_id>0) $config->set("filter_language", 0);

        if($config->get("filter_language",0))
        {
            $whereClauses[]="cm.language=".$this->_db->quote(JFactory::getLanguage()->getTag());
        }
        
        // IMPORTANT FOR CACHING
        $latest_cm = $session->get('latest_cm',15);
        if($latest_cm>0 && $this->timestamp_offset>0)
        {
            // the comment just posted - show notification anyone
            $whereClauses[] = '(cm.published=1 OR cm.id='. intval($latest_cm) .')';
            $session->set('latest_cm',0);
        } else {
            $SUC = $config->get('show_unpublished_cm',2); // show unpublished comment
            $SUC_Condition = 'cm.published=1';
            if($SUC!=1)
            {
                if($config->get("u_state_any_comment",false)==true)
                {
                    $SUC_Condition = '';
                } elseif ($this->jcm_user->guest && $SUC==2) {
                    $SUC_Condition = '(cm.published=1 OR (cm.created_by=0 AND cm.ip_address=' . $this->_db->quote($ip_address). '))';
                } elseif (!$this->jcm_user->guest) {
                    $SUC_Condition = '(cm.published=1 OR cm.created_by='. $this->_db->quote($this->jcm_user->id) .')';
                }
            }

            if($SUC_Condition!='') $whereClauses[]=$SUC_Condition;
        }
        // END.

        $whereClauses[] = "cm.".($this->indent_root?'root_parent_id':'parent_id')."=".$this->parent_id;

        if ( $this->timestamp>0 )
        {
            $whereClauses[] = 'cm.created_time<=' . $this->getDbo()->quote( JFactory::getDate($this->timestamp)->toSql() );
        } else {
            $whereClauses[] = 'cm.created_time<=' . $this->getDbo()->quote( $now );
        }

        if ( $this->timestamp_offset > 0)
        {
            $whereClauses[] = 'cm.created_time>=' . $this->getDbo()->quote( JFactory::getDate($this->timestamp_offset)->toSql() );
        }

        $query->clear()
              ->select('SQL_CALC_FOUND_ROWS cm.*')
              ->from('#__jlexcomment AS cm')
              ->select('u.username,u.name screen_name, u.email user_email')
              ->leftJoin('#__users AS u ON cm.created_by=u.id')
              ->where($whereClauses);

        // is online
        $query->select('0 isOnline');
              // It will make site can slow so do not use it.
              // ->leftJoin('#__session AS ss ON (cm.created_by=ss.userid AND ss.client_id=0)');

        // voting table
        if ( $config->get('jcm_layout_vote',1) == 1 )
        {
            $query->select('IF(vote.point=NULL,0,vote.point*1) voted')
              ->leftJoin ('#__jlexcomment_vote AS vote ON (vote.comment_id=cm.id AND vote.' .$this->_conditions(). ')');
        }
        
        // reporting table
        if ( $config->get('u_report_comment',1) == 1)
        {
            $query->select('IF(report.id>0,1,0) reported')
                  ->leftJoin ('#__jlexcomment_report AS report ON (report.comment_id=cm.id AND report.' .$this->_conditions(). ')');
        }

        // sticker table
        $query->select ('sticker.path2file sk_path2file')
              ->leftJoin ('#__jlexcomment_sticker AS sticker ON(sticker.id=cm.sticker_id AND sticker.published=1) ');

        // parent comment
        $query->select('cm2.guest_name AS guest_name2, cm2.created_by AS created_by2')
              ->leftJoin('#__jlexcomment cm2 ON cm.parent_id=cm2.id');

        // highlight featured comment
        $query->order('cm.featured DESC');
        $query->group('cm.id');

        switch ( $this->sort ) {
            case 'best':
                if ($config->get('jcm_reaction',0)==0)
                {
                    $query->order ('(cm.up_point-cm.down_point) DESC, cm.child_count DESC, cm.id DESC');
                } else {
                    $query->order ('cm.reaction_count DESC, cm.child_count DESC, cm.id DESC');
                }
                
                break;

            case 'popular':
                $query->order ('cm.child_count DESC,cm.created_time DESC');
                break;

            case 'desc':
                $query->order ('cm.created_time DESC');
                break;

            default:
                $query->order ('cm.created_time ASC');
                break;
        }
        
        $response->comments = $this->getDbo()->setQuery($query, $pagination->get('offset'), $pagination->get('limit') )->loadObjectList();
        $response->total    = $this->getDbo()
                                    ->setQuery("SELECT FOUND_ROWS()")
                                    ->loadResult();

        if (! $response->comments) return $response;

        $pagination->set('total', $response->total);

        $response->pagination = $pagination;

        foreach ( $response->comments as $k=>&$comment ) {
            $comment->childs = null;
            if ($comment->child_count>0) {
                $this->comment_id = 0;
                $this->parent_id = $comment->id;
                $this->page = 1;

                $comment->childs = $this->getComments( true );

                // sometimes, some comment not published.
                // so we need update again child_count
                $comment->child_count = count($comment->childs->comments);
            }

            if ($config->get('jcm_reaction',0)==0)
            {
                $comment->point = $comment->up_point - $comment->down_point;
            } else {
                $comment->point = $comment->reaction_count;
                $comment->reaction = new stdClass();
                $comment->reaction->set     = $comment->voted;
                $comment->reaction->list = !empty($comment->reaction_data)?json_decode($comment->reaction_data):[];
            }

            $this->_formatComment($comment);
        }

        return $response;
    }

    private function _findId($cm_id, $extraWhere = array() )
    {
        $prefix = $this->indent_root?'root_parent_id':'parent_id';
        // check to see it is top comment or child comment
        $query = $this->_db->getQuery(true);
        $query->select($prefix)
              ->from('#__jlexcomment')
              ->where('id='.$this->_db->quote($cm_id));

        $parentId = $this->_db->setQuery($query)->loadResult();
        if(is_null($parentId)) return null;

        $config = JLexCommentHelper::getConfig();
        $sortBy = 't.featured DESC,';
        $sortCmd = !$parentId?$config->get('sort','best'):$config->get('sort_child', 'desc');
        
        switch($sortCmd)
        {
            case 'best':
                $sortBy .= '(t.up_point-t.down_point) DESC, t.child_count DESC, t.id DESC';
                break;

            case 'popular':
                $sortBy .= 't.child_count DESC,t.created_time DESC';
                break;

            case 'desc':
                $sortBy .= 't.created_time DESC';
                break;

            default:
                $sortBy .= 't.created_time ASC';
                break;
        }

        $whereClauses = count($extraWhere)>0 ? implode(" AND ", $extraWhere) : "";

        // find offset
        $query = "
            SELECT cm.id, 
                   cm.position,
                   cm.{$prefix} AS parent_id,
                   cm.published
              FROM (SELECT t.id,
                           t.{$prefix},
                           t.published,
                           @rownum := @rownum + 1 AS position
                      FROM #__jlexcomment t
                      JOIN (SELECT @rownum := 0) r
                      WHERE t.{$prefix} = (SELECT {$prefix} FROM #__jlexcomment WHERE id={$cm_id})
                      ".($whereClauses!=""?" AND ".$whereClauses:"")."
                  ORDER BY {$sortBy}) cm
             WHERE cm.id = {$cm_id}
        ";
        
        $result = $this->_db->setQuery($query)->loadObject();

        if(!$result) 
            return null;

        $limit = intval(!$result->parent_id?$config->get('cms_in_page',20):$config->get('reply_in_cm_more',5));
        $roundpage = intval($result->position/$limit);
        $page = $result->position/$limit>$roundpage ? $roundpage+1:$roundpage ;

        // convert to page
        $data = [];
        $data['x'.$result->parent_id] = $page;

        if ($result->parent_id>0)
        {
            $data2=$this->_findId($result->parent_id, $extraWhere);
            if($data2!=null) $data = array_merge($data, $data2);
        }

        return $data;
    }

    public function getStyles()
    {
        $query = $this->_db->getQuery(true);
        
        $query->select('id,css')
              ->from('#__jlexcomment_style')
              ->where('published=1');

        $styles = $this->_db->setQuery($query)->loadObjectList('id');

        if($styles)
        {
            foreach($styles as $k=>&$style)
            {
                $style->css = preg_replace('/\n|\r/', '', $style->css);
            }
        }

        return $styles;
    }

    public $id = 0;

    public function getComment()
    {
        $this->jcm_user = JFactory::getUser();
        $query = $this->getDbo()->getQuery (true);
        $now = JFactory::getDate()->toSql();

        $ip_address = JLexCommentHelper::ip_address();

        if ($this->id < 1)
        {
            $this->setError (JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        $whereClauses = array (
                'cm.id='. $this->id
            );

        $query->select ('SQL_CALC_FOUND_ROWS cm.*')
              ->from ('#__jlexcomment AS cm')
              ->select('u.username,u.name screen_name, u.email user_email')
              ->leftJoin ('#__users AS u ON u.id=cm.created_by')
              ->where ($whereClauses);

        // sticker table
        $query->select ('sticker.path2file sk_path2file')
              ->leftJoin ('#__jlexcomment_sticker AS sticker ON(sticker.id=cm.sticker_id AND sticker.published=1) ');
        
        $comment = $this->getDbo()->setQuery ($query,0,1)->loadObject();

        if (! $comment)
        {
            $this->setError (JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        if (! $comment)
        {
            $this->setError (JText::_("JCM_COMMENT_NOT_FOUND"));
            return false;
        }

        $this->_formatComment ($comment);

        return $comment;
    }

    public function _formatComment(&$comment)
    {
        $config     = JLexCommentHelper::getConfig();
        $email      = $comment->created_by>0 ? $comment->user_email : $comment->guest_email;
        $profile    = JLexCommentHelper::getProfile();
        $profile    = $profile->getUser($comment->created_by, $email);
        $ip_address = JLexCommentHelper::ip_address();

        // process comment
        $comment->params = !empty($comment->params)?json_decode($comment->params):[];
        $isHtml = preg_match("/<[^<]+>/", $comment->comment)!=0;

        // replacer
        if($config->get('replacer',1)==1)
        {
            $replaceClause = $this->_getReplaceClauses ();
            if ($replaceClause)
            {
                $usergroups = $this->_getUserGroups ($comment->created_by);
                foreach ($replaceClause as $rClause)
                {
                    if (count(array_intersect($usergroups, $rClause->group_cid))>0)
                    {
                        $comment->comment = preg_replace ($rClause->regexClause, $rClause->replaceClause, $comment->comment);
                    }
                }
            }
        }


        if(!$isHtml) $comment->comment=nl2br($comment->comment);

        // filter words
        $bad_words = $config->get('bad_words','');
        if($config->get('filter_bad_word',1)==1 && !empty($bad_words) && $config->get('bw_next',0)==0)
        {
            $bad_words = explode ("," , $bad_words);

            if(count($bad_words))
            {
                foreach($bad_words as $word)
                {
                    $word = trim ($word);
                    $word_encode = '';
                    if ( $word== '') continue;

                    for ($i=0; $i<strlen($word) ; $i++)
                    {
                        $word_encode.= $i%2==0 ? $word[$i] : '*';
                    }

                    $comment->comment = preg_replace ("/".preg_quote($word)."/i", '<span class="jcm-bad-word">'.$word_encode.'</span>', $comment->comment);
                }
            }
        }

        if($comment->sk_path2file!=null)
        {
            $comment->sticker = JUri::root(true) . "/" . $comment->sk_path2file;
            $comment->comment .= "
                <div class=\"jcm_sticker\">
                    <img src=\"{$comment->sticker}\" />
                    <span class=\"jcm_sticker_holder\"></span>
                </div>
            ";
        }

        // remote link
        if($config->get('format_link',1)==1)
        {
            // && !$isHtml
            $url_regex = '~\".*?\"(*SKIP)(*F)|<a[^<]*>.*?<\/a>(*SKIP)(*F)|(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~mi'; 
            $comment->comment = preg_replace_callback($url_regex, function($matches){
                $url = (preg_match('/^https?:\/\//', $matches[0])?'':'https://').$matches[0];

                return '<a href="'.$url.'" target="_blank" rel="nofollow">'.$matches[0].'</a>';
            }, $comment->comment);
        }

        // mention
        $type = $config->get('author_name','screen_name')=='screen_name'?1:0;
        $comment->comment = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches) use($type) {
            $name = $this->_name_preview($matches[1], $type);
            if (!$name)
            {
                $name = $matches[2];
            } else {
                if ($type==0)
                {
                    $name = '@'.$name;
                }
            }

            return '<a class="jcm-mention-html jcm-user-cm" data-id="'.$matches[1].'">'.$name.'</a>';
        }, $comment->comment);

        // giphy
        if(!empty($comment->giphy_id) && preg_match('/^([A-z0-9]+),([1-9][0-9]*),([1-9][0-9]*)$/', $comment->giphy_id, $giphy))
        {
            $giphyHeight = intval($giphy[3]*100/$giphy[2]);
            $comment->comment .= "
                <div class=\"giphy-item\">
                    <div style=\"width:100%;height:0;padding-bottom:{$giphyHeight}%;position:relative;\"><iframe src=\"https://giphy.com/embed/{$giphy[1]}\" width=\"100%\" height=\"100%\" style=\"position:absolute\" frameBorder=\"0\" class=\"giphy-embed\" allowFullScreen></iframe></div>
                    <div class=\"gif-overlay\"></div>
                </div>
            ";
        }

        // link to author
        if (!$comment->created_by)
        {
            $comment->author_name = $comment->guest_name;
            $comment->me = $comment->ip_address==$ip_address&&$this->jcm_user->id==0 ? true : false;
        } else {
            $comment->author_name = $config->get('author_name','screen_name')=='screen_name' ? $comment->screen_name : $comment->username;
            $comment->me = $comment->created_by==$this->jcm_user->id ? true : false;
        }

        $comment->author_thumb = $profile ['thumb'];
        $comment->author_link = $profile ['link'];

        // parent comment
        if($comment->parent_id>0 && isset($comment->created_by2))
        {
            $comment->author_name2 = $comment->guest_name2;
            if($comment->created_by2>0)
            {
                $uParent = JFactory::getUser($comment->created_by2);
                $comment->author_name2 = $config->get('author_name','screen_name')=='screen_name' ? $uParent->name : $uParent->username;
            }
        }

        // media
        $comment->media = $this->_getMedia($comment->id);

        // roles
        if ( $config->get('jcm_layout_role',1) == 1)
        {
            $comment->roles = $this->_getRoles ($comment->created_by);
        }

        // date
        $comment->created_og = $comment->created_time;
        $format_date = $config->get("jcm_date_format","sub")=="sub" ? null : $config->get("jcm_date_format");
        $comment->created_time = JLexCommentHelper::formatTime ($comment->created_time, null, $format_date, ($config->get("jcm_date_tz",1)==1?true:false));

        unset($comment->username);
        unset($comment->screen_name);
        unset($comment->guest_name);
    }

    // mention
    protected $names = array ();
    
    protected function _name_preview($uid, $type=1)
    {
        $uid = intval($uid);
        if ($uid<1) return false;

        if (array_key_exists($uid, $this->names))
        {
            return $this->names[$uid];
        }

        $query = 'SELECT '.($type==1?'name':'username').' FROM #__users WHERE id=' . $uid;
        $result = $this->_db->setQuery($query)->loadResult();

        $this->names[$uid] = $result;

        return $result;
    }

    // replacer -->
    protected $userGroups = array ();

    private function _getUserGroups ($user_id)
    {
        if ( !$user_id ) return array (1); // guest: Public group :-)

        if ( array_key_exists($user_id, $this->userGroups) )
        {
            return $this->userGroups [ $user_id ];
        }

        $query = $this->getDbo()->getQuery (true);
        $query->select ("group_id")
              ->from ("#__user_usergroup_map")
              ->where ("user_id = " . $user_id );

        $this->userGroups [$user_id] = $this->getDbo()->setQuery ($query)->loadColumn ();

        return $this->userGroups [$user_id];
    }

    private function _getReplaceClauses ()
    {
        static $clauses = false;

        if ($clauses===false)
        {
            $query = $this->getDbo()->getQuery (true);
            $query->select ("regexClause,replaceClause,group_cid")
                  ->from ("#__jlexcomment_replacer")
                  ->where ("published = 1" );

            $clauses = $this->getDbo()->setQuery ($query)->loadObjectList ();

            if ($clauses)
            {
                foreach ($clauses as $k=>&$clause)
                {
                    $clause->group_cid = explode (",", $clause->group_cid);
                }
            }
        }
        
        return $clauses;
    }

    // <-- end replacer

    protected $usersRoles = array ();

    private function _getRoles ( $user_id )
    {
        if ( !$user_id ) return null; // guest. :-)

        if ( array_key_exists($user_id, $this->usersRoles) )
        {
            return $this->usersRoles [ $user_id ];
        }

        $query = $this->getDbo()->getQuery (true);
        $query->select ("role.title, role.colour")
              ->from ("#__user_usergroup_map gm")
              ->innerJoin ("#__jlexcomment_roles as role ON gm.group_id=role.group_id")
              ->where ("gm.user_id = " . $user_id . " AND role.published=1");

        $this->usersRoles [$user_id] = $this->getDbo()->setQuery ($query)->loadObjectList ();

        return $this->usersRoles [$user_id];
    }

    private function _getMedia ( $comment_id )
    {
        $query = $this->getDbo()->getQuery (true);
        $query->select ("*")
              ->from ("#__jlexcomment_media")
              ->where ("comment_id=" . $comment_id);

        $rows = $this->getDbo()->setQuery ($query)->loadObjectList();

        if (! $rows)
        {
            return null;
        }

        $return = new stdClass();
        $return->images = array();
        $return->files   = array();

        foreach ($rows as $k=>&$row)
        {
            if ($row->fileType == 'image')
            {
                $row->root  = JUri::base (true) . '/' . $row->path;
                $row->thumb = JUri::base (true) . '/' . str_replace('og/', 'thumb/', $row->path);

                // check image has resized or not
                $rz_path = JPATH_ROOT.'/'.str_replace('og/', 'rz/', $row->path);
                if(is_file($rz_path))
                {
                    $row->root = JUri::base (true) . '/' . str_replace('og/', 'rz/', $row->path);
                }

                $row->media = '';
                $row->html  = '<h4>' . $row->name . '</h4>';

                if ( !preg_match("/^\s*$/", $row->description) )
                {
                    $row->html .= '<p>' . $row->description . '</p>';
                }

                $return->images[] = $row;
            } else {
                // format file size.
                if ($row->fileSize >= 1000000000) {
                    $row->fileSize = number_format($row->fileSize / 1000000000, 2) . ' Gb';
                } elseif ($row->fileSize >= 1000000) {
                    $row->fileSize = number_format($row->fileSize / 1000000, 2) . ' Mb';
                } else {
                    $row->fileSize = number_format($row->fileSize / 1000) . ' Kb';
                }

                $row->url2download = JRoute::_( 'index.php?option=com_jlexcomment&task=media.download&id=' . $row->id, false );

                $return->files[] = $row;
            }
        }

        return $return;
    }

    private function _conditions()
    {
        $user = JFactory::getUser ();

        $ip_address = JLexCommentHelper::ip_address();
        $conditions = $user->guest ? "ip_address=".$this->getDbo()->quote($ip_address) : "created_by=" . $user->id;
        
        return $conditions;
    }

    protected $obj_id = 0;

    public function getUser($uid = null)
    {
        $user = JFactory::getUser($uid);
        $profile = JLexCommentHelper::getProfile ();
        $profile = $profile->getUser ($user->id);

        $user->author_thumb = $profile ['thumb'];
        $user->author_link = $profile ['link'];

        if ( $user->id>0 && $this->obj_id>0 )
        {
            // check user subscribe
            $query = "SELECT COUNT(*) FROM #__jlexcomment_subscribe";
                $query.= "\nWHERE published=1 AND obj_id={$this->obj_id} AND created_by=" . $user->id;

            $return = $this->getDbo()->setQuery ($query)->loadResult ();

            $user->set ('jcm_subscribe', $return>0 ? true : false);
        }

        return $user;
    }

    protected $numberMoreUser = 0;

    public function getPeoples ()
    {
        if (! $this->obj_id)
        {
            return array ();
        }

        $user   = JFactory::getUser ();
        $query  = $this->getDbo ()->getQuery (true);
        $profileClass = JLexCommentHelper::getProfile ();
        $limit  = 20;

        $whereClauses = array (
                "cm.created_by > 0",
                "cm.published=1",
                "cm.obj_id=" . $this->obj_id
            );

        $query->select ("SQL_CALC_FOUND_ROWS u.id, u.name, u.username, u.email")
              ->from ("#__jlexcomment cm")
              ->innerJoin ("#__users u ON cm.created_by=u.id")
              ->where ( $whereClauses )
              ->group ("cm.created_by")
              ->order ("cm.created_time DESC");

        $users = $this->getDbo()->setQuery ($query,0,$limit)->loadObjectList ();
        $total = $this->getDbo()->setQuery("SELECT FOUND_ROWS()")
                                    ->loadResult();

        $this->numberMoreUser = $total <= $limit ? 0 : $total - $limit;

        if ($users)
        {
            foreach ($users as $k=>&$user)
            {
                $profile = $profileClass->getUser ($user->id, $user->email);
                $user->thumb = $profile ['thumb'];
                $user->link  = $profile ['link'];
                unset( $user->email );
            }
        } else {
            $users = array ();
        }

        return $users;
    }

    public function getMorePeople()
    {
        return $this->numberMoreUser;
    }

    public function getTotalComment ()
    {
        $config = JLexCommentHelper::getConfig ();

        if ( $config->get('count_cm',0)==0 )
        {
            $column = $this->_db->quoteName("cm_i_count_active");
        } else {
            $column = $this->_db->quoteName("cm_count_active");
        }

        $query = "SELECT {$column} FROM #__jlexcomment_obj WHERE id={$this->obj_id} AND published=1";

        $total = $this->getDbo()->setQuery ($query,0,1)->loadResult();

        return !$total ? 0 : $total*1;
    }

    public function getCacheUnique()
    {
        if($this->comment_id>0||($this->timestamp>0&&$this->timestamp_offset>0))
        {
            return false;
        }

        $keys = array(
                $this->com_name,
                $this->com_key,
                $this->page,
                $this->parent_id,
                $this->sort
            );

        return implode(',', $keys);
    }

    public function getCacheTurnOn()
    {
        $config     = JLexCommentHelper::getConfig();
        $session    = JFactory::getSession();
        $latest_cm  = $session->get('latest_cm',0);
        $user       = JFactory::getUser();

        $cacheTurnOn = false;

        if($config->get('joomla_cache',false)==true && $config->get('cache',1)==1 && $user->guest)
        {
            if($this->comment_id<1&&$latest_cm<1)
            {
                $cacheTurnOn = true;
            }
        }

        return $cacheTurnOn;
    }
}
