<?php
$pageTitle = t('register_title');
ob_start();
?>
<section class="section">
    <div class="container narrow-sm">
        <?php if ($result[0] == true): ?>
            <div class="alert alert-ok"><strong><?php echo e(t('reg_ok')); ?></strong> <a href="./"><?php echo e(t('back_home')); ?></a></div>
        <?php else: ?>
            <div class="alert alert-error">
                <strong><?php echo e(t('reg_err')); ?></strong><br><?php echo $result[1]; ?><br>
                <a href="registerForm"><?php echo e(t('reg_back')); ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
