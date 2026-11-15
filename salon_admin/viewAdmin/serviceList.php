<?php ob_start(); ?>
<div class="page-header-row">
    <h2>Teenused</h2>
    <a class="btn btn-primary" href="serviceAdd"><i class="fa fa-plus"></i> Lisa teenus</a>
</div>

<div class="panel panel-default">
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th width="5%">ID</th>
                <th width="10%">Pilt</th>
                <th>Teenus</th>
                <th width="12%">Hind</th>
                <th width="10%">Kestus</th>
                <th width="16%"></th>
            </tr>
            <?php foreach ($arr as $row): ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td>
                        <?php if ($row['picture']): ?>
                            <img class="thumb" src="../<?php echo e($row['picture']); ?>" alt="">
                        <?php else: ?>
                            <span class="thumb thumb-empty"><i class="fa fa-image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <b><?php echo e($row['title_et']); ?></b>
                        <?php if ($row['is_popular']): ?><span class="label label-pink">Populaarne</span><?php endif; ?><br>
                        <small class="text-muted">RU: <?php echo e($row['title_ru']); ?></small><br>
                        <small><i class="fa fa-tag"></i> <?php echo e($row['category_et']); ?></small>
                    </td>
                    <td><?php echo e(number_format($row['price'], 2, ',', ' ')); ?> €</td>
                    <td><?php echo (int)$row['duration_min']; ?> min</td>
                    <td class="actions">
                        <a class="btn btn-default btn-sm" href="serviceEdit?id=<?php echo (int)$row['id']; ?>"><i class="fa fa-pencil"></i> Muuda</a>
                        <a class="btn btn-danger btn-sm" href="serviceDel?id=<?php echo (int)$row['id']; ?>"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include 'viewAdmin/templates/layout.php';
