<aside>
    <div class="main-menu-aside">
      <div class="main-menu-header-block main-header-block">
          <div class="block-header-title left">
              <a ><?=Lang::get()->translate('Add yourself');?></a>
          </div>
          <div class="block-header-title right">
              <a ><?=Lang::get()->translate('Enter');?></a>
          </div>    
      </div>
      <div class="main-menu-enter-form-block hidden">
          <p class="title"><?=Lang::get()->translate('Enter with');?></p>
          <div class="socnet-slider-wrapper">
              <div class="socnet-slider-block">
                  <a href="<?=$this->vars['fblink'];?>"><img src="../assets/images/fb-icon.png" alt="Facebook"></a>
                  <a href="<?=$this->vars['vklink'];?>"><img src="../assets/images/vk-icon.png" alt="Vkontakte"></a>
                  <a href="<?=$this->vars['instalink'];?>"><img src="../assets/images/insta-icon.png" alt="Instagram"></a>
                  <a href="<?=$this->vars['glink'];?>"><img src="../assets/images/gplus-icon.png" alt="Google+"></a>
                  <a href="<?=$this->vars['twlink'];?>"><img src="../assets/images/tw-icon.png" alt="Twitter"></a>
                  <a href="<?=$this->vars['oklink'];?>"><img src="../assets/images/ok-icon.png" alt="Odnoklassniki"></a>
                  <a href="<?=$this->vars['maillink'];?>"><img src="../assets/images/mail-icon.png" alt="Mail.Ru"></a>
                  <a href="<?=$this->vars['fblink'];?>"><img src="../assets/images/fb-icon.png" alt="Facebook"></a>
              </div>
          </div>
          <p class="title"><?=Lang::get()->translate('Or fill out the form');?></p>
          <form action="/auth/login" method="POST" id="enterForm">
              <p><input type="text" name="login" placeholder="<?=Lang::get()->translate('Email or login')?>"></p>
              <p><input type="password" name="password" placeholder="<?=Lang::get()->translate('Password')?>"><span class="hidden-pass pass-action"></span></p>
              <div class="main-menu-enter-form-row">
                  <div class="main-menu-enter-form-col left">
                      <input type="checkbox" class="custom-checkbox" name="rememberme" id="rememberme">
                      <label for="rememberme"></label>
                      <span><?=  Lang::get()->translate('Remember')?></span>
                  </div>
                  <div class="main-menu-enter-form-col right">
                      <a href="/"><?= Lang::get()->translate('Forgot password?');?></a>
                  </div>
              </div>
              <div class="main-menu-enter-form-row">
                  <div class="main-menu-enter-form-col left">
                      <button class="standard-button btn"><?=  Lang::get()->translate('Enter');?></button>
                  </div>
                  <div class="main-menu-enter-form-col right">
                      <span class="error hidden" id="error1"><?=  Lang::get()->translate('Enter email or login');?></span>
                      <span class="error hidden" id="error2"><?=  Lang::get()->translate('Enter password');?></span>
                  </div>   
              </div>    
          </form>
      </div>
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
          <form method="GET" action="/" id="mainSearchForm">
              <p id="search-main-form-wrapper">
                  <input type="text" name="search" id="search-main-form" placeholder="<?=Lang::get()->translate('Search');?>" value="<?=isset($_GET['search'])?$_GET['search']:''?>">
              </p>
              <p>
                  <select name="activity_id" class="custom-select">
                      <option value="0"><?=Lang::get()->translate('Activity');?></option>
                      <?php (isset($_GET['activity_id']) && !empty($_GET['activity_id']))? $act_id = $_GET['activity_id']:$act_id = 0; ?>
                      <?php foreach ($this->data->activities as $act): ?>
                      <option value="<?=$act->id?>" <?=($act_id == $act->id)?'selected':'';?>><?= Lang::get()->translate($act->name); ?></option>
                      <?php endforeach;?>
                  </select>
              </p>
              <p>
                  <select name="country" class="custom-select">
                      <option value="0"><?=Lang::get()->translate('Country');?></option>
                      <?php (isset($_GET['country']) && $_GET['country'] != 0)? $coun_id = $_GET['country']:$coun_id = 0; ?>
                      <?php foreach ($this->data->countries as $country): echo $coun_id;?>
                      <option value="<?=$country->id?>"  <?=($coun_id == $country->id)?'selected':'';?>><?= Lang::get()->translate($country->name); ?></option>
                      <?php endforeach;?>
                  </select>
              </p>
              <p>
                  <select name="city" class="custom-select" <?=(isset($this->data->cities)&&!empty($this->data->cities))?'':'disabled'?>>
                      <option  value="0"><?=Lang::get()->translate('City');?></option>
                      <?php if(isset($this->data->cities) && !empty($this->data->cities)) {
                                (isset($_GET['city'])&&!empty($_GET['city']))?$city_id = $_GET['city']:$city_id=0;
                                foreach ($this->data->cities as $city):?>
                      <option value="<?=$city->id?>"  <?=($city_id == $city->id)?'selected':'';?>><?=Lang::get()->translate($city->name);?></option>
                      <?php     endforeach;
                           } ?>
                  </select>
              </p>
              <p>
                  <select name="age" class="custom-select">
                      <?php (isset($_GET['age']) && !empty($_GET['age']))? $age_id = $_GET['age']:$age_id = 0; ?>
                      <option value="0"><?=Lang::get()->translate('Age');?></option>
                      <option value="1"  <?=($age_id == 1)?'selected':'';?>>0 - 20</option>
                      <option value="2"  <?=($age_id == 2)?'selected':'';?>>20 - 25</option>
                      <option value="3"  <?=($age_id == 3)?'selected':'';?>>25 - 30</option>
                      <option value="4"  <?=($age_id == 4)?'selected':'';?>>30 - 35</option>
                      <option value="5"  <?=($age_id == 5)?'selected':'';?>>35 - 40</option>
                      <option value="6"  <?=($age_id == 6)?'selected':'';?>>40 - 45</option>
                      <option value="7"  <?=($age_id == 7)?'selected':'';?>>45 - 50</option>
                      <option value="8"  <?=($age_id == 8)?'selected':'';?>>50+</option>
                  </select>
              </p>
              <p>
                  <select name="gender" class="custom-select">
                      <?php (isset($_GET['gender']) && !empty($_GET['gender']))? $g_id = $_GET['gender']:$g_id = 0;     echo $g_id;?>
                      <option value="0" <?=($g_id === '0')?'selected':'';?>><?=Lang::get()->translate('Gender');?></option>
                      <option value="M" <?=($g_id === 'M')?'selected':'';?>><?=Lang::get()->translate('Male');?></option>
                      <option value="W" <?=($g_id === 'W')?'selected':'';?>><?=Lang::get()->translate('Female');?></option>
                      <!--<option value="A"><?=Lang::get()->translate('Any');?></option>-->
                  </select>
              </p>
              <p>
                  <button class="btn standard-button"><?=  Lang::get()->translate('Search');?></button>
              </p>
          </form>
      </div>    
    </div>
    <div class="main-banner-aside">
        <div class="aside-banner-item">
            <div class="aside-banner-image">
                <img src="http://<?= $GLOBALS['config']['domain'] ?>/assets/images/di-caprio.jpg" alt="Леонардо Ди Каприо получил Оскар!">
            </div>
            <div class="aside-banner-title">
                <span>Леонардо Ди Каприо получил Оскар!</span>
            </div>    
        </div> 
        <div class="aside-banner-item">
            <div class="aside-banner-image">
                <img src="http://<?= $GLOBALS['config']['domain'] ?>/assets/images/di-caprio.jpg" alt="Леонардо Ди Каприо получил Оскар!">
            </div>
            <div class="aside-banner-title">
                <span>Леонардо Ди Каприо получил Оскар!</span>
            </div>    
        </div> 
    </div>    
  </aside>   