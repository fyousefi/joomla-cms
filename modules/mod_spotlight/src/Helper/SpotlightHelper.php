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

        // Media branch (query by tags; newest first)
        if ($type === 'media') {
            return $this->getMediaItems($params);
        }

        // Pick the correct subform field
        if ($type === 'hot') {
            $fieldName = 'items_hot';
        } elseif ($type === 'review') {
            $fieldName = 'items_review';
        } else {
            $fieldName = 'items_editor';
        }

        // Extract ordered rows (id + optional override_title / score)
        $rows = $this->extractRows($params, $fieldName, 20);
        if (!$rows) {
            return [];
        }

        // Build id list and override labels (hot)
        $labelById = [];   // for hot
        $scoreById = [];   // for review
        $ids       = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;

                if ($type === 'hot' && !empty($r['override_title'])) {
                    $labelById[$id] = (string) $r['override_title'];
                }

                if ($type === 'review' && isset($r['score']) && $r['score'] !== '') {
                    // keep as float; formatting is done in layout
                    $scoreById[$id] = (float) $r['score'];
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
            ->setLimit(\count($ids));

        if ($type === 'hot') {
            $q->select(['a.id']); // light
        } else {
            $q->select(['a.id', 'a.title', 'a.images']); // Editor & Review need title+image
        }

        $db->setQuery($q);
        $rowsDb = (array) $db->loadObjectList();

        $out = [];

        if ($type === 'hot') {
            foreach ($rowsDb as $r) {
                $id                 = (int) $r->id;
                $o                  = new \stdClass();
                $o->id              = $id;
                $o->title           = $labelById[$id] ?? ''; // admin-provided
                $o->image_intro     = '';
                $o->image_intro_alt = '';
                $out[]              = $o;
            }
            return $out;
        }

        // Editor & Review: parse intro image once; attach score for Review
        foreach ($rowsDb as $r) {
            $img                = json_decode($r->images ?: '{}', true) ?: [];
            $r->image_intro     = $img['image_intro'] ?? '';
            $r->image_intro_alt = $img['image_intro_alt'] ?? '';
            unset($r->images);

            if ($type === 'review') {
                $r->score = $scoreById[$r->id] ?? null; // null when not set
            }

            $out[] = $r;
        }

        return $out;
    }

    /**
     * MEDIA: latest N articles that have ANY of the selected tags.
     * Returns objects with: id, title, image_intro(+alt), created, category_title.
     * Single query, access/publish filtered, ordered by created DESC.
     */
    private function getMediaItems(Registry $params): array
    {
        $tagIds = (array) $params->get('media_tags', []);
        $tagIds = array_values(array_unique(array_map('intval', $tagIds)));
        $limit  = max(1, (int) $params->get('media_limit', 5));

        if (!$tagIds) {
            return [];
        }

        $db     = $this->getDatabase() ?: Factory::getContainer()->get(DatabaseInterface::class);
        $user   = Factory::getApplication()->getIdentity();
        $levels = $user ? $user->getAuthorisedViewLevels() : [1];
        $nowSql = Factory::getDate()->toSql();

        $q = $db->getQuery(true)
            ->select([
                'a.id', 'a.title', 'a.images', 'a.created',
                'a.catid', 'c.title AS category_title'
            ])
            ->from('#__content AS a')
            ->innerJoin('#__contentitem_tag_map AS m ON m.content_item_id = a.id AND m.type_alias = ' . $db->quote('com_content.article'))
            ->leftJoin('#__categories AS c ON c.id = a.catid')
            ->where('m.tag_id IN (' . implode(',', $tagIds) . ')')
            ->where('a.state = 1')
            ->where('a.access IN (' . implode(',', array_map('intval', $levels)) . ')')
            ->where('(a.publish_up IS NULL OR a.publish_up = ' . $db->quote($db->getNullDate()) . ' OR a.publish_up <= ' . $db->quote($nowSql) . ')')
            ->where('(a.publish_down IS NULL OR a.publish_down = ' . $db->quote($db->getNullDate()) . ' OR a.publish_down >= ' . $db->quote($nowSql) . ')')
            ->group('a.id')                  // de-dupe when multiple selected tags match the same article
            ->order('a.created DESC')
            ->setLimit($limit);

        $db->setQuery($q);
        $rows = (array) $db->loadObjectList();

        foreach ($rows as $r) {
            $img                = json_decode($r->images ?: '{}', true) ?: [];
            $r->image_intro     = $img['image_intro']     ?? '';
            $r->image_intro_alt = $img['image_intro_alt'] ?? ($r->title ?? '');
            unset($r->images);
        }

        return $rows;
    }

    /**
     * Extract ordered rows from the given subform field.
     * Returns array of ['id'=>int, 'override_title'=>string?], 'score'=>float?].
     */
    private function extractRows(Registry $params, string $fieldName, int $cap): array
    {
        $raw  = $params->get($fieldName, []);
        $rows = \is_string($raw) ? (json_decode($raw, true) ?: []) : (array) $raw;

        $out  = [];
        $seen = [];

        foreach ($rows as $row) {
            // Normalize
            if (\is_array($row) && isset($row['item'])) {
                $row = $row['item'];
            } elseif (\is_object($row) && isset($row->item)) {
                $row = (array) $row->item;
            } elseif (\is_object($row)) {
                $row = (array) $row;
            } else {
                $row = (array) $row;
            }

            $id = $this->toId($row['article_id'] ?? ($row['id'] ?? ($row['value']['id'] ?? null)));
            if ($id > 0 && !isset($seen[$id])) {
                $seen[$id] = true;
                $out[]     = [
                    'id'             => $id,
                    'override_title' => isset($row['override_title']) ? (string) $row['override_title'] : '',
                    'score'          => isset($row['score']) ? (string) $row['score'] : '', // keep raw; cast later
                ];
                if (\count($out) >= $cap) {
                    break;
                }
            }
        }

        return $out;
    }

    private function toId($v): int
    {
        if (\is_int($v)) {
            return $v;
        }
        if (\is_string($v)) {
            if (preg_match('/\d+/', $v, $m)) {
                return (int) $m[0];
            } return 0;
        }
        if (\is_array($v)) {
            foreach (['article_id','id','value','select'] as $k) {
                if (\array_key_exists($k, $v)) {
                    return $this->toId($v[$k]);
                }
            } return 0;
        }
        if (\is_object($v)) {
            foreach (['article_id','id','value','select'] as $k) {
                if (isset($v->$k)) {
                    return $this->toId($v->$k);
                }
            }
        }
        return 0;
    }
}
