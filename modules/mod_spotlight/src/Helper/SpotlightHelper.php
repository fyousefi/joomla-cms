<?php

namespace AsiaSun\Module\Spotlight\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class SpotlightHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Return articles in the exact Subform drag order.
     * Payload: id, title, image_intro, image_intro_alt.
     * Relies on module cache (Dispatcher → ModuleHelper::moduleCache).
     */
    public function getItems(Registry $params): array
    {
        // 1) Get ordered IDs from Subform (handles JSON string or array)
        $ids = $this->extractIds($params, 20);
        if (!$ids) {
            return [];
        }

        // 2) Minimal query, fast filters, keep order via FIELD()
        $db     = $this->getDatabase() ?: Factory::getContainer()->get(DatabaseInterface::class);
        $user   = Factory::getApplication()->getIdentity();
        $levels = $user ? $user->getAuthorisedViewLevels() : [1];
        $nowSql = Factory::getDate()->toSql();

        $q = $db->getQuery(true)
            ->select(['a.id', 'a.title', 'a.images'])
            ->from('#__content AS a')
            ->where('a.state = 1')
            ->where('a.access IN (' . implode(',', array_map('intval', $levels)) . ')')
            ->where('a.id IN (' . implode(',', $ids) . ')')
            ->where('(a.publish_up IS NULL OR a.publish_up = ' . $db->quote($db->getNullDate()) . ' OR a.publish_up <= ' . $db->quote($nowSql) . ')')
            ->where('(a.publish_down IS NULL OR a.publish_down = ' . $db->quote($db->getNullDate()) . ' OR a.publish_down >= ' . $db->quote($nowSql) . ')')
            ->clear('order')
            ->order('FIELD(a.id,' . implode(',', $ids) . ')')
            ->setLimit(count($ids));

        $db->setQuery($q);

        // Debug
        //Factory::getApplication()->enqueueMessage(print_r($params->get('items'), true), 'info');

        $rows = (array) $db->loadObjectList();

        // 3) Intro image only
        foreach ($rows as $r) {
            $img = json_decode($r->images ?: '{}', true) ?: [];
            $r->image_intro     = $img['image_intro']     ?? '';
            $r->image_intro_alt = $img['image_intro_alt'] ?? '';
            unset($r->images);
        }

        return $rows;
    }

    private function extractIds(Registry $params, int $cap): array
    {
        $raw = $params->get('items', []);

        // Normalize to PHP array
        if (is_string($raw)) {
            $rows = json_decode($raw, true) ?: [];
        } else {
            $rows = (array) $raw;
        }

        $ids  = [];
        $seen = [];

        foreach ($rows as $row) {
            // Some Subform serializers wrap fields under "item"
            if (is_array($row) && isset($row['item'])) {
                $row = $row['item'];
            } elseif (is_object($row) && isset($row->item)) {
                $row = $row->item;
            }

            // Accept common shapes: number, "123: title", ['article_id'=>123], ['id'=>123], ['value'=>['id'=>123]], etc.
            $id = $this->toId($row);

            if ($id > 0 && !isset($seen[$id])) {
                $seen[$id] = true;
                $ids[]     = $id;
                if (count($ids) >= $cap) {
                    break;
                }
            }
        }

        return $ids;
    }

    private function toId($v): int
    {
        if (is_int($v)) {
            return $v;
        }
        if (is_string($v)) {
            // Grab first integer in string (handles "123" or "123: Title")
            if (preg_match('/\d+/', $v, $m)) {
                return (int) $m[0];
            }
            return 0;
        }
        if (is_array($v)) {
            foreach (['article_id', 'id', 'value', 'select'] as $k) {
                if (array_key_exists($k, $v)) {
                    return $this->toId($v[$k]);
                }
            }
            return 0;
        }
        if (is_object($v)) {
            foreach (['article_id', 'id', 'value', 'select'] as $k) {
                if (isset($v->$k)) {
                    return $this->toId($v->$k);
                }
            }
        }
        return 0;
    }
}
