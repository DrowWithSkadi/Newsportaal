<?php
$pageTitle = t('my_bookings');

// Ühe broneeringu kaart
$card = function ($b) {
    $start = $b['booking_date'] . ' ' . substr($b['booking_time'], 0, 5);
    ob_start(); ?>
    <article class="my-booking status-<?php echo e($b['status']); ?>">
        <div class="my-booking-date">
            <strong><?php echo e(date('d.m', strtotime($b['booking_date']))); ?></strong>
            <span><?php echo e(substr($b['booking_time'], 0, 5)); ?></span>
        </div>
        <div class="my-booking-info">
            <h3><a href="service?id=<?php echo (int)$b['service_id']; ?>"><?php echo e(Lang::field($b, 'title')); ?></a></h3>
            <p class="muted"><?php echo e($b['master_name']); ?> · <?php echo (int)$b['duration_min']; ?> <?php echo e(t('min')); ?> · <?php echo ViewService::Price($b['price']); ?></p>
            <span class="status-pill"><?php echo e(t('status_' . $b['status'])); ?></span>
        </div>
        <div class="my-booking-action">
            <?php if (Booking::canCancel($b)): ?>
                <form method="POST" action="bookingCancel" onsubmit="return confirm('<?php echo e(t('cancel_confirm')); ?>');">
                    <?php echo Auth::csrfField(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                    <button type="submit" class="btn btn-ghost btn-sm"><?php echo e(t('cancel_btn')); ?></button>
                </form>
            <?php elseif (Booking::isUpcoming($b)): ?>
                <small class="muted"><?php echo e(t('cancel_too_late')); ?><br><a href="tel:+37255501234">+372 5550 1234</a></small>
            <?php endif; ?>
        </div>
    </article>
    <?php return ob_get_clean();
};
ob_start();
?>
<section class="page-head">
    <div class="container">
        <h1><?php echo e(t('my_bookings')); ?></h1>
        <p class="lead"><?php echo e(sprintf(t('cancel_rule'), Booking::CANCEL_HOURS)); ?></p>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <?php if (!$upcoming && !$past): ?>
            <p class="muted"><?php echo e(t('my_none')); ?></p>
            <a class="btn" href="booking"><?php echo Icon::svg('calendar'); ?> <?php echo e(t('my_book_first')); ?></a>
        <?php else: ?>
            <h2><?php echo e(t('my_upcoming')); ?></h2>
            <?php if ($upcoming): ?>
                <div class="my-bookings"><?php foreach ($upcoming as $b) echo $card($b); ?></div>
            <?php else: ?>
                <p class="muted"><?php echo e(t('my_none')); ?></p>
            <?php endif; ?>
            <a class="btn" href="booking"><?php echo Icon::svg('calendar'); ?> <?php echo e(t('nav_book')); ?></a>

            <?php if ($past): ?>
                <h2 class="my-past-title"><?php echo e(t('my_past')); ?></h2>
                <div class="my-bookings my-bookings-past"><?php foreach ($past as $b) echo $card($b); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
