<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelReporting extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getReports()
    {
        $app = JFactory::getApplication();
        $whereClauses = array();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS r.*")
              ->from ("#__jlexcomment_report r")

              ->select ("cm.comment, cm.created_by comment_author_id, cm.ip_address comment_ip_address")
              ->select ("cm.guest_email comment_guest_email")
              ->innerJoin ("#__jlexcomment cm ON r.comment_id=cm.id")

              ->select ("u.name AS author")
              ->leftJoin ("#__users AS u ON r.created_by=u.id")

              // blacklist
              ->select ("IF(b1.id>0,1,0) block_member")
              ->leftJoin ("#__jlexcomment_blacklist b1 ON (cm.created_by=b1.method_value AND b1.method=1)")

              ->select ("IF(b2.id>0,1,0) block_email")
              ->leftJoin ("#__jlexcomment_blacklist b2 ON (cm.guest_email=b2.method_value AND b2.method=2)")

              ->select ("IF(b3.id>0,1,0) block_ip")
              ->leftJoin ("#__jlexcomment_blacklist b3 ON (cm.ip_address=b3.method_value AND b3.method=3)")

              ->group ("r.id");

        // where clauses
        $keywords   = $app->getUserStateFromRequest ( "jcm.rep.query", "filter_search", "");
       
        $this->setState('jcm.rep.query', $keywords);

        if (!preg_match("/^\s*$/", $keywords))
        {
            $keywords = $this->getDbo()->quote("%" . $keywords . "%");
            $orClauses = array (
                    'r.reason_msg LIKE ' . $keywords,
                    'r.ip_address LIKE ' . $keywords,
                    'r.guest_name LIKE ' . $keywords,
                    'r.guest_email LIKE ' . $keywords
                );

            $whereClauses[] = "(" . implode(" OR ", $orClauses) . ")";
        }

        if (count($whereClauses) > 0)
        {
            $query->where ($whereClauses);
        }

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.rep.sortby", "filter_order", "r.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.rep.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "r.created_time",
                "r.comment_id",
                "r.created_by",
                "r.ip_address"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.rep.sortby', $sortBy);
        $this->setState('jcm.rep.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.rep.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.rep.limit", 'limit', $this->limit, 20, "int" );

        $reports = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $reports)
        {
            return null;
        }

        foreach ($reports as $k=>&$report)
        {
            if (! empty($report->comment))
            {
                $report->comment = preg_replace_callback('/\{u-([1-9][0-9]*),(.*?)\}/', function($matches){
                    return '<span class="jcm-mention-html" data-id="'.$matches[1].'">@'.$matches[2].'</span>';
                }, $report->comment);

                $report->comment = substr ($report->comment, 0, 20) . "...";
            } else {
                $report->comment = '[Sticker]';
            }
            $report->url2comment = JUri::base (true) . "/index.php?option=com_jlexcomment&view=comments&hl=" . $report->comment_id;
            $report->url2author = JUri::base (true) . "/index.php?option=com_users&view=user&layout=edit&id=" . $report->created_by;    
            $report->block_user = $report->block_member==1 || $report->block_email==1 ? true : false;
        
            $report->url2add_ip = JUri::base (true) . "/index.php?option=com_jlexcomment&task=reporting.add2blacklist&method=3&ip_address=" . $report->comment_ip_address;
            if ( $report->comment_author_id >0 )
            {
                $report->url2add_member = JUri::base (true) . "/index.php?option=com_jlexcomment&task=reporting.add2blacklist&method=1&uid=" . $report->comment_author_id;
            } else {
                $report->url2add_member = JUri::base (true) . "/index.php?option=com_jlexcomment&task=reporting.add2blacklist&method=2&email=" . $report->comment_guest_email;
            }
        }

        return $reports;
    }

    public function getPageNav() {
        jimport ( 'joomla.html.pagination' );
        return new JPagination ( $this->total, $this->limitstart, $this->limit );
    }

    public function add2blacklist ()
    {
        $app = JFactory::getApplication ();
        $method = $app->input->getInt ("method", 1);
        $data = array ();

        switch ($method)
        {
            case 1:
                $uid = $app->input->getInt ("uid", 0);
                if ($uid < 1)
                {
                    $this->setError (JText::_("JCM_USER_NOT_FOUND"));
                    return false;
                }
                $data ["method"] = 1;
                $data ["method_value"] = $uid;
                break;

            case 2:
                $email = $app->input->getString ("email", "");
                if ( !filter_var ($email, FILTER_VALIDATE_EMAIL))
                {
                    $this->setError (JText::_("JCM_EMAIL_INCORRECT"));
                    return false;
                }
                $data ["method"] = 2;
                $data ["method_value"] = $email;
                break;

            case 3:
                $ip_address = $app->input->getString ("ip_address", "");
                if ( preg_match("/^\s*$/", $ip_address))
                {
                    $this->setError (JText::_("JCM_IP_ADDRESS_NOT_EMPTY"));
                    return false;
                }
                $data ["method"] = 3;
                $data ["method_value"] = $ip_address;
                break;
        }

        $data ["created_by"] = JFactory::getUser ()->id;
        $data ["created_time"] = JFactory::getDate ()->toSql ();

        $row = $this->getTable ("blacklist", "TableCm");
        $row->bind ($data);

        $subQuery = "SELECT COUNT(*) FROM #__jlexcomment_blacklist WHERE ";
            $subQuery .= "\nmethod=" . $row->method;
            $subQuery .= " AND method_value=" . $this->getDbo()->quote ($row->method_value);

        $return = $this->getDbo()->setQuery ($subQuery)->loadResult ();

        if ($return>0)
        {
            return true;
        }
        
        if (! $row->store())
        {
            $this->setError (JText::_("JCM_APPEAR_ERROR_WHEN_SAVING_YOUR_DATE_TRY_LATER"));
            return false;
        }

        return true;
    }

    public function remove()
    {   
        $app    = JFactory::getApplication();
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
            return false;
        }

        $state = $this->state > 0 ? 1 : 0;

        $query = "DELETE FROM #__jlexcomment_report";
            $query.= "\nWHERE id IN (" .implode(",", $cid). ")";

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }
}
