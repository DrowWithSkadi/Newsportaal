<?php
$pageTitle = t('search_title');
ob_start();
?>
<section class="page-head">
    <div class="container">
        <h1><?php echo e(t('search_title')); ?><?php echo $q !== '' ? ': «' . e($q) . '»' : ''; ?></h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php
        if ($arr) {
            echo ViewService::Grid($arr);
        } else {
            echo '<p class="muted center">' . e(t('search_none')) . '</p>';
        }
        ?>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
