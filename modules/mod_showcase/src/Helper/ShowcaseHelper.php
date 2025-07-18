<?php

namespace AsiaSun\Module\Showcase\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;

class ShowcaseHelper
{
    public function getLoggedonUsername(string $default)
    {
        $user = Factory::getApplication()->getIdentity();
        if ($user->id !== 0)  // found a logged-on user
        {
            return $user->username;
        }
        else
        {
            return $default;
        }
    }

    public function countAjax()
    {
        $user = Factory::getApplication()->getIdentity();

        if ($user->id == 0) {
            // not logged on
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'));
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__session AS s')
            ->where('s.guest = 0');

        $db->setQuery($query);

        return (string) $db->loadResult();
    }
}
