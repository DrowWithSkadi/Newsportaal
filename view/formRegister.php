<?php
$pageTitle = t('register_title');
ob_start();
?>
<section class="section">
    <div class="container narrow-sm">
        <form class="form auth-form reveal" method="POST" action="registerAnswer">
            <h1><?php echo e(t('register_title')); ?></h1>
            <label><?php echo e(t('reg_name')); ?>
                <input type="text" name="name" maxlength="100" required autofocus>
            </label>
            <label><?php echo e(t('reg_email')); ?>
                <input type="email" name="email" maxlength="150" required>
            </label>
            <label><?php echo e(t('reg_password')); ?>
                <input type="password" name="password" minlength="6" required>
            </label>
            <label><?php echo e(t('reg_confirm')); ?>
                <input type="password" name="confirm" minlength="6" required>
            </label>
            <button type="submit" class="btn btn-block" name="save"><?php echo e(t('reg_btn')); ?></button>
        </form>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
