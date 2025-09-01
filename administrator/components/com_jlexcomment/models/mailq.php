<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelMailQ extends JModelLegacy
{
    protected $limit = 20;

    protected $limitstart = 0;

    protected $total = 0;

    public function getMailQuery ()
    {
        $app = JFactory::getApplication();

        $query = $this->getDbo()->getQuery(true);
        $query->select ("SQL_CALC_FOUND_ROWS nof.*")
              ->from ("#__jlexcomment_notification nof")

              ->select ("IF(u.name IS NULL,nof.guest_remind_name,u.name) AS rep_name, IF(u.email IS NULL,nof.guest_remind_email,u.email) AS rep_email")
              ->leftJoin ("#__users AS u ON nof.user_remind=u.id");

        // sort by
        $sortBy = $app->getUserStateFromRequest ( "jcm.nof.sortby", "filter_order", "nof.created_time");
        $sortDir = $app->getUserStateFromRequest ( "jcm.nof.sortdir", "filter_order_Dir", "desc");

        // safe
        $sortDir = $sortDir=="desc" ? "desc" : "asc";
        $sortByColumns = array (
                "nof.created_time",
                "rep_name",
                "rep_email",
                "nof.sent"
            );

        if (!in_array($sortBy, $sortByColumns))
        {
            $sortBy = $sortByColumns[0];
        }

        $this->setState('jcm.nof.sortby', $sortBy);
        $this->setState('jcm.nof.sortdir', $sortDir);

        $query->order( $sortBy . " " . $sortDir);

        $this->limitstart = $app->getUserStateFromRequest ( "jcm.nof.limitstart", 'limitstart', 0, "int" );
        $this->limit = $app->getUserStateFromRequest ("jcm.nof.limit", 'limit', $this->limit, 20, "int" );

        $items = $this->getDbo()->setQuery($query, $this->limitstart, $this->limit)->loadObjectList();

        $this->total = $this->getDbo()->setQuery( "SELECT FOUND_ROWS()" )->loadResult ();

        if (! $items)
        {
            return null;
        }

        foreach ($items as $k=>&$item)
        {
            $item->caption = "Unknown";

            switch ($item->action_type)
            {
                case "MENTION":
                    $item->caption = JText::_("JCM_USER_MENTIONED_IN_A_COMMENT");
                    break;

                case "REPLY":
                    $item->caption = JText::_("JCM_SOMEONE_WHO_REPLIED_THEIR_COMMENT");
                    break;
            }

            $item->url = JUri::base (true) . '/index.php?option=com_jlexcomment&view=comments&hl=' . $item->comment_id;
        }

        return $items;
    }

    public function getPageNav() {
        jimport ( 'joomla.html.pagination' );
        return new JPagination ( $this->total, $this->limitstart, $this->limit );
    }

    // for object single
    public $status = 1;

    public function markas ()
    {
        $cid    = array_key_exists('cid', $_POST) ? $_POST['cid'] : null;

        if (! is_array($cid) || ! count($cid)) 
        {
            throw new Exception(JText::_("JCM_NO_ROW_FOUND_TO_TASK"), 500);
            return false;
        }

        $query = "UPDATE #__jlexcomment_notification SET sent={$this->status}";
            $query.= "\nWHERE id IN (" .implode(",", $cid). ")";

        // make query
        $this->_db->setQuery($query)->execute();

        return true;
    }
}
