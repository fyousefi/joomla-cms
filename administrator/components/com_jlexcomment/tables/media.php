<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class TableCmMedia extends JTable
{

    var $id             = null;

    var $comment_id     = 0;

    var $name           = null;

    var $description    = '';

    var $created        = null;

    var $created_by     = null;

    var $path           = null;

    var $fileSize       = null;

    var $fileName       = null;

    var $fileType       = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jlexcomment_media', 'id', $db);
    }


    public function delete($pk=null)
    {
        /*if ($this->id < 1)
        {
            return false;
        }

        // decrease count comment of parent_id
        if ($this->parent_id > 0)
        {
            $query = "UPDATE #__comment SET child_count=child_count-1 WHERE id=".$this->parent_id;
            parent::getDbo()->setQuery($query)->query();
        }

        // delete all voting for this comment
        $query = "DELETE FROM #__comment_vote WHERE comment_id=".$this->id;
        parent::getDbo()->setQuery($query)->query();

        // update point
        JFactory::getPoint($this->created_by)->update(-2,-2);

        return parent::delete($pk);
        */
    }
}