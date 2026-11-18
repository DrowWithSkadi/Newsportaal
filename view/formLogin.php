<?php
$pageTitle = t('login_title');
ob_start();
?>
<section class="section">
    <div class="container narrow-sm">
        <form class="form auth-form reveal" method="POST" action="login">
            <h1><?php echo e(t('login_title')); ?></h1>
            <p class="muted"><?php echo e(t('login_intro')); ?></p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php echo Auth::csrfField(); ?>
            <input type="hidden" name="return" value="<?php echo e($return); ?>">
            <label><?php echo e(t('login_email')); ?>
                <input type="email" name="email" maxlength="150" required autofocus autocomplete="username" value="<?php echo e($email); ?>">
            </label>
            <label><?php echo e(t('login_password')); ?>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button type="submit" class="btn btn-block"><?php echo Icon::svg('user'); ?> <?php echo e(t('login_btn')); ?></button>
            <p class="auth-switch"><?php echo e(t('login_no_account')); ?>
                <a href="registerForm?return=<?php echo urlencode($return); ?>"><?php echo e(t('nav_register')); ?></a></p>
        </form>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
