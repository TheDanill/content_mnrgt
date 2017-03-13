<!DOCTYPE html>
<html>
    <head>
        <title><?= $this->title; ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="stylesheet" href="http://<?= $GLOBALS['config']['domain']; ?>/style/css/bootstrap.min.css">
        <link rel="stylesheet" href="http://<?= $GLOBALS['config']['domain']; ?>/style/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="http://<?= $GLOBALS['config']['domain']; ?>/style/main.css">
        <link rel="stylesheet" href="http://<?= $GLOBALS['config']['domain']; ?>/style/admin.css">
        <link rel="shortcut icon" href="http://<?= $GLOBALS['config']['domain']; ?>/style/favicon.ico" type="image/x-icon">
        <script src="http://yandex.st/jquery/2.0.0/jquery.min.js"></script>
        <script src="http://<?= $GLOBALS['config']['domain']; ?>/style/js/bootstrap.min.js"></script>
        <script src="http://<?= $GLOBALS['config']['domain']; ?>/style/js/doT.min.js"></script>
        <script src="http://<?= $GLOBALS['config']['domain']; ?>/style/main.js"></script>
        <script src="http://<?= $GLOBALS['config']['domain']; ?>/style/admin.js"></script>
        <script src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    </head>
    <body>
        <header class="container-fluid">
            <? if (AuthModel::get()->isAuthorized()) { ?>
            <div class="clearfix">
                <h1 class="col-lg-3 col-md-3 col-sm-4 col-xs-12"><a href="http://<?= $GLOBALS['config']['domain']; ?>/" class="show"><img src="/assets/images/main-logo.png" class="logo" alt="PopTop"></a></h1>
                <div class="col-lg-offset-6 col-lg-3 col-md-offset-6 col-md-3 col-sm-8 col-xs-12 text-right well-lg user-menu">
                    <div class="btn-group">
                        <a href="http://<?= $GLOBALS['config']['domain']; ?>/" class="btn btn-toolbar dropdown-toggle" data-toggle="dropdown"><span class="hidden-xs"><?= $this->user->name . ' ' . $this->user->surname; ?> <span class="caret"></span></span><span class="visible-xs icon-bars"></span></a>
                        <ul class="dropdown-menu text-left" role="menu">
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/">Моя страница</a></li>
                            <li class="disabled"><a href="http://<?= $GLOBALS['config']['domain']; ?>/history">Моя история</a></li>
                            <li class="disabled"><a href="http://<?= $GLOBALS['config']['domain']; ?>/stats">Моя статистика</a></li>
                            <li class="divider"></li>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/exercises/">Задания</a></li>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/blocks/">Блоки</a></li>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/variables/">Переменные</a></li>
                            <li class="divider"></li>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/users/">Пользователи</a></li>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/groups/">Группы</a></li>
                            <li class="divider"></li>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/auth/logout">Выйти</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <? } else { ?>
            <h1 class="text-center vertical-margin-center"><img src="/assets/images/main-logo.png" alt="PopTop"></h1>
            <? } ?>
        </header>
        <? if (AuthModel::get()->isAuthorized()) { ?>
        <div class="container-fluid">
            <div class="col-lg-12">
        <? } ?>
