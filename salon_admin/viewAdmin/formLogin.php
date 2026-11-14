<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lilia — haldus | Sisselogimine</title>
    <link rel="icon" href="../public/img/favicon.svg" type="image/svg+xml">
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/css/font-awesome.min.css" rel="stylesheet">
    <link href="public/css/admin.css" rel="stylesheet">
</head>
<body class="login-page">
    <form class="login-card" action="login" method="POST">
        <div class="login-logo"><i class="fa fa-leaf"></i></div>
        <h3>Lilia halduspaneel</h3>
        <p class="text-muted">Logi sisse administraatori kontoga</p>
        <?php if (isset($_SESSION['errorString'])): ?>
            <div class="alert alert-danger"><?php echo e($_SESSION['errorString']); unset($_SESSION['errorString']); ?></div>
        <?php endif; ?>
        <div class="form-group">
            <input type="email" name="email" class="form-control input-lg" placeholder="E-post" required autofocus>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control input-lg" placeholder="Parool" required>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit" name="btnLogin">Logi sisse</button>
        <p class="login-back"><a href="../"><i class="fa fa-arrow-left"></i> Tagasi veebisaidile</a></p>
    </form>
</body>
</html>
