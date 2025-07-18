<?php
$image  = json_decode($item->images)->image_intro ?? '';
$title  = htmlspecialchars($item->title, ENT_QUOTES);
$cat    = htmlspecialchars($item->cat_title, ENT_QUOTES);
$link   = Route::_(ContentHelperRoute::getArticleRoute($item->id));
$classes = $isBig ? 'showcase-item showcase-big' : 'showcase-item';
?>
<article class="<?php echo $classes; ?>">
    <?php if ($image): ?>
        <figure class="ratio ratio-16x9">
            <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>"
                 loading="lazy" width="480" height="270"/>
        </figure>
    <?php endif; ?>

    <header class="showcase-caption">
        <span class="badge bg-danger small"><?php echo $cat; ?></span>
        <h3 class="h6 m-0 fw-bold lh-base text-white">
            <?php echo $title; ?>
        </h3>
    </header>

    <a href="<?php echo $link; ?>" class="stretched-link"></a>
</article>
