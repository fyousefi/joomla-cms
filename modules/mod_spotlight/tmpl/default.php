<?php
/**
 * @var  array $items   from Dispatcher (SpotlightHelper::getItems)
 * @var  \Joomla\Registry\Registry $params
 * @var  \stdClass $module
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$app   = Factory::getApplication();
$doc   = $app->getDocument();
$wa    = $doc->getWebAssetManager();
$uid   = 'mod-spotlight-' . (int) $module->id;
$tag   = $params->get('heading', 'h4');

$prev = Text::_('MOD_SPOTLIGHT_PREV');
$next = Text::_('MOD_SPOTLIGHT_NEXT');
$emptyMsg = Text::_('MOD_SPOTLIGHT_EMPTY');

// ---- Inline CSS (tiny, you can move into media if you prefer)
$wa->addInlineStyle('
#' . $uid . ' { position: relative; }
#' . $uid . ' .spotlight-viewport { overflow: hidden; }
#' . $uid . ' .spotlight-track {
  display: flex;
  gap: var(--bs-gutter-x, .75rem);
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  padding: .25rem .25rem 1rem;
}
#' . $uid . ' .spotlight-card {
  flex: 0 0 auto;
  scroll-snap-align: start;
  width: 90%;
}
@media (min-width: 576px) { #' . $uid . ' .spotlight-card { width: 50%; } }
@media (min-width: 768px) { #' . $uid . ' .spotlight-card { width: 33.3333%; } }
@media (min-width: 992px) { #' . $uid . ' .spotlight-card { width: 25%; } }
@media (min-width: 1200px){ #' . $uid . ' .spotlight-card { width: 20%; } } /* up to 5 cards */

#' . $uid . ' .spotlight-img .ratio > img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
#' . $uid . ' .spotlight-title {
  font-weight: 700;
  margin: .5rem 0 0;
  line-height: 1.35;
  font-size: 1rem;
}

#' . $uid . ' .spotlight-nav {
  position: absolute;
  top: 50%;
  translate: 0 -50%;
  z-index: 10;
}
#' . $uid . ' .spotlight-prev { inset-inline-start: 0; }
#' . $uid . ' .spotlight-next { inset-inline-end: 0; }
#' . $uid . ' .spotlight-nav .btn {
  --bs-btn-padding-y: .25rem;
  --bs-btn-padding-x: .5rem;
  --bs-btn-border-radius: 50rem;
  box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,.15);
}
');

// ---- Tiny JS for Prev/Next (RTL-aware)
$wa->addInlineScript("
document.addEventListener('DOMContentLoaded', function () {
  var root  = document.getElementById('$uid');
  if (!root) return;
  var track = root.querySelector('.spotlight-track');
  var prev  = root.querySelector('[data-role=\"prev\"]');
  var next  = root.querySelector('[data-role=\"next\"]');

  // Scroll one viewport minus a small gap
  function step() { return Math.max(root.clientWidth * 0.9, 300); }

  // RTL-safe scroll
  function scrollByAmount(sign) {
    var dir = document.dir || document.documentElement.getAttribute('dir') || 'ltr';
    var mul = (dir.toLowerCase() === 'rtl') ? -1 : 1;
    track.scrollBy({ left: mul * sign * step(), behavior: 'smooth' });
  }

  prev && prev.addEventListener('click', function(){ scrollByAmount(-1); });
  next && next.addEventListener('click', function(){ scrollByAmount( 1); });

  // Basic keyboard support when track is focused
  track && track.addEventListener('keydown', function(e){
    if (e.key === 'ArrowLeft')  { e.preventDefault(); scrollByAmount(-1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); scrollByAmount( 1); }
  });
});
");

// ---- Render
?>

<?php if (empty($items)): ?>
    <div class="text-muted small py-3"><?php echo $emptyMsg; ?></div>
    <?php return; ?>
<?php endif; ?>

<div id="<?php echo $uid; ?>" class="mod-spotlight container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <<?= $tag ?> class="m-0">
    </<?= $tag ?>>
</div>

<div class="spotlight-viewport position-relative">

    <div class="spotlight-nav spotlight-prev">
        <button type="button" class="btn btn-light" data-role="prev" aria-label="<?php echo $prev; ?>">‹</button>
    </div>

    <div class="spotlight-nav spotlight-next">
        <button type="button" class="btn btn-light" data-role="next" aria-label="<?php echo $next; ?>">›</button>
    </div>

    <div class="spotlight-track" tabindex="0" role="region" aria-label="<?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>">
        <?php foreach ($items as $item): ?>
            <?php
            $href = Route::_('index.php?option=com_content&view=article&id=' . (int) $item->id);
            $img  = $item->image_intro ?: ''; // already minimal from helper
            $alt  = $item->image_intro_alt ?: $item->title;
            ?>
            <article class="spotlight-card card border-0">
                <a class="spotlight-img text-decoration-none" href="<?php echo $href; ?>">
                    <div class="ratio ratio-16x9">
                        <?php if ($img): ?>
                            <img loading="lazy" decoding="async" src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <img loading="lazy" decoding="async" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3Crect width='16' height='9' fill='%23e9ecef'/%3E%3C/svg%3E"
                                 alt="">
                        <?php endif; ?>
                    </div>
                </a>
                <div class="card-body p-2">
                    <h3 class="spotlight-title h6 m-0">
                        <a class="text-body text-decoration-none" href="<?php echo $href; ?>">
                            <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h3>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
</div>
