<?php
$pageTitle = t('error404_title');
ob_start();
?>
<section class="section">
    <div class="container narrow center">
        <p class="big-404">404</p>
        <h1><?php echo e(t('error404_title')); ?></h1>
        <p class="muted"><?php echo e(t('error404_text')); ?></p>
        <a class="btn" href="./"><?php echo e(t('back_home')); ?></a>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
