<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$input       = Factory::getApplication()->getInput();
$option      = $input->getCmd('option', '');
$view        = $input->getCmd('view', '');
$articleView = ($option === 'com_content' && $view === 'article');

// If not in article view, keep behavior identical to original (don’t render)
if (!$articleView) {
    return;
}

// Current URL and title
$item         = $displayData['item'] ?? null;
$params       = $displayData['params'] ?? null;
if (!$item) { return; }

$currentUrl   = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
$articleTitle = $item->title;

// Output exactly the same markup you used before (no extra CSS/JS)
?>
<span class="social-icons float-end ps-1">
  <a href="https://telegram.me/share/url?url=<?php echo $currentUrl; ?>&text=<?php echo $articleTitle; ?>"
     target="_blank" aria-label="اشتراک در تلگرام" rel="noopener noreferrer">
    <span class="fab fa-telegram" aria-hidden="true"></span>
  </a>
</span>
<span class="social-icons float-end ps-1">
  <a href="https://x.com/intent/post?text=<?php echo $articleTitle; ?>. <?php echo $currentUrl; ?>"
     target="_blank" aria-label="اشتراک در توییتر" rel="noopener noreferrer">
    <span class="fab fa-x-twitter" aria-hidden="true"></span>
  </a>
</span>
<span class="social-icons float-end ps-1">
  <a href="whatsapp://send?text=<?php echo $articleTitle; ?>. <?php echo $currentUrl; ?>"
     target="_blank" aria-label="اشتراک در این واتس اپ" rel="noopener noreferrer">
    <span class="fab fa-whatsapp" aria-hidden="true"></span>
  </a>
</span>
<span class="social-icons float-end">
  <a href="mailto:?subject=<?php echo $articleTitle; ?>&body=<?php echo $currentUrl; ?>"
     aria-label="اشتراک با ایمیل">
    <span class="fa fa-envelope" aria-hidden="true"></span>
  </a>
</span>
