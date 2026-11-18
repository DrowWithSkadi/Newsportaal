<?php ob_start(); ?>
<h2>Tere, <?php echo e($_SESSION['name']); ?>!</h2>

<div class="row stats-row">
    <div class="col-xs-6 col-md-3"><a class="stat-box" href="bookingsAdmin?status=new"><strong><?php echo (int)$stats['new']; ?></strong>uut broneeringut</a></div>
    <div class="col-xs-6 col-md-3"><a class="stat-box" href="bookingsAdmin?upcoming=1"><strong><?php echo (int)$stats['upcoming']; ?></strong>eelseisvat aega</a></div>
    <div class="col-xs-6 col-md-3"><a class="stat-box" href="servicesAdmin"><strong><?php echo (int)$stats['services']; ?></strong>teenust</a></div>
    <div class="col-xs-6 col-md-3"><a class="stat-box" href="categoriesAdmin"><strong><?php echo (int)$stats['categories']; ?></strong>kategooriat</a></div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-calendar"></i> Eelseisvad broneeringud</div>
    <div class="table-wrap">
        <table class="table table-hover table-cards">
            <tr><th>Kuupäev</th><th>Aeg</th><th>Klient</th><th>Teenus</th><th>Meister</th><th>Staatus</th></tr>
            <?php foreach ($upcoming as $b): ?>
                <tr>
                    <td><?php echo e(date('d.m.Y', strtotime($b['booking_date']))); ?></td>
                    <td><?php echo e(substr($b['booking_time'], 0, 5)); ?></td>
                    <td><?php echo e($b['client_name']); ?><br><small class="text-muted"><?php echo e($b['phone']); ?></small></td>
                    <td><?php echo e($b['title_et']); ?></td>
                    <td><?php echo e($b['master_name']); ?></td>
                    <td><span class="status status-<?php echo e($b['status']); ?>"><?php echo e(modelAdminBooking::STATUSES[$b['status']]); ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$upcoming): ?><tr><td colspan="6" class="text-muted">Eelseisvaid broneeringuid pole.</td></tr><?php endif; ?>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include 'viewAdmin/templates/layout.php';
