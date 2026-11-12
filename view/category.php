<?php
$pageTitle = Lang::field($category, 'name');
ob_start();
?>
<section class="page-head tone-<?php echo ((int)$category['id'] - 1) % 5 + 1; ?>">
    <div class="container">
        <span class="page-icon"><?php echo Icon::svg($category['icon']); ?></span>
        <h1><?php echo e(Lang::field($category, 'name')); ?></h1>
        <a class="back-link" href="services">← <?php echo e(t('nav_all_services')); ?></a>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php
        if ($arr) {
            echo ViewService::Grid($arr);
        } else {
            echo '<p class="muted center">' . e(t('category_empty')) . '</p>';
        }
        ?>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
