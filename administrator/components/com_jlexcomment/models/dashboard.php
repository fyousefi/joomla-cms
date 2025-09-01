<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelDashboard extends JModelLegacy
{
    public $period = 7; // by days

    public function statistics ()
    {
        // comments
        // reporting
        // subscriber
        $app = JFactory::getApplication();
        $date_format = "%e-%c-%y";
        $date_table  = "SELECT CURDATE() as day";

        if ( is_numeric($this->period) )
        {
            // by day(s)
            for ($i = 0; $i < $this->period; $i ++)
            {
                $date_table .= "\nUNION SELECT CURDATE() - INTERVAL {$i} day";
            }
        } else {
            // by year
            $date_format = "%c-%y";
            for ($i = 0; $i < 12; $i ++)
            {
                $date_table .= "\nUNION SELECT CURDATE() - INTERVAL {$i} month";
            }
        }

        $query = $this->getDbo()->getQuery (true);
        $query->select ( "DATE_FORMAT(date_table.day, '{$date_format}') AS label")
              ->from ("({$date_table}) AS date_table");

        // comment table
        $comment_table = "(SELECT COUNT(*) comment,DATE_FORMAT(created_time,'{$date_format}') label FROM #__jlexcomment GROUP BY label)";
        $query->select ("IF(cm.comment, cm.comment, 0) comment")
              ->leftJoin ("{$comment_table} cm ON DATE_FORMAT(date_table.day,'{$date_format}')=cm.label");

        // reporting table
        $reporting_table = "(SELECT COUNT(*) report,DATE_FORMAT(created_time,'{$date_format}') label FROM #__jlexcomment_report GROUP BY label)";
        $query->select ("IF(r.report, r.report, 0) report")
              ->leftJoin ("{$reporting_table} r ON DATE_FORMAT(date_table.day,'{$date_format}')=r.label");

        // subscriber table
        $sub_table = "(SELECT COUNT(*) memberx,DATE_FORMAT(created_time,'{$date_format}') label FROM #__jlexcomment_subscribe GROUP BY label)";
        $query->select ("IF(sub.memberx, sub.memberx, 0) subscriber")
              ->leftJoin ("{$sub_table} sub ON DATE_FORMAT(date_table.day,'{$date_format}')=sub.label");

        $query->group ("date_table.day");
        
        $return = $this->getDbo()
                        ->setQuery($query)
                        ->loadRowList();

        foreach ($return as $i => $row)
        {
            foreach ($row as $j => $v)
            {
                if (! $j)
                    continue;
                $return [$i][$j] += 0;
            }
        }
        
        array_unshift ($return, array(
            "Date",
            "Comment",
            "Reporting",
            "Subscriber"
        ));
        
        return $return;    
    }

    public function others ()
    {
        $now = JFactory::getDate()->toSql ();
        $config = JLexCommentHelper::getConfig ();

        $data = new stdClass ();
        $data->latest_comments = array ();
        $data->top_items = array ();
        $data->new_items = array ();
        $data->total_comments = 0;

        if ( !is_numeric ($this->period) || $this->period<5 )
        {
            $day = 7;
        } else {
            $day = $this->period * 1;
        }

        // latest comments
        $query = $this->getDbo()->getQuery (true);
        $query->select ("cm.*, u.email as author_email, u.username")
              ->from ("#__jlexcomment cm")

              ->leftJoin ("#__users u ON cm.created_by=u.id")

              ->select ("obj.title AS object_name, obj.url AS object_url, obj.com_name, obj.com_key")
              ->innerJoin ("#__jlexcomment_obj obj ON cm.obj_id=obj.id")
              ->where ("cm.created_time >= DATE_SUB('{$now}', INTERVAL {$day} DAY)")
              ->order ("cm.created_time DESC");

        // sticker table
        $query->select ("sticker.path2file sk_path2file")
              ->leftJoin ("#__jlexcomment_sticker AS sticker ON sticker.id=cm.sticker_id");

        $data->latest_comments = $this->getDbo()->setQuery ($query,0,10)->loadObjectList ();

        if ($data->latest_comments)
        {
            foreach ($data->latest_comments as $k=>&$cm)
            {
                // mention
                $cm->comment = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
                    return '<span class="jcm-mention-html" data-id="'.$matches[1].'">@'.$matches[2].'</span>';
                }, $cm->comment);

                if ($cm->sk_path2file!=null)
                {
                    $cm->sticker = JUri::root(true) . "/" . $cm->sk_path2file;
                    $cm->comment .= "
                        <div class=\"jcm_sticker\">
                            <img src=\"{$cm->sticker}\" />
                            <span class=\"jcm_sticker_holder\"></span>
                        </div>
                    ";
                }

                $cm->author = $cm->created_by > 0 ? $cm->username : $cm->guest_name;
                $cm->author_url = null;

                if ($cm->created_by > 0)
                {
                    $cm->author_url = JUri::base (true) . '/index.php?option=com_users&view=users&filter[search]=' . $cm->author_email;
                }

                if($config->get("cm_link",0)==1)
                {
                    $router = CommentHelperAdmin::getObjDetail($cm->com_name, $cm->com_key, $cm->object_name, $cm->object_url);

                    if($router)
                    {
                        $cm->object_name = $router->title;
                        $cm->object_url = $router->url;
                    }
                }

                $cm->object_url = $cm->object_url . (stripos($cm->object_url, '?')!==false?'&':'?') . 'comment_id=' . $cm->id;

                if(!preg_match('/^http/', $cm->object_url))
                    $cm->object_url = JUri::root(true).'/'.trim($cm->object_url, '/');

                unset($cm->username);
                unset($cm->guest_name);
                unset($cm->author_email);
            }
        }

        // top items
        $subTable = "(
                SELECT COUNT(id) AS cm_count, obj_id
                FROM #__jlexcomment
                WHERE created_time >= DATE_SUB('{$now}', INTERVAL {$day} DAY)
                GROUP BY obj_id
            )";

        $query->clear ()
              ->select ("obj.*, cmGroup.cm_count AS cm_count_period")
              ->from ("#__jlexcomment_obj obj")
              ->leftJoin ( $subTable . " AS cmGroup ON obj.id=cmGroup.obj_id" )
              ->order ("cmGroup.cm_count DESC");

        $data->top_items = $this->getDbo()->setQuery ($query, 0, 10)->loadObjectList ();

        if(count($data->top_items))
        {
            foreach ($data->top_items as $k=>&$item)
            {
                if($config->get("cm_link",0)==1)
                {
                    $router = CommentHelperAdmin::getObjDetail($item->com_name, $item->com_key, $item->title, $item->url);

                    if($router)
                    {
                        $item->title = $router->title;
                        $item->url   = $router->url;
                    }
                } else {
                    $item->url = JUri::root(true) . '/' . $item->url;
                }
                
                $item->cm_count_period = !$item->cm_count_period ? 0 : $item->cm_count_period;
            }
        }

        // new items
        $query->clear ()
              ->select ("*")
              ->from ("#__jlexcomment_obj")
              ->where ("created_time >= DATE_SUB('{$now}', INTERVAL {$day} DAY)")
              ->order ("created_time DESC");

        $data->new_items = $this->getDbo()->setQuery ($query, 0, 10)->loadObjectList ();

        if(count($data->new_items))
        {
            foreach ($data->new_items as $k=>&$item)
            {
                if($config->get("cm_link",0)==1)
                {
                    $router = CommentHelperAdmin::getObjDetail($item->com_name, $item->com_key, $item->title, $item->url);

                    if($router)
                    {
                        $item->title = $router->title;
                        $item->url   = $router->url;
                    }
                } else {
                    $item->url = JUri::root(true) . '/' . $item->url;
                }
            }
        }

        // total comment
        $query->clear ()
              ->select ("COUNT(*)")
              ->from ("#__jlexcomment");

        $data->total_comments = $this->getDbo()->setQuery ($query)->loadResult();
        if (!$data->total_comments)
        {
            $data->total_comments = 0;
        } else {
            $data->total_comments = number_format ($data->total_comments);
        }

        return $data;
    }

    public function version ()
    {

    }
}
