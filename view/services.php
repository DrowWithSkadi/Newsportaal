<?php
$pageTitle = t('all_services');
ob_start();
?>
<section class="page-head">
    <div class="container">
        <h1><?php echo e(t('all_services')); ?></h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Filtreerimine kategooria järgi toimub JavaScriptiga ilma lehte uuesti laadimata -->
        <div class="filters" role="toolbar">
            <button class="chip active" type="button" data-filter="all"><?php echo e(t('filter_all')); ?></button>
            <?php foreach ($categories as $c): ?>
                <button class="chip" type="button" data-filter="<?php echo (int)$c['id']; ?>"><?php echo e(Lang::field($c, 'name')); ?></button>
            <?php endforeach; ?>
        </div>
        <?php echo ViewService::Grid($arr); ?>
    </div>
</section>
<?php
$content = ob_get_clean();
include_once 'view/layout.php';
