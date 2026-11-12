<?php
ob_start();
?>
<section class="hero">
    <div class="hero-blob blob-1"></div>
    <div class="hero-blob blob-2"></div>
    <div class="container hero-inner">
        <div class="hero-text">
            <p class="eyebrow"><?php echo e(t('site_tagline')); ?></p>
            <h1><?php echo e(t('hero_title')); ?></h1>
            <p class="lead"><?php echo e(t('hero_text')); ?></p>
            <div class="hero-actions">
                <a class="btn" href="booking"><?php echo Icon::svg('calendar'); ?> <?php echo e(t('hero_cta')); ?></a>
                <a class="btn btn-ghost" href="services"><?php echo e(t('hero_cta2')); ?> <?php echo Icon::svg('arrow-right'); ?></a>
            </div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="petal p1"></div><div class="petal p2"></div><div class="petal p3"></div>
            <div class="hero-circle"><?php echo Icon::svg('lotus', 'hero-lotus'); ?></div>
        </div>
    </div>
    <div class="container stats">
        <div class="stat reveal"><strong class="counter" data-target="1200">0</strong><span>+ <?php echo e(t('stat_clients')); ?></span></div>
        <div class="stat reveal"><strong class="counter" data-target="<?php echo (int)$servicesCount; ?>">0</strong><span><?php echo e(t('stat_services')); ?></span></div>
        <div class="stat reveal"><strong class="counter" data-target="10">0</strong><span><?php echo e(t('stat_years')); ?></span></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title reveal"><?php echo e(t('section_categories')); ?></h2>
        <div class="grid cat-grid">
            <?php foreach ($categories as $c) { echo ViewService::CategoryCard($c); } ?>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <h2 class="section-title reveal"><?php echo e(t('section_popular')); ?></h2>
        <?php echo ViewService::Grid($popular); ?>
        <p class="center"><a class="btn btn-ghost" href="services"><?php echo e(t('all_services')); ?> <?php echo Icon::svg('arrow-right'); ?></a></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title reveal"><?php echo e(t('section_why')); ?></h2>
        <div class="grid why-grid">
            <div class="why reveal"><?php echo Icon::svg('user'); ?><h3><?php echo e(t('why_1_t')); ?></h3><p><?php echo e(t('why_1_d')); ?></p></div>
            <div class="why reveal"><?php echo Icon::svg('leaf'); ?><h3><?php echo e(t('why_2_t')); ?></h3><p><?php echo e(t('why_2_d')); ?></p></div>
            <div class="why reveal"><?php echo Icon::svg('calendar'); ?><h3><?php echo e(t('why_3_t')); ?></h3><p><?php echo e(t('why_3_d')); ?></p></div>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <h2 class="section-title reveal"><?php echo e(t('section_masters')); ?></h2>
        <div class="grid masters-grid">
            <?php foreach ($masters as $m) { echo ViewService::MasterCard($m); } ?>
        </div>
        <p class="center"><a class="btn btn-ghost" href="masters"><?php echo e(t('nav_masters')); ?> <?php echo Icon::svg('arrow-right'); ?></a></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title reveal"><?php echo e(t('section_reviews')); ?></h2>
        <div class="grid reviews-grid">
            <?php foreach ($reviews as $r) { echo ViewReview::Review($r, true); } ?>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-inner reveal">
        <h2><?php echo e(t('hero_title')); ?></h2>
        <a class="btn btn-light" href="booking"><?php echo e(t('hero_cta')); ?></a>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
