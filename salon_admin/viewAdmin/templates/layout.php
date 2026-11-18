<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lilia — haldus</title>
    <link rel="icon" href="../public/img/favicon.svg" type="image/svg+xml">
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/css/font-awesome.min.css" rel="stylesheet">
    <link href="public/css/admin.css" rel="stylesheet">
    <script src="public/js/jquery.min.js"></script>
    <script src="public/js/bootstrap.min.js"></script>
</head>
<body>
<?php $uri = explode('?', $_SERVER['REQUEST_URI'])[0]; $current = substr($uri, strrpos($uri, '/') + 1); ?>
<nav class="navbar navbar-default navbar-static-top admin-nav">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#admin-menu" aria-expanded="false">
                <span class="sr-only">Menüü</span>
                <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="dashboard"><i class="fa fa-leaf"></i> Lilia <small>haldus</small></a>
        </div>
        <div class="collapse navbar-collapse" id="admin-menu">
            <ul class="nav navbar-nav">
                <li class="<?php echo $current == 'dashboard' ? 'active' : ''; ?>"><a href="dashboard"><i class="fa fa-dashboard"></i> Töölaud</a></li>
                <li class="<?php echo strpos($current, 'service') === 0 ? 'active' : ''; ?>"><a href="servicesAdmin"><i class="fa fa-list"></i> Teenused</a></li>
                <li class="<?php echo strpos($current, 'categor') === 0 ? 'active' : ''; ?>"><a href="categoriesAdmin"><i class="fa fa-tags"></i> Kategooriad</a></li>
                <li class="<?php echo strpos($current, 'booking') === 0 ? 'active' : ''; ?>"><a href="bookingsAdmin"><i class="fa fa-calendar"></i> Broneeringud</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="../" target="_blank"><i class="fa fa-external-link"></i> Veebisait</a></li>
                <li><a href="logout"><i class="fa fa-user"></i> <?php echo e($_SESSION['name']); ?> · Välju <i class="fa fa-sign-out"></i></a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container admin-content">
    <?php echo $content; ?>
</div>

<script>
// Mobiilis kuvatakse tabelid kaartidena: igale lahtrile lisatakse veeru pealkiri (data-label)
document.querySelectorAll('table.table-cards').forEach(function (table) {
    var head = table.querySelector('tr');
    if (!head || !head.querySelector('th')) return;
    head.classList.add('head-row');
    var labels = Array.prototype.map.call(head.children, function (th) { return th.textContent.trim(); });
    table.querySelectorAll('tr').forEach(function (tr) {
        if (tr === head) return;
        Array.prototype.forEach.call(tr.children, function (td, i) {
            if (labels[i] && !td.hasAttribute('colspan')) td.setAttribute('data-label', labels[i]);
        });
    });
});
</script>

<footer class="admin-footer container">&copy; <?php echo date('Y'); ?> Lilia ilusalong · halduspaneel</footer>
</body>
</html>
