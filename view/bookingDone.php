<?php
$pageTitle = t('booking_ok_title');
ob_start();
?>
<section class="section">
    <div class="container narrow">
        <div class="success-card reveal">
            <div class="success-icon"><?php echo Icon::svg('check'); ?></div>
            <h1><?php echo e(t('booking_ok_title')); ?></h1>
            <?php if ($booking): ?>
                <dl class="facts facts-list">
                    <div><dt><?php echo e(t('booking_service')); ?></dt><dd><?php echo e(Lang::field($booking, 'title')); ?></dd></div>
                    <div><dt><?php echo e(t('booking_master')); ?></dt><dd><?php echo e($booking['master_name']); ?></dd></div>
                    <div><dt><?php echo e(t('booking_date')); ?></dt><dd><?php echo e(date('d.m.Y', strtotime($booking['booking_date']))); ?></dd></div>
                    <div><dt><?php echo e(t('booking_time')); ?></dt><dd><?php echo e(substr($booking['booking_time'], 0, 5)); ?></dd></div>
                    <div><dt><?php echo e(t('price')); ?></dt><dd><?php echo ViewService::Price($booking['price']); ?></dd></div>
                </dl>
                <p><?php echo e(t('booking_ok_text')); ?>, <?php echo e($booking['client_name']); ?>!</p>
            <?php endif; ?>
            <a class="btn" href="./"><?php echo e(t('back_home')); ?></a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
