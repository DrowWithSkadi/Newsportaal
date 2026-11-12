<?php
$pageTitle = t('contact_title');
ob_start();
?>
<section class="page-head">
    <div class="container">
        <h1><?php echo e(t('contact_title')); ?></h1>
        <p class="lead"><?php echo e(t('contact_text')); ?></p>
    </div>
</section>

<section class="section">
    <div class="container grid contact-grid">
        <div class="contact-card reveal"><?php echo Icon::svg('pin'); ?><h3><?php echo e(t('address')); ?></h3><p><?php echo e(t('address_value')); ?></p></div>
        <div class="contact-card reveal"><?php echo Icon::svg('phone'); ?><h3><?php echo e(t('phone')); ?></h3><p><a href="tel:+37255501234">+372 5550 1234</a></p></div>
        <div class="contact-card reveal"><?php echo Icon::svg('mail'); ?><h3><?php echo e(t('email')); ?></h3><p><a href="mailto:info@lilia.ee">info@lilia.ee</a></p></div>
        <div class="contact-card reveal"><?php echo Icon::svg('clock'); ?><h3><?php echo e(t('hours')); ?></h3>
            <p><?php echo e(t('hours_weekdays')); ?><br><?php echo e(t('hours_saturday')); ?><br><?php echo e(t('hours_sunday')); ?></p>
        </div>
    </div>
    <p class="center"><a class="btn" href="booking"><?php echo Icon::svg('calendar'); ?> <?php echo e(t('nav_book')); ?></a></p>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
