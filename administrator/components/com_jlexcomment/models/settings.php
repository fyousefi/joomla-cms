<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JLexCommentModelSettings extends JModelLegacy
{
    public function getItem ()
    {
        $query = $this->_db->getQuery (true);

        $whereClauses [] = $this->_db->quoteName ('element') . '=' . $this->_db->quote ('com_jlexcomment');
        $whereClauses [] = $this->_db->quoteName ('type') . '=' . $this->_db->quote ('component');

        $query->select ("params")
              ->from ("#__extensions")
              ->where ( $whereClauses );

        $params = $this->_db->setQuery ($query,0,1)->loadResult ();
        $params = json_decode ($params);

        return $params;
    }

    public function getForm ()
    {
        $path = dirname (__FILE__) . "/forms/settings.xml";
        $form = JForm::getInstance("jcm_settings", $path);

        $params = $this->getItem ();
        if ($params)
        {
            $form->bind ( array('jform'=>$params) );
        }

        return $form;
    }

    public function getUserGroups()
    {
        $query = $this->_db->getQuery(true);
        $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id')
            ->from('#__usergroups AS a')
            ->leftJoin($this->_db->quoteName('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
            ->group('a.id, a.title, a.lft, a.rgt, a.parent_id')
            ->order('a.lft ASC');
        $this->_db->setQuery($query);
        $options = $this->_db->loadObjectList();

        return $options;
    }

    public function getPermissionForms()
    {
        $item = $this->getItem();
        $groups = $this->getUserGroups();

        JForm::addFieldPath( dirname(__FILE__) . '/fields');
        
        $path = dirname (__FILE__) . "/forms/permission.xml";
        $form = JForm::getInstance("jcm_permission", $path, array('control' => ''), false, '/permissions');

        $parameters = array();
        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($form->getFieldset($fieldset->name) as $field) {
                $name = $field->fieldname;
                $parameters[$name] = !empty($item->$name) && is_array ($item->$name) ? $item->$name : array();
            }
        }

        $groupParameters = array();
        foreach ($groups as $group)
        {
            foreach ($parameters as $key => $values) {
                $groupParameters[$group->value][$key] = array('group' => $group->value,
                                                              'value' => in_array($group->value, $values) ? $group->value : null);
            }
        }

        $forms = array();
        foreach ($groups as $group)
        {
            $form = JForm::getInstance("jcm_permission_" . $group->value, $path, array('control' => ''), false, '/permissions');
            $form->bind( array('jform'=>$groupParameters[$group->value]));
            $forms[$group->value] = $form;
        }

        return $forms;
    }

    public function save ()
    {
        $app = JFactory::getApplication ();
        $params = $app->input->get ('jform', array(), 'array');

        // permission
        $path = dirname (__FILE__) . "/forms/permission.xml";
        $form = JForm::getInstance("jcm_permission", $path, array('control' => ''), false, '/permissions');

        $parameters = array();
        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($form->getFieldset($fieldset->name) as $field) {
                $name = $field->fieldname;
                if (! array_key_exists($name, $params))
                {
                    $params [$name] = array ();
                }
            }
        }

        // media path
        if(array_key_exists('media_base', $params) && $params['media_base']!='media/jcm')
        {
            $pathOg = trim($params['media_base']);
            $pathOg = trim($pathOg,'/');

            if(preg_match('/^[\s\\\/]*$/', $pathOg))
            {
                $params['media_base']='media/jcm';
            } else {
                try {
                    jimport( 'joomla.filesystem.folder' );
                    $params['media_base']=$pathOg;

                    JFolder::create(JPATH_ROOT . '/' . $pathOg);

                    $childFolders = ['avatar', 'cache', 'compression', 'images', 'og', 'other', 'reaction', 'rz', 'stickers', 'sys', 'thumb'];

                    foreach($childFolders as $folder)
                    {
                        JFolder::create(JPATH_ROOT . '/' . $pathOg.'/'.$folder);
                    }
                } catch(Exception $e) {
                    $app->enqueueMessage($e->getMessage() ,'error');
                    $params['media_base']='media/jcm';
                }
            }
        }

        $params = json_encode ($params);

        $query = "UPDATE #__extensions SET params = " .$this->_db->quote ($params). " WHERE type='component' AND element='com_jlexcomment' ";
        $this->_db->setQuery ($query)->execute();

        return true;
    }
}
