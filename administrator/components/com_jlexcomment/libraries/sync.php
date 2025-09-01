<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die;

class JLexCommentSync
{
	protected $_db = null;

    protected $items = [];

    protected $object = null;

    protected $object_id = null;

    public function __construct()
    {
        $this->_db = JFactory::getDbo();
    }

    public function set($object, $object_id)
    {
        $this->object = $object;
        $this->object_id = $object_id;
    }

	public function action()
    {
        $query = $this->_db->getQuery(true);
        
        if(!array_key_exists($this->object, $this->items))
        {
            $query->clear()
                  ->select('*')
                  ->from('#__jlexcomment_sync')
                  ->where(array(
                        'published=1',
                        'object='.$this->_db->quote($this->object)
                    ));

            $item = $this->_db->setQuery($query)->loadObject();
            $this->items[$this->object] = $item?$item:false;

            if(!$item) return;
        }

        if(!$this->items[$this->object]) return;
        $item = $this->items[$this->object];

        $numargs = func_num_args();
        if($numargs<1) return;

        $arg_list = func_get_args();
        $cmd = $arg_list[0];

        // execute command
        switch ($cmd) {
            case 'entry_details':
            case 'entry_updated':
            case 'author_follow':
                if($item->$cmd==1)
                {
                    // enabled
                    $nameOfCmd = 'cb_'.$cmd;
                    try {
                        $code = $item->$nameOfCmd;
                        if(!preg_match("/^[\n\s]*\<\?php/", $code))
                        {
                            $code = "<?php\n".$code;
                        }

                        $tmpfname = tempnam(JPATH_SITE."/tmp", "html");
                        $handle = fopen($tmpfname, "w");
                        fwrite($handle, $code, strlen($code));
                        fclose($handle);

                        // global var
                        $object_id = $this->object_id;
                        if($cmd=='entry_updated')
                        {
                            $comment_count = $arg_list[1];
                            $all_comment_count = $arg_list[2];
                        } elseif($cmd=='author_follow'){
                            $entry_id = $arg_list[1];
                        }

                        $result = include_once($tmpfname);
                        unlink($tmpfname);

                        if($result>1 && $cmd=='author_follow' && preg_match('/^[1-9][0-9]*$/', $result))
                        {
                            $date = JFactory::getDate();
                            $now  = $date->toSql();
                            $author = JFactory::getUser($result);

                            if($author->id)
                            {
                                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlexcomment/tables');
                            
                                $row = JTable::getInstance("Subscribe", "TableCm");

                                $row->bind(array(
                                        'name' => $author->name,
                                        'email' => $author->email,
                                        'email_confirmed' => 1,
                                        'created_by' => $result,
                                        'obj_id' => $entry_id,
                                        'created_time' => $now,
                                        'point' => $date->toUnix()
                                    ));

                                if($row->check())
                                {
                                    $row->store();
                                }
                            }
                        }
                        
                        if($result=='deleted' && $cmd=='entry_details')
                        {
                            // delete this entry
                            $query->clear()
                                  ->select('id')
                                  ->from('#__jlexcomment_obj')
                                  ->where(array(
                                        'com_name='.$this->_db->quote($this->object),
                                        'com_key='.$this->_db->quote($this->object_id)
                                    ));

                            $id = $this->_db->setQuery($query)->loadResult();

                            if($id>0)
                            {
                                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlexcomment/tables');
                            
                                $row = JTable::getInstance("Object", "TableCm");
                                $row->load($id);
                                $row->delete();
                            }
                        }

                        if($cmd=='entry_details' && is_array($result) && !empty($result))
                        {
                            $fields = array();
                            if(array_key_exists('title', $result))
                                $fields[]='title='.$this->_db->quote($result['title']);

                            if(array_key_exists('url', $result))
                                $fields[]='url='.$this->_db->quote($result['url']);

                            if(count($fields))
                            {
                                // update entry params
                                $query->clear()
                                      ->update('#__jlexcomment_obj')
                                      ->set($fields)
                                      ->where(array(
                                            'com_name='.$this->_db->quote($this->object),
                                            'com_key='.$this->_db->quote($this->object_id)
                                        ));

                                $this->_db->setQuery($query)->execute();
                            }
                        }
                    } catch(Exception $e){
                        // add to log
                        $err = $cmd.': '.$e->getMessage();
                        $query->clear()
                              ->update('#__jlexcomment_sync')
                              ->set('latest_log='.$this->_db->quote($err))
                              ->where('object='.$this->_db->quote($this->object));

                        $this->_db->setQuery($query)->execute();
                    }
                }
                break;
        }
    }
}