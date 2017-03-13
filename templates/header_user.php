<?php Lang::get(); ?>
<!DOCTYPE html>
<html lang="<?= substr(Lang::get()->lang, 0, 2); ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, height=90, initial-scale=1, maximum-scale=1, user-scalable=yes">
        <meta name="yandex-verification" content="0e1ffd32ab2bd581" />
        <meta name="description" content="<?= $this->description; ?>">
        <meta name="keywords" content="<?= $this->keywords; ?>">
        <meta name="google-site-verification" content="zFHRWy3g1IULu3KWYRQFl16QAiQVCY_e5a8yG98RzXo" />
        <title><?= $this->title ? $this->title : "TopStar"; ?></title>
        <link rel="shortcut icon" href="<?= 'http://' . $GLOBALS['config']['domain']; ?>/assets/favicon.ico">
        <link rel="stylesheet" href="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/css/style.css">
        <link rel="stylesheet" href="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/css/slick.css">
        <link rel="stylesheet" href="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/css/slick-theme.css">
        <link rel="stylesheet" href="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/css/select2.min.css">
        <link href='http://fonts.googleapis.com/css?family=PT+Sans%7CPT+Serif+Caption%7CRoboto:500,700,400&subset=cyrillic,latin' rel='stylesheet' type='text/css'>
        <link type="text/css" rel="stylesheet" href="http://<?=$GLOBALS['config']['domain']?>/assets/css/jquery.selectBoxIt.css" />
        <link rel="stylesheet" href="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/css/responsive.css">
        <?php 
            // Проверяем, что это страница пользователя. Подключаем css Emoji
            preg_match("/id([0-9]*)/si", $_SERVER['REQUEST_URI'], $matches);
            if ( ( isset($matches[1]) && is_numeric( $matches[1] ) ) || ( $_SERVER['REQUEST_URI'] == '/me') ) { ?>
                <link href="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/css/emoji/nanoscroller.css" rel="stylesheet">
                <link href="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/css/emoji/emoji.css" rel="stylesheet">
        <?php } ?>
    </head>
  <body>
  <div class="content">
    <div class="mobile-navbar">
      <div class="navbar">
        <div class="container">
          <div class="navbar-header">
            <button class="navbar-toggle" type="button">
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a href="/" class="navbar-brand"><img src="../assets/images/sm-auth-logo.png" /></a>
            <?php
                if($this->data->authorized){
                    $user = UserModel::get()->getInfoById($this->data->whois['id']); ?>
                    <p class="navbar-text">
                      <a href="/id<?= $user->id; ?>"><img src="http://<?= $GLOBALS['config']['domain'] ?>/files/avatars/<?= isset($user->avatar) ? $user->avatar : 'unknown.png' ?>" /></a>
                      <span class="place">
                      <?php if(!empty($user->rating)) : ?>
                        <span><?= $user->rating; ?></span>
                        <i class="rating-up"></i>
                      <?php endif; ?>
                        <?php if(!empty($user->place)): ?>
                          <span><?= Lang::get()->translate('Place').": ".$user->place; ?></span>
                        <?php endif; ?>
                    </span>
                    </p>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  <header class="main-header">
        <div class="main-logo">
          <a href="/">
              <img alt="MyPop.Top" src="../assets/images/main-logo.png">
          </a>
        </div>
        <div class="main-banner">
          <img alt="Banner" src="../assets/images/main-banner.jpg">
        </div>    
  </header>
   