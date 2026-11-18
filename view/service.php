<?php
$pageTitle = Lang::field($service, 'title');
ob_start();
?>
<section class="section">
    <div class="container service-detail">
        <?php echo ViewService::Picture($service, 'detail-media reveal'); ?>
        <div class="detail-info">
            <a class="back-link" href="category?id=<?php echo (int)$service['category_id']; ?>">← <?php echo e(Lang::field($service, 'category')); ?></a>
            <h1><?php echo e(Lang::field($service, 'title')); ?></h1>
            <?php if ((int)$service['reviews_count'] > 0): ?>
                <a href="#reviews" class="card-rating"><?php echo Icon::stars($service['rating']); ?> <?php echo e($service['rating']); ?> (<?php echo (int)$service['reviews_count']; ?>)</a>
            <?php endif; ?>
            <p class="lead"><?php echo nl2br(e(Lang::field($service, 'description'))); ?></p>
            <dl class="facts">
                <div><dt><?php echo e(t('price')); ?></dt><dd class="price"><?php echo ViewService::Price($service['price']); ?></dd></div>
                <div><dt><?php echo e(t('duration')); ?></dt><dd><?php echo (int)$service['duration_min']; ?> <?php echo e(t('min')); ?></dd></div>
            </dl>
            <a class="btn" href="booking?service=<?php echo (int)$service['id']; ?>"><?php echo Icon::svg('calendar'); ?> <?php echo e(t('book_now')); ?></a>
        </div>
    </div>
</section>

<section class="section section-soft" id="reviews">
    <div class="container reviews-layout">
        <div>
            <h2><?php echo e(t('reviews_title')); ?></h2>
            <?php if (isset($_GET['review'])): $rv = $_GET['review']; ?>
                <div class="alert <?php echo $rv == 'ok' ? 'alert-ok' : 'alert-error'; ?>">
                    <?php echo e($rv == 'ok' ? t('review_thanks') : ($rv == 'dup' ? t('review_dup') : t('review_error'))); ?>
                </div>
            <?php endif; ?>
            <?php echo ViewReview::ReviewsByService($reviews); ?>
        </div>
        <?php echo ViewReview::ReviewForm($service['id']); ?>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
