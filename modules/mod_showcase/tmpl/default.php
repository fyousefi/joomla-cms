<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_showcase
 *
 * @copyright   ...
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

?>

<?php if (empty($items)) : ?>
    <p><?php echo JText::_('MOD_SHOWCASE_NO_ITEMS'); ?></p>
    <?php return; ?>
<?php endif; ?>

<div class="showcase-grid d-grid">
    <?php foreach ($items as $i => $item) :
        $isBig = $i === 0;
        $img   = '';
        if (!empty($item->images)) {
            $images = json_decode($item->images);
            $img = $images->image_intro ?? '';
        }
        $link  = Route::_(RouteHelper::getArticleRoute($item->id));
        $cls   = $isBig ? 'showcase-item showcase-big' : 'showcase-item';
        ?>
        <article class="<?php echo $cls; ?>">
            <figure class="ratio ratio-16x9">
                <?php if ($img) : ?>
                    <img src="<?php echo htmlspecialchars($img, ENT_QUOTES); ?>"
                         alt="<?php echo htmlspecialchars($item->title, ENT_QUOTES); ?>"
                         loading="lazy"
                         width="640"
                         height="360">
                <?php endif; ?>
            </figure>
            <header class="showcase-caption">
                <span class="badge bg-danger"><?php echo htmlspecialchars($item->cat, ENT_QUOTES); ?></span>
                <<?php echo $heading; ?> class="h6 m-0 fw-bold lh-base text-white">
                <?php echo htmlspecialchars($item->title, ENT_QUOTES); ?>
            </<?php echo $heading; ?>>
            </header>
            <a href="<?php echo $link; ?>" class="stretched-link"></a>
        </article>
    <?php endforeach; ?>
</div>
