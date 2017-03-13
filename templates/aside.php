<aside>
    <div class="main-menu-aside">

        <?php if (!$this->data->authorized) { ?>

            <div class="main-menu-header-block main-header-block">
                <div class="block-header-title left">
                    <a ><?= Lang::get()->translate('Add yourself'); ?></a>
                </div>
                <div class="block-header-title right">
                    <a ><?= Lang::get()->translate('Enter'); ?></a>
                </div>    
            </div>
            <div class="main-menu-enter-form-block hidden">
                <p class="title"><?= Lang::get()->translate('Enter with'); ?></p>
                <div class="socnet-slider-wrapper">
                    <div class="socnet-slider-block">
                        <a href="<?= $this->vars['fblink']; ?>"><img src="../assets/images/fb-icon.png"></a>
                        <a href="<?= $this->vars['vklink']; ?>"><img src="../assets/images/vk-icon.png"></a>
                        <a href="<?= $this->vars['instalink']; ?>"><img src="../assets/images/insta-icon.png"></a>
                        <a href="<?= $this->vars['glink']; ?>"><img src="../assets/images/gplus-icon.png"></a>
                        <a href="<?= $this->vars['twlink']; ?>"><img src="../assets/images/tw-icon.png"></a>
                        <a href="<?= $this->vars['oklink']; ?>"><img src="../assets/images/ok-icon.png"></a>
                        <a href="<?= $this->vars['maillink']; ?>"><img src="../assets/images/mail-icon.png"></a>
                    </div>
                </div>
                <p class="title"><?= Lang::get()->translate('Or fill out the form'); ?></p>
                <form action="/auth/login" method="POST" id="enterForm">
                    <p><input type="text" name="login" placeholder="<?= Lang::get()->translate('Email or login') ?>"></p>
                    <p><input type="password" name="password" placeholder="<?= Lang::get()->translate('Password') ?>"><span class="hidden-pass pass-action"></span></p>
                    <div class="main-menu-enter-form-row">
                        <div class="main-menu-enter-form-col left">
                            <input type="checkbox" class="custom-checkbox" name="rememberme" id="rememberme">
                            <label for="rememberme"></label>
                            <span><?= Lang::get()->translate('Remember') ?></span>
                        </div>
                        <div class="main-menu-enter-form-col right">
                            <a href="/"><?= Lang::get()->translate('Forgot password?'); ?></a>
                        </div>
                    </div>
                    <div class="main-menu-enter-form-row">
                        <div class="main-menu-enter-form-col left">
                            <button class="standard-button btn"><?= Lang::get()->translate('Enter'); ?></button>
                        </div>
                        <div class="main-menu-enter-form-col right">
                            <span class="error hidden" id="error1"><?= Lang::get()->translate('Enter email or login'); ?></span>
                            <span class="error hidden" id="error2"><?= Lang::get()->translate('Enter password'); ?></span>
                        </div>   
                    </div>    
                </form>
            </div>

        <?php } else { ?>

            <div class="main-menu-header-block main-header-block">
                <div class="block-header-title left">
                    <?= Lang::get()->translate('Search'); ?>
                </div>
                <div class="block-header-title right">
                    <a href="/logout"><?= Lang::get()->translate('Log out'); ?></a>
                </div> 
            </div>
            <div class='sidebar-user-info'>
                <?php echo Lang::get()->translate('Hello') . Lang::get()->translate(',') . ' '; ?>
                <?php if ($this->data->userInfo->id != $this->data->whois['id']) { ?>
                    <a href="/id<?php echo $this->data->whois['id']; ?>">
                        <?php
                            if (!empty($this->data->whois['name']) && !empty($this->data->whois['surname'])) {
                                echo $this->data->whois['name'] . " " . $this->data->whois['surname'];
                            } else {
                                echo $this->data->whois['nickname'];
                            }
                        ?>
                    </a>
                <?php } else { ?>
                    <?php
                        if (!empty($this->data->whois['name']) && !empty($this->data->whois['surname'])) {
                            echo $this->data->whois['name'] . " " . $this->data->whois['surname'];
                        } else {
                            echo $this->data->whois['nickname'];
                        }
                    ?>
                <?php } ?>
            </div>

        <?php } ?>

        <div class="main-menu-content-block">
            <div class="loading hidden">
                <div id="circularG">
                    <div id="circularG_1" class="circularG"></div>
                    <div id="circularG_2" class="circularG"></div>
                    <div id="circularG_3" class="circularG"></div>
                    <div id="circularG_4" class="circularG"></div>
                    <div id="circularG_5" class="circularG"></div>
                    <div id="circularG_6" class="circularG"></div>
                    <div id="circularG_7" class="circularG"></div>
                    <div id="circularG_8" class="circularG"></div>
                </div>
            </div>  
            <form action="/" method="GET" id="mainSearchForm" >
                <p id="search-main-form-wrapper">
                    <input type="text" name="search" id="search-main-form" placeholder="<?= Lang::get()->translate('Search'); ?>">
                </p>
                <p>
                    <select name="activity_id" class="custom-select">
                        <option value="0"><?= Lang::get()->translate('Activity'); ?></option>
                        <?php foreach ($this->data->activities as $act): ?>
                            <option value="<?= $act->id ?>"><?= Lang::get()->translate($act->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <select name="country" class="custom-select">
                        <option value="0"><?= Lang::get()->translate('Country'); ?></option>
                        <?php foreach ($this->data->countries as $country): ?>
                            <option value="<?= $country->id ?>"><?= Lang::get()->translate($country->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <select name="city" class="custom-select"  disabled>
                        <option  value="0"><?= Lang::get()->translate('City'); ?></option>

                    </select>
                </p>
                <p>
                    <select name="age" class="custom-select">
                        <option value="0"><?= Lang::get()->translate('Age'); ?></option>
                        <option value="1">0 - 20</option>
                        <option value="2">20 - 25</option>
                        <option value="3">25 - 30</option>
                        <option value="4">30 - 35</option>
                        <option value="5">35 - 40</option>
                        <option value="6">40 - 45</option>
                        <option value="7">45 - 50</option>
                        <option value="8">50+</option>
                    </select>
                </p>
                <p>
                    <select name="gender" class="custom-select">
                        <option value="0"><?= Lang::get()->translate('Gender'); ?></option>
                        <option value="M"><?= Lang::get()->translate('Male'); ?></option>
                        <option value="W"><?= Lang::get()->translate('Female'); ?></option>
                        <!--<option value="A"><?= Lang::get()->translate('Any'); ?></option>-->
                    </select>
                </p>
                <p>
                    <button name="searching" class="btn standard-button"><?= Lang::get()->translate('Search'); ?></button>
                </p>
            </form>
        </div>    
    </div>
    <div class="main-banner-aside">
        <div class="aside-banner-item">
            <div class="aside-banner-image">
                <img src="http://<?= $GLOBALS['config']['domain'] ?>/assets/images/di-caprio.jpg">
            </div>
            <div class="aside-banner-title">
                <span>Леонардо Ди Каприо получил Оскар!</span>
            </div>    
        </div> 
        <div class="aside-banner-item">
            <div class="aside-banner-image">
                <img src="http://<?= $GLOBALS['config']['domain'] ?>/assets/images/di-caprio.jpg">
            </div>
            <div class="aside-banner-title">
                <span>Леонардо Ди Каприо получил Оскар!</span>
            </div>    
        </div> 
    </div>    
</aside>   