<?php
defined('_JEXEC') or die;

use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

final class PlgContentOffcanvasbreak extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $app     = Factory::getApplication();

        // Only on full article view
        if ($context !== 'com_content.article'
            || empty($row->text)
            || $app->input->getString('view') !== 'article') {
            return;
        }

        $text  = $row->text;
        $regex = '#<hr(?P<attrs>[^>]*\sclass="[^"]*\bsystem-pagebreak\b[^"]*"[^>]*)\/?>#iU';

        if (!preg_match($regex, $text)) {
            return; // nothing to do
        }

        $index    = 0;
        $sections = [];
        $prefix   = 'pb-sec-';

        // Replace each pagebreak with an in-page anchor; collect titles
        $newText = preg_replace_callback($regex, function ($m) use (&$index, &$sections, $prefix) {
            $index++;
            $attrs = $m['attrs'] ?? '';
            $title = null;

            if (preg_match('#\btitle="([^"]*)"#i', $attrs, $mm) && trim($mm[1]) !== '') {
                $title = stripslashes($mm[1]);
            } elseif (preg_match('#\balt="([^"]*)"#i', $attrs, $mm) && trim($mm[1]) !== '') {
                $title = stripslashes($mm[1]);
            } else {
                $title = 'Page ' . ($index);
            }

            $id = $prefix . $index;

            $sections[] = (object)[
                'id'    => $id,
                'title' => $title,
            ];

            // Replace HR with a named anchor
            return '<a id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"></a>';
        }, $text);

        if ($newText === null) {
            return;
        }

        // Prevent the core Page Break plugin from paginating this article
        $newText = preg_replace('#\bsystem-pagebreak\b#i', 'offcanvas-pagebreak', $newText);
        $row->text = $newText;

        // Expose data to template via Application user-state (Joomla 5-safe)
        $data = [
            'articleId' => (int)($row->id ?? 0),
            'sections'  => $sections,
            'title'     => $row->title ?? '',
            // Per your spec: show offcanvas from the LEFT
            'offcanvasSideClass' => 'offcanvas-start',
        ];
        $app->setUserState('plg.offcanvasbreak.data', $data);

        // Conditionally load Bootstrap JS ONLY when pagebreaks exist
        $wam = Factory::getDocument()->getWebAssetManager();
        // Using bundle is the safest core key; it includes Offcanvas
        $wam->useScript('bootstrap.offcanvas');

        // (Optional) inline render at top of article if you want a fallback before adding navbar hook:
        // $row->text = $this->renderInlineOffcanvas($data) . $row->text;
    }

    private function renderInlineOffcanvas(array $data): string
    {
        $path = JPATH_PLUGINS . '/content/offcanvasbreak/tmpl/offcanvas.php';
        if (!is_file($path)) {
            return '';
        }
        $displayData = $data;
        ob_start();
        include $path;
        return ob_get_clean();
    }
}
