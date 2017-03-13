<?php
include_once 'header.php';
?>
<div class="container">
    <h2 class="col-lg-12">Войти с помощью:</h2>
    <div class="col-lg-12">
        <a class="btn btn-primary" href="<?=$this->vars['vklink'];?>">
        Вконтакте
        </a>
        <a class="btn btn-primary" href="<?=$this->vars['fblink'];?>">
        Facebook
        </a>
        <a class="btn btn-primary" href="<?=$this->vars['instalink'];?>">
        Instagram
        </a>
        <a class="btn btn-primary" href="<?=$this->vars['glink'];?>">
        Google+
        </a>
        <a class="btn btn-primary" href="<?=$this->vars['twlink'];?>">
        Twitter 
        </a>      
        <a class="btn btn-primary" href="<?=$this->vars['oklink'];?>">
        Одноклассники
        </a> 
        <a class="btn btn-primary" href="<?=$this->vars['maillink'];?>">
        Mail.Ru
        </a>
        <a class="btn btn-primary" href="<?=$this->vars['linlink'];?>">
        LinkedIn
        </a>
        <a class="btn btn-primary" href="<?=$this->vars['weibolink'];?>">
        Weibo
        </a>
        <a class="btn btn-primary" href="<?=$this->vars['qzonelink'];?>">
        Qzone
        </a>
    </div>
    <h3>Или заполните форму:</h3>
    <ul class="nav nav-tabs">
        <li  class="active"><a data-toggle="pill" href="#login">Войти</a></li>
        <li ><a data-toggle="pill" href="#register">Регистрация</a></li>
    </ul>
    <div id="login" class="tab-pane fade in active">
        <form role="form" action="/auth/login">
            <div class="form-group"><input class="form-control" name="login" type="text" placeholder="Логин или email"></div>
            <div class="form-group"><input class="form-control" name="password" type="password" placeholder="Пароль"></div>
            <div class="form-group"><input class="form-control" type="submit" value="Я все заполнил!"></div>
        </form>
    </div>
    <div id="register" class="tab-pane fade">
        <form role="form" action="/auth/register">
            <div class="form-group"><input class="form-control" name="name" type="text" placeholder="Имя"></div>
            <div class="form-group"><input class="form-control" name="surname" type="text" placeholder="Фамилия"></div>
            <div class="form-group"><input class="form-control" type="middlename" placeholder="Отчество"></div>
            <div class="form-group"><input class="form-control" type="password" placeholder="Пароль"></div>
            <div class="form-group"><input class="form-control" type="submit" value="Я все заполнил!"></div>
        </form>
    </div>
</div>


