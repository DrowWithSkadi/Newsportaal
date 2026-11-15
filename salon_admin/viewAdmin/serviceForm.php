<?php
// Ühine vorm teenuse lisamiseks ja muutmiseks ($mode = 'add' | 'edit')
$v = function ($k, $default = '') use ($detail) { return isset($detail[$k]) ? e($detail[$k]) : $default; };
$isEdit = $mode == 'edit';
ob_start();
?>
<div class="page-header-row">
    <h2><?php echo $isEdit ? 'Muuda teenust' : 'Lisa teenus'; ?></h2>
    <a href="servicesAdmin" class="btn btn-default"><i class="fa fa-arrow-left"></i> Teenuste nimekiri</a>
</div>

<?php if (isset($test) && $test): ?>
    <div class="alert alert-success">
        <strong><?php echo $isEdit ? 'Muudatused salvestatud.' : 'Teenus lisatud.'; ?></strong>
        <a href="servicesAdmin">Tagasi nimekirja</a>
        <?php if ($isEdit): ?> · <a href="../service?id=<?php echo (int)$detail['id']; ?>" target="_blank">Vaata saidil</a><?php endif; ?>
    </div>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><strong>Viga!</strong><br><?php echo implode('<br>', array_map('e', $errors)); ?></div>
    <?php endif; ?>

    <form class="panel panel-default panel-body" method="POST" enctype="multipart/form-data"
          action="<?php echo $isEdit ? 'serviceEditResult?id=' . (int)$detail['id'] : 'serviceAddResult'; ?>">
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Pealkiri (ET) *</label>
                <input type="text" name="title_et" class="form-control" maxlength="150" required value="<?php echo $v('title_et'); ?>">
            </div>
            <div class="col-md-6 form-group">
                <label>Pealkiri (RU) *</label>
                <input type="text" name="title_ru" class="form-control" maxlength="150" required value="<?php echo $v('title_ru'); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Kirjeldus (ET) *</label>
                <textarea name="description_et" rows="5" class="form-control" required><?php echo $v('description_et'); ?></textarea>
            </div>
            <div class="col-md-6 form-group">
                <label>Kirjeldus (RU) *</label>
                <textarea name="description_ru" rows="5" class="form-control" required><?php echo $v('description_ru'); ?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 form-group">
                <label>Kategooria *</label>
                <select name="idCategory" class="form-control" required>
                    <?php foreach ($arr as $row): ?>
                        <option value="<?php echo (int)$row['id']; ?>" <?php echo $v('category_id') == $row['id'] ? 'selected' : ''; ?>><?php echo e($row['name_et']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 form-group">
                <label>Hind (€) *</label>
                <input type="number" name="price" class="form-control" min="1" step="0.01" required value="<?php echo $v('price'); ?>">
            </div>
            <div class="col-sm-4 form-group">
                <label>Kestus (min) *</label>
                <input type="number" name="duration_min" class="form-control" min="5" max="480" step="5" required value="<?php echo $v('duration_min', '60'); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label>Pilt (JPG, PNG, WEBP, kuni 3 MB)</label>
                <?php if (!empty($detail['picture'])): ?>
                    <div class="current-pic">
                        <img class="thumb thumb-lg" src="../<?php echo e($detail['picture']); ?>" alt="">
                        <label class="checkbox-inline"><input type="checkbox" name="remove_picture"> Eemalda pilt</label>
                    </div>
                <?php endif; ?>
                <input type="file" name="picture" accept="image/jpeg,image/png,image/webp">
            </div>
            <div class="col-sm-6 form-group">
                <label>&nbsp;</label>
                <div class="checkbox"><label><input type="checkbox" name="is_popular" <?php echo $v('is_popular') ? 'checked' : ''; ?>> Näita avalehel populaarsete hulgas</label></div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" name="save"><i class="fa fa-check"></i> Salvesta</button>
    </form>
<?php endif; ?>
<?php $content = ob_get_clean(); ?>
<?php include 'viewAdmin/templates/layout.php';
