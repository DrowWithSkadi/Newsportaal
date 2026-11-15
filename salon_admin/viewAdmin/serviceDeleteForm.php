<?php ob_start(); ?>
<div class="page-header-row">
    <h2>Kustuta teenus</h2>
    <a href="servicesAdmin" class="btn btn-default"><i class="fa fa-arrow-left"></i> Teenuste nimekiri</a>
</div>

<?php if (isset($test)): ?>
    <div class="alert <?php echo $test ? 'alert-success' : 'alert-danger'; ?>">
        <strong><?php echo $test ? 'Teenus kustutatud.' : 'Kustutamine ebaõnnestus!'; ?></strong>
        <a href="servicesAdmin">Tagasi nimekirja</a>
    </div>
<?php else: ?>
    <div class="panel panel-danger">
        <div class="panel-heading">Kas oled kindel, et soovid selle teenuse kustutada?</div>
        <div class="panel-body">
            <?php if ($detail['picture']): ?><img class="thumb thumb-lg pull-left" src="../<?php echo e($detail['picture']); ?>" alt=""><?php endif; ?>
            <h4><?php echo e($detail['title_et']); ?> <small><?php echo e($detail['title_ru']); ?></small></h4>
            <p><?php echo e($detail['description_et']); ?></p>
            <p><b><?php echo e(number_format($detail['price'], 2, ',', ' ')); ?> €</b> · <?php echo (int)$detail['duration_min']; ?> min</p>
            <p class="text-danger"><i class="fa fa-warning"></i> Koos teenusega kustutatakse ka selle broneeringud ja arvustused.</p>
            <form method="POST" action="serviceDelResult?id=<?php echo (int)$detail['id']; ?>">
                <button type="submit" class="btn btn-danger" name="save"><i class="fa fa-trash"></i> Kustuta</button>
                <a href="servicesAdmin" class="btn btn-default">Loobu</a>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php $content = ob_get_clean(); ?>
<?php include 'viewAdmin/templates/layout.php';
