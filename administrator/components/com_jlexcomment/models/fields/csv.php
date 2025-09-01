<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCM_CSV extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCM_CSV';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$options =  $this->getParse ();
        
        $html = JHTML::_ ( 'select.genericlist', 
                        $options , 
                        $this->name, 
                        ' class="input-medium custom-select" ', 
                        'value', 'text',
                        $this->value);

        return $html;
	}

	protected function getParse ()
    {
    	static $options = null;

    	if (! empty($options))
    	{
    		return $options;
    	}

        require_once ( dirname(__FILE__) . "/../../libraries/parsecsv.lib.php" );

        $id = JFactory::getApplication()->input->getInt ('id', 0);

        $file   = JPATH_ROOT . "/tmp/import_" . $id . '.tmp';

        if ( !JFile::exists($file) )
        {
            throw new Exception("File CSV not found or Session expired.", 500);
            return false;
        }

        $csv = new parseCSV();
        $csv->heading = false;
        $csv->auto( $file );

        if (! count($csv->data))
        {
            throw new Exception("Don't find any row to import. Try again with another file.", 500);
            return false;
        }

        $fields = $csv->data[0];
        $options = array ();


        foreach ($fields as $key => $field)
        {
            $options[] = JHtml::_("select.option", $key, $field);
        }

        return $options;
    }
}
