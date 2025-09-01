<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelComments extends JModelLegacy
{
	public $offset = 0;

	public $limit  = 20;

	public $total  = 0;

	public function getComments ()
    {
    	$app = JFactory::getApplication ();
        $query = $this->getDbo()->getQuery (true);
        $config = JLexCommentHelper::getConfig ();

        $whereClauses = array ();

        if ( $app->input->getInt('oid',0) > 0 )
        {
        	$whereClauses [] = "cm.obj_id=" . $app->input->getInt('oid');
        }

        if ( $app->input->getInt('pid',0) > 0 )
        {
        	$whereClauses [] = "cm.parent_id=" . $app->input->getInt('pid');
        }

        if ( $app->input->getInt('hl',0) > 0 )
        {
        	$whereClauses [] = "cm.id=" . $app->input->getInt('hl');
        }

        $keywords   = $app->getUserStateFromRequest ( "jcm.cms.query", "q", "");
        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $orClauses = [
                "cm.comment LIKE $keywords",
                "cm.guest_name LIKE $keywords",
                "cm.guest_email LIKE $keywords",
                "cm.ip_address LIKE $keywords",
                "u.username LIKE $keywords",
                "u.name LIKE $keywords",
                "u.email LIKE $keywords"
            ];

            $whereClauses[] = '('.implode(' OR ', $orClauses).')';
        }

        $query->select ('SQL_CALC_FOUND_ROWS cm.*')
              ->from ('#__jlexcomment AS cm')
              // user table
              ->select ('u.username,u.name screen_name,u.email AS member_email')
              ->leftJoin ('#__users AS u ON u.id=cm.created_by')

              // object table
              ->select ('obj.title object_name, obj.com_name object_type, obj.com_key object_id, obj.url object_url')
              ->innerJoin ('#__jlexcomment_obj obj ON cm.obj_id=obj.id');

        // sticker table
        $query->select ('sticker.path2file sk_path2file')
              ->leftJoin ('#__jlexcomment_sticker AS sticker ON(sticker.id=cm.sticker_id AND sticker.published=1) ');

        // where clauses
        if ( count($whereClauses))
        {
        	$query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.cms.sortby", "filter_order", "cm.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.cms.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "cm.created_time",
                "obj.title",
                "cm.published",
                "cm.featured",
                "cm.id",
                $config->get('jcm_reaction',0)==0?"(cm.up_point-cm.down_point)":"cm.reaction_count",
                "cm.report_count"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.cms.sortby', $sortBy);
        $this->setState('jcm.cms.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->offset = $app->input->getInt ("limitstart", 0);
        $this->limit = $app->input->getInt ("limit", 20);
        
        $comments = $this->getDbo()->setQuery($query, $this->offset, $this->limit )->loadObjectList();
        $this->total    = $this->getDbo()
                                    ->setQuery("SELECT FOUND_ROWS()")
                                    ->loadResult();

        if (! $comments) return null;

        foreach ( $comments as $k=>&$cm ) {
            $cm->point = $config->get('jcm_reaction',0)==0 ? ($cm->up_point - $cm->down_point) : $cm->reaction_count;
            $cm->reaction = $config->get('jcm_reaction',0)==0?false:true;

            if ($cm->parent_id>0)
            {
            	$query->clear ()
            		  ->select ('comment')
            		  ->from ('#__jlexcomment')
            		  ->where ('id='.$cm->parent_id);
            	
            	$comment = $this->getDbo()->setQuery ($query)->loadResult();
            	$cm->parent_comment = null;
            	$cm->parent_comment_url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=comments&hl=' . $cm->parent_id;
            	if ( !empty($comment))
            	{
            		$cm->parent_comment = preg_replace ("/\{u\-[1-9][0-9]*,(.*?)\}/", "<b>@$1</b>", $comment);
					$cm->parent_comment = JLexCommentHelper::subwords ( $cm->parent_comment );
            	}
            }

            if ($config->def("cm_link",0)==1)
            {
                $up = CommentHelperAdmin::getObjDetail($cm->object_type, $cm->object_id,$cm->object_name , $cm->object_url);

                $cm->object_url = $up->url;
                $cm->object_name = $up->title;
                $cm->url2frontend = $cm->object_url . (stripos($cm->object_url, '?')!==false?'&':'?') . 'comment_id=' . $cm->id;
            } else {
                $cm->url2frontend = JUri::root (true) . '/' . $cm->object_url . (stripos($cm->object_url, '?')!==false?'&':'?') . 'comment_id=' . $cm->id;
            }
            
            $cm->url2edit 	= JUri::base (true) . '/index.php?option=com_jlexcomment&view=item&id=' . $cm->id;
            $cm->childUrl 	= JUri::base (true) . '/index.php?option=com_jlexcomment&view=comments&pid=' . $cm->id;
            $cm->objectUrl 	= JUri::base (true) . '/index.php?option=com_jlexcomment&view=comments&oid=' . $cm->obj_id;
            $this->_formatComment($cm);
        }

        return $comments;
    }

    public function getPageNav()
    {
        jimport('joomla.html.pagination');
        return new JPagination($this->total, $this->offset, $this->limit );
    }

    private function _formatComment(&$comment)
    {
        $config = JLexCommentHelper::getConfig();

        // process comment
        $isHtml = preg_match("/<[^<]+>/", $comment->comment)!=0;
        if(!$isHtml) $comment->comment = nl2br ($comment->comment);

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

        // mention
        $comment->comment = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
            return '<a class="jcm-mention-html jcm-user-cm" data-id="'.$matches[1].'">@'.$matches[2].'</a>';
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
        $comment->author_name = !$comment->created_by ? $comment->guest_name : $comment->screen_name;

        // media
        $comment->media = $this->_getMedia ($comment->id);

        unset($comment->username);
        unset($comment->screen_name);
        unset($comment->guest_name);
    }

    private function _getMedia($comment_id)
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
                $row->thumb = JUri::root (true) . '/media/jcm/thumb/' . $row->fileName;
                $row->root  = JUri::root (true) . '/' . $row->path;
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


    public $jcm_state = 1;

	public function state ()
	{
		$row = $this->getTable('Comment', 'TableCm');
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;
        $config  = JLexCommentHelper::getConfig ();

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $result = array(
                'total' => count($cid),
                'success' => 0
            );

        foreach ($cid as $k=>$id)
        {
            $row->set('id', 0); // reset
            $row->load ($id);
            if ($row->do_publish( $this->jcm_state ))
            {
                $result['success']++;
            }
        }

        // clear cache
        if ($config->def("cache",1)==0)
        {
            CommentHelperAdmin::clearCache();
        }

        return $result;
	}

	public function feature ()
	{
		$app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;
        $config  = JLexCommentHelper::getConfig ();

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $state = $this->jcm_state > 0 ? 1 : 0;

        $query = "UPDATE #__jlexcomment SET featured=$state";
            $query.= "\nWHERE id IN (" .implode(",", $cid). ")";

        // make query
        $this->getDbo()->setQuery($query)->execute();

        // clear cache
        if ($config->def("cache",1)==0)
        {
            CommentHelperAdmin::clearCache();
        }

        return true;
	}

	public function remove ()
	{
		$row = $this->getTable('Comment', 'TableCm');
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;
        $config  = JLexCommentHelper::getConfig ();

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
        }

        $result = array(
                'total' => count($cid),
                'success' => 0
            );

        foreach ($cid as $k=>$id)
        {
            $row->set('id', 0); // reset
            $row->load ($id);
            if ($row->delete())
            {
                $result['success']++;
            }
        }

        // clear cache
        if ($config->def("cache",1)==0)
        {
            CommentHelperAdmin::clearCache();
        }

        return $result;
	}
}