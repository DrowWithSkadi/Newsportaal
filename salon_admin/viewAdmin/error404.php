<?php ob_start(); ?>
<div class="text-center" style="padding:60px 0">
    <h1 style="font-size:72px;color:#9c5b74">404</h1>
    <h3>Lehte ei leitud</h3>
    <p class="text-muted">Sellist lehte halduspaneelis ei ole.</p>
    <a class="btn btn-primary" href="dashboard">Töölauale</a>
</div>
<?php $content = ob_get_clean(); ?>
<?php include 'viewAdmin/templates/layout.php';
