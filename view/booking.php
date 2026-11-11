<?php
$pageTitle = t('booking_title');
$old = isset($old) ? $old : array();
$val = function ($k) use ($old) { return isset($old[$k]) ? e($old[$k]) : ''; };
$selectedMaster = isset($old['master_id']) ? (int)$old['master_id'] : 0;
$selectedDate   = isset($old['booking_date']) ? $old['booking_date'] : '';
$selectedTime   = isset($old['booking_time']) ? $old['booking_time'] : '';
ob_start();
?>
<section class="page-head">
    <div class="container">
        <h1><?php echo e(t('booking_title')); ?></h1>
        <p class="lead"><?php echo e(t('booking_intro')); ?></p>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <?php if ($errorKey): ?>
            <div class="alert alert-error"><strong><?php echo e(t('booking_err')); ?>.</strong> <?php echo e(t($errorKey)); ?></div>
        <?php endif; ?>

        <form class="form booking-form" method="POST" action="bookingSave" id="booking-form">
            <div class="form-row">
                <label><?php echo e(t('booking_service')); ?> *
                    <select name="service_id" id="service" required>
                        <option value=""><?php echo e(t('choose')); ?></option>
                        <?php foreach ($categories as $c): ?>
                            <optgroup label="<?php echo e(Lang::field($c, 'name')); ?>">
                            <?php foreach ($services as $s): if ($s['category_id'] != $c['id']) continue; ?>
                                <option value="<?php echo (int)$s['id']; ?>" data-category="<?php echo (int)$s['category_id']; ?>"
                                    <?php echo $selectedService == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo e(Lang::field($s, 'title')); ?> — <?php echo ViewService::Price($s['price']); ?>
                                </option>
                            <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?php echo e(t('booking_master')); ?> *
                    <select name="master_id" id="master" required>
                        <option value=""><?php echo e(t('choose')); ?></option>
                        <?php foreach ($masters as $m): ?>
                            <option value="<?php echo (int)$m['id']; ?>" data-category="<?php echo (int)$m['category_id']; ?>"
                                <?php echo $selectedMaster == $m['id'] ? 'selected' : ''; ?>>
                                <?php echo e($m['name']); ?> (<?php echo e(Lang::field($m, 'category')); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="form-row">
                <label><?php echo e(t('booking_date')); ?> *
                    <input type="date" name="booking_date" id="date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($selectedDate); ?>">
                </label>
                <label><?php echo e(t('booking_time')); ?> *
                    <select name="booking_time" id="time" required data-taken-label="<?php echo e(t('slot_taken')); ?>">
                        <option value=""><?php echo e(t('choose')); ?></option>
                        <?php if ($selectedDate && $selectedMaster): foreach (Booking::getSlotsWithState($selectedMaster, $selectedDate) as $slot): ?>
                            <option value="<?php echo $slot['time']; ?>" <?php echo $slot['free'] ? '' : 'disabled'; ?>
                                <?php echo $slot['time'] == $selectedTime ? 'selected' : ''; ?>>
                                <?php echo $slot['time'] . ($slot['free'] ? '' : ' — ' . e(t('slot_taken'))); ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </label>
            </div>

            <div class="form-row">
                <label><?php echo e(t('booking_name')); ?> *
                    <input type="text" name="client_name" maxlength="100" required value="<?php echo $val('client_name'); ?>">
                </label>
                <label><?php echo e(t('booking_phone')); ?> *
                    <input type="tel" name="phone" maxlength="30" required placeholder="+372 5xxx xxxx" value="<?php echo $val('phone'); ?>">
                </label>
            </div>

            <label><?php echo e(t('booking_email')); ?>
                <input type="email" name="email" maxlength="150" value="<?php echo $val('email'); ?>">
            </label>
            <label><?php echo e(t('booking_note')); ?>
                <textarea name="note" rows="3" maxlength="500"><?php echo $val('note'); ?></textarea>
            </label>

            <button class="btn btn-block" type="submit"><?php echo Icon::svg('calendar'); ?> <?php echo e(t('booking_send')); ?></button>
        </form>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
