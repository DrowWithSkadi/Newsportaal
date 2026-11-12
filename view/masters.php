<?php
$pageTitle = t('masters_title');
ob_start();
?>
<section class="page-head">
    <div class="container">
        <h1><?php echo e(t('masters_title')); ?></h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid masters-grid">
            <?php foreach ($masters as $m) { echo ViewService::MasterCard($m); } ?>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
