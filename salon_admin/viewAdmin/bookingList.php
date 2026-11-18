<?php
$back = http_build_query(array_intersect_key($_GET, array('status' => 1, 'upcoming' => 1)));
ob_start();
?>
<h2>Broneeringud</h2>

<ul class="nav nav-pills filter-pills">
    <li class="<?php echo $status == '' && !$upcoming ? 'active' : ''; ?>"><a href="bookingsAdmin">Kõik</a></li>
    <li class="<?php echo $upcoming ? 'active' : ''; ?>"><a href="bookingsAdmin?upcoming=1">Eelseisvad</a></li>
    <?php foreach ($statuses as $key => $label): ?>
        <li class="<?php echo $status == $key ? 'active' : ''; ?>"><a href="bookingsAdmin?status=<?php echo $key; ?>"><?php echo e($label); ?></a></li>
    <?php endforeach; ?>
</ul>

<div class="panel panel-default">
    <div class="table-wrap">
        <table class="table table-hover table-cards">
            <tr><th>#</th><th>Aeg</th><th>Klient</th><th>Teenus / meister</th><th>Lisainfo</th><th>Staatus</th><th></th></tr>
            <?php foreach ($arr as $b): ?>
                <tr>
                    <td><?php echo (int)$b['id']; ?></td>
                    <td><b><?php echo e(date('d.m.Y', strtotime($b['booking_date']))); ?></b><br><?php echo e(substr($b['booking_time'], 0, 5)); ?></td>
                    <td><?php echo e($b['client_name']); ?><br>
                        <small><a href="tel:<?php echo e($b['phone']); ?>"><?php echo e($b['phone']); ?></a></small>
                        <?php if ($b['email']): ?><br><small><?php echo e($b['email']); ?></small><?php endif; ?>
                        <?php if ($b['account_email']): ?><br><span class="label label-info" title="Broneeritud kontoga"><i class="fa fa-user"></i> konto</span><?php endif; ?>
                    </td>
                    <td><?php echo e($b['title_et']); ?><br><small class="text-muted"><?php echo e($b['master_name']); ?></small></td>
                    <td><small><?php echo e($b['note']); ?></small></td>
                    <td>
                        <form method="POST" action="bookingStatus" class="inline">
                            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                            <input type="hidden" name="back" value="<?php echo e($back); ?>">
                            <select name="status" class="form-control input-sm status-select status-<?php echo e($b['status']); ?>" onchange="this.form.submit()">
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $b['status'] == $key ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="bookingDel" class="inline" onsubmit="return confirm('Kustutada broneering?');">
                            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                            <input type="hidden" name="back" value="<?php echo e($back); ?>">
                            <button class="btn btn-link text-danger" type="submit" title="Kustuta"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$arr): ?><tr><td colspan="7" class="text-muted">Broneeringuid pole.</td></tr><?php endif; ?>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include 'viewAdmin/templates/layout.php';
