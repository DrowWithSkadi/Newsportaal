<!DOCTYPE html>
<html lang="<?php echo Lang::current(); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' — ' : ''; ?><?php echo e(t('site_name')); ?></title>
    <meta name="description" content="<?php echo e(t('hero_text')); ?>">
    <link rel="icon" href="public/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Manrope:wght@400;500;600;700&display=swap&subset=cyrillic,latin-ext" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <header class="site-header" id="top">
        <div class="container header-inner">
            <a class="logo" href="./">
                <?php echo Icon::svg('lotus', 'logo-icon'); ?>
                <span>Lilia<small><?php echo Lang::current() == 'et' ? 'ilusalong' : 'салон красоты'; ?></small></span>
            </a>

            <button class="burger" type="button" aria-label="<?php echo e(t('menu')); ?>" aria-expanded="false" aria-controls="main-nav">
                <?php echo Icon::svg('menu', 'icon icon-open') . Icon::svg('close', 'icon icon-close'); ?>
            </button>

            <nav class="main-nav" id="main-nav">
                <ul class="topmenu">
                    <li><a href="./"><?php echo e(t('nav_home')); ?></a></li>
                    <li class="has-sub">
                        <a href="services"><?php echo e(t('nav_services')); ?> <?php echo Icon::svg('chevron', 'icon chev'); ?></a>
                        <ul class="submenu">
                            <li><a href="services"><?php echo e(t('nav_all_services')); ?></a></li>
                            <?php foreach (Controller::AllCategory() as $cat): ?>
                                <li><a href="category?id=<?php echo (int)$cat['id']; ?>"><?php echo e(Lang::field($cat, 'name')); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="masters"><?php echo e(t('nav_masters')); ?></a></li>
                    <li><a href="contact"><?php echo e(t('nav_contact')); ?></a></li>
                </ul>

                <form class="search-form" action="search" method="GET" role="search">
                    <input type="search" name="q" placeholder="<?php echo e(t('search_placeholder')); ?>"
                           value="<?php echo isset($_GET['q']) ? e($_GET['q']) : ''; ?>" aria-label="<?php echo e(t('search_btn')); ?>">
                    <button type="submit" aria-label="<?php echo e(t('search_btn')); ?>"><?php echo Icon::svg('search'); ?></button>
                </form>

                <div class="lang-switch" aria-label="Language">
                    <?php foreach (Lang::SUPPORTED as $l): ?>
                        <a href="<?php echo Lang::switchUrl($l); ?>" class="<?php echo $l == Lang::current() ? 'active' : ''; ?>" hreflang="<?php echo $l; ?>"><?php echo strtoupper($l); ?></a>
                    <?php endforeach; ?>
                </div>

                <a class="btn btn-nav" href="booking"><?php echo e(t('nav_book')); ?></a>
            </nav>

            <?php $authUser = Auth::user(); ?>
            <div class="account">
                <?php if ($authUser): ?>
                    <a class="account-link" href="myBookings" title="<?php echo e(t('account_menu')); ?>">
                        <?php echo Icon::svg('user'); ?><span><?php echo e($authUser['name']); ?></span>
                    </a>
                    <form method="POST" action="logout" class="logout-form">
                        <?php echo Auth::csrfField(); ?>
                        <button type="submit" class="icon-btn" title="<?php echo e(t('logout')); ?>" aria-label="<?php echo e(t('logout')); ?>"><?php echo Icon::svg('logout'); ?></button>
                    </form>
                <?php else: ?>
                    <a class="account-link" href="login?return=<?php echo urlencode(Controller::currentPath()); ?>">
                        <?php echo Icon::svg('user'); ?><span><?php echo e(t('login')); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <?php if ($flash = Controller::takeFlash()): ?>
            <div class="container flash"><div class="alert alert-<?php echo $flash['type']; ?>"><?php echo e($flash['text']); ?></div></div>
        <?php endif; ?>
        <?php
        if (isset($content)) {
            echo $content;
        }
        ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <a class="logo logo-light" href="./"><?php echo Icon::svg('lotus', 'logo-icon'); ?><span>Lilia</span></a>
                <p><?php echo e(t('footer_text')); ?></p>
            </div>
            <div>
                <h4><?php echo e(t('nav_contact')); ?></h4>
                <p><?php echo Icon::svg('pin'); ?> <?php echo e(t('address_value')); ?></p>
                <p><?php echo Icon::svg('phone'); ?> <a href="tel:+37255501234">+372 5550 1234</a></p>
                <p><?php echo Icon::svg('mail'); ?> <a href="mailto:info@lilia.ee">info@lilia.ee</a></p>
            </div>
            <div>
                <h4><?php echo e(t('hours')); ?></h4>
                <p><?php echo e(t('hours_weekdays')); ?></p>
                <p><?php echo e(t('hours_saturday')); ?></p>
                <p><?php echo e(t('hours_sunday')); ?></p>
            </div>
        </div>
        <p class="copyright">&copy; <?php echo date('Y'); ?> Lilia · JPTV24 · <?php echo e(t('rights')); ?></p>
    </footer>

    <button class="to-top" type="button" aria-label="<?php echo e(t('to_top')); ?>"><?php echo Icon::svg('arrow-up'); ?></button>

    <script src="public/js/main.js"></script>
</body>
</html>
