<?php

namespace AsiaSun\Module\Showcase\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

class ShowcaseHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Return up to 4 articles according to the mode.
     * Relies on Joomla's module cache (Advanced → Caching) – no manual cache here.
     */
    public function getItems(Registry $params): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $q     = $db->getQuery(true)
            ->select('a.id, a.title, a.images, c.title AS cat')
            ->from('#__content AS a')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid')
            ->where('a.state = 1')
            ->where('a.access IN (' . implode(',', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels()) . ')')
            ->order('a.publish_up DESC')
            ->setLimit(5);

        // Prep for Normal or Dynamic Modes
        $mode = $params->get('mode', 'auto_feature');

        switch ($mode) {
            case 'auto_feature':
                $q->where('a.featured = 1');
                break;

            case 'auto_cats':
                $cats = array_map('intval', (array) $params->get('cats', []));
                if ($cats) {
                    $q->where('a.catid IN (' . implode(',', $cats) . ')');
                }
                break;

            case 'manual':
                $ids = array_slice(array_filter(array_map('intval', explode(',', $params->get('ids', '')))),0,5);
                if (!$ids) {
                    return [];
                }
                $q->where('a.id IN (' . implode(',', $ids) . ')')
                    ->clear('order')
                    ->order('FIELD(a.id,' . implode(',', $ids) . ')');
                break;

            case 'select_article':
                $ids = [];
                for ($i = 1; $i <= 5; $i++) {
                    $id = (int) $params->get('article' . $i);
                    if ($id) {
                        $ids[] = $id;
                    }
                }
                if (!$ids) {
                    return [];
                }
                $q->where('a.id IN (' . implode(',', $ids) . ')')
                    ->clear('order')
                    ->order('FIELD(a.id,' . implode(',', $ids) . ')');
                break;
        }

        $db->setQuery($q);

        // Debug
        /*Factory::getApplication()->enqueueMessage(
            'Showcase SQL → ' . $q->dump(), 'info'
        );*/

        return $db->loadObjectList();
    }
}
