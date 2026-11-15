<?php
$isEdit = !empty($edit);
$f = $isEdit ? $edit : array('name_et' => '', 'name_ru' => '', 'icon' => 'sparkle', 'sort_order' => count($arr) + 1);
ob_start();
?>
<h2>Kategooriad</h2>

<?php if ($result): ?>
    <div class="alert <?php echo $result[0] ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo $result[0] ? 'Salvestatud.' : implode('<br>', array_map('e', $result[1])); ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="table-responsive">
                <table class="table table-hover">
                    <tr><th>Järjekord</th><th>Nimetus (ET / RU)</th><th>Ikoon</th><th>Teenuseid</th><th>Meistreid</th><th></th></tr>
                    <?php foreach ($arr as $c): ?>
                        <tr>
                            <td><?php echo (int)$c['sort_order']; ?></td>
                            <td><b><?php echo e($c['name_et']); ?></b><br><small class="text-muted"><?php echo e($c['name_ru']); ?></small></td>
                            <td><code><?php echo e($c['icon']); ?></code></td>
                            <td><?php echo (int)$c['services_count']; ?></td>
                            <td><?php echo (int)$c['masters_count']; ?></td>
                            <td class="actions">
                                <a class="btn btn-default btn-sm" href="categoryEdit?id=<?php echo (int)$c['id']; ?>"><i class="fa fa-pencil"></i></a>
                                <form method="POST" action="categoryDel?id=<?php echo (int)$c['id']; ?>" class="inline"
                                      onsubmit="return confirm('Kustutada kategooria?');">
                                    <button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <form class="panel panel-default panel-body" method="POST"
              action="<?php echo $isEdit ? 'categoryEditResult?id=' . (int)$edit['id'] : 'categoryAdd'; ?>">
            <h4><?php echo $isEdit ? 'Muuda kategooriat' : 'Lisa kategooria'; ?></h4>
            <div class="form-group"><label>Nimetus (ET) *</label>
                <input class="form-control" name="name_et" maxlength="100" required value="<?php echo e($f['name_et']); ?>"></div>
            <div class="form-group"><label>Nimetus (RU) *</label>
                <input class="form-control" name="name_ru" maxlength="100" required value="<?php echo e($f['name_ru']); ?>"></div>
            <div class="row">
                <div class="col-xs-6 form-group"><label>Ikoon</label>
                    <select class="form-control" name="icon">
                        <?php foreach ($icons as $i): ?>
                            <option <?php echo $f['icon'] == $i ? 'selected' : ''; ?>><?php echo e($i); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xs-6 form-group"><label>Järjekord</label>
                    <input class="form-control" type="number" name="sort_order" value="<?php echo (int)$f['sort_order']; ?>"></div>
            </div>
            <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> Salvesta</button>
            <?php if ($isEdit): ?><a class="btn btn-default" href="categoriesAdmin">Loobu</a><?php endif; ?>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include 'viewAdmin/templates/layout.php';
