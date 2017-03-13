<?php
    include_once 'header.php';
    include_once 'aside.php';
?>

<section class="main-content feedback">
    <h1><?= __('Feedback'); ?></h1>
    <form class="ajax-form" action="/send" method="post">
        <input type='text' class="required" name="name" placeholder="<?= Lang::get()->translate('Your name'); ?>" />
        <input type="email" class="required" name="email" placeholder="<?= Lang::get()->translate('Email'); ?>" />
        <textarea name="message" placeholder="<?= Lang::get()->translate('Message'); ?>"></textarea>
        <button type="submit" class="standard-button"><?= Lang::get()->translate('Send message'); ?></button>
    </form>
</section>    

<?php include_once 'footer.php'; ?>