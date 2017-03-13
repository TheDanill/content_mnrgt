<?php 
//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';
Lang::get();
?>
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
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/jquery-1.12.1.min.js"></script>
    <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/jquery-ui.min.js"></script>
    <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/jquery.selectBoxIt.min.js"></script>
    <script src="http://<?=$GLOBALS['config']['domain']?>/assets/js/select2.min.js"></script>
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/common.js"></script>
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="<?= 'http://' . $GLOBALS['config']['domain'] ?>/assets/js/slick.min.js"></script>
  </head>
  <body>
  <div class="content">    
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
   