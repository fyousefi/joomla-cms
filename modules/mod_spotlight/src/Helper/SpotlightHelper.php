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

    public function getItems(Registry $params): array
    {
        $type = (string) $params->get('type', 'editor');

        // Pick the correct subform
        $fieldName = $type === 'hot' ? 'items_hot' : 'items_editor';

        // Extract ordered rows
        $rows = $this->extractRows($params, $fieldName, 20);
        if (!$rows) {
            return [];
        }

        // Build id list and override labels (hot)
        $labelById = [];
        $ids = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
                if ($type === 'hot' && !empty($r['override_title'])) {
                    $labelById[$id] = (string) $r['override_title'];
                }
            }
        }

        if (!$ids) {
            return [];
        }

        // Single query, access/publish filtered, keep drag order
        $db     = $this->getDatabase() ?: Factory::getContainer()->get(DatabaseInterface::class);
        $user   = Factory::getApplication()->getIdentity();
        $levels = $user ? $user->getAuthorisedViewLevels() : [1];
        $nowSql = Factory::getDate()->toSql();

        $q = $db->getQuery(true)
            ->from('#__content AS a')
            ->where('a.state = 1')
            ->where('a.access IN (' . implode(',', array_map('intval', $levels)) . ')')
            ->where('a.id IN (' . implode(',', $ids) . ')')
            ->where('(a.publish_up IS NULL OR a.publish_up = ' . $db->quote($db->getNullDate()) . ' OR a.publish_up <= ' . $db->quote($nowSql) . ')')
            ->where('(a.publish_down IS NULL OR a.publish_down = ' . $db->quote($db->getNullDate()) . ' OR a.publish_down >= ' . $db->quote($nowSql) . ')')
            ->clear('order')
            ->order('FIELD(a.id,' . implode(',', $ids) . ')')
            ->setLimit(count($ids));

        if ($type === 'hot') {
            $q->select(['a.id']); // light
        } else {
            $q->select(['a.id', 'a.title', 'a.images']); // editor needs title+image
        }

        $db->setQuery($q);
        $rowsDb = (array) $db->loadObjectList();

        $out = [];

        if ($type === 'hot') {
            foreach ($rowsDb as $r) {
                $id = (int) $r->id;
                $o = new \stdClass();
                $o->id              = $id;
                $o->title           = $labelById[$id] ?? ''; // admin-provided
                $o->image_intro     = '';
                $o->image_intro_alt = '';
                $out[] = $o;
            }
            return $out;
        }

        // Editor Choice: parse intro image once
        foreach ($rowsDb as $r) {
            $img = json_decode($r->images ?: '{}', true) ?: [];
            $r->image_intro     = $img['image_intro']     ?? '';
            $r->image_intro_alt = $img['image_intro_alt'] ?? '';
            unset($r->images);
            $out[] = $r;
        }

        return $out;
    }

    /**
     * Extract ordered rows from the given subform field.
     * Returns array of ['id'=>int, 'override_title'=>string?].
     */
    private function extractRows(Registry $params, string $fieldName, int $cap): array
    {
        $raw = $params->get($fieldName, []);
        $rows = is_string($raw) ? (json_decode($raw, true) ?: []) : (array) $raw;

        $out  = [];
        $seen = [];

        foreach ($rows as $row) {
            // Normalize
            if (is_array($row) && isset($row['item'])) {
                $row = $row['item'];
            } elseif (is_object($row) && isset($row->item)) {
                $row = (array) $row->item;
            } elseif (is_object($row)) {
                $row = (array) $row;
            } else {
                $row = (array) $row;
            }

            $id = $this->toId($row['article_id'] ?? ($row['id'] ?? ($row['value']['id'] ?? null)));
            if ($id > 0 && !isset($seen[$id])) {
                $seen[$id] = true;
                $out[] = [
                    'id' => $id,
                    'override_title' => isset($row['override_title']) ? (string) $row['override_title'] : '',
                ];
                if (count($out) >= $cap) {
                    break;
                }
            }
        }

        return $out;
    }

    private function toId($v): int
    {
        if (is_int($v)) return $v;
        if (is_string($v)) { if (preg_match('/\d+/', $v, $m)) return (int) $m[0]; return 0; }
        if (is_array($v))  { foreach (['article_id','id','value','select'] as $k) if (array_key_exists($k,$v)) return $this->toId($v[$k]); return 0; }
        if (is_object($v)) { foreach (['article_id','id','value','select'] as $k) if (isset($v->$k)) return $this->toId($v->$k); }
        return 0;
    }
}
