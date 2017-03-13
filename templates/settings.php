<?php
include_once 'header.php';
include_once 'aside.php'; ?>
<section class="main-content">
    <div class="settings-content-wrapper">
        <div class="settings-title">
            <span><?=  Lang::get()->translate('Settings');?></span>
        </div>
        <div class="setting-form-wrapper">
            <form enctype="multipart/form-data">
                <p><input class="secondary-input" name="name" type="text" placeholder="<?=  Lang::get()->translate('Name')?>"></p>
                <p><input class="secondary-input" name="surname" type="text" placeholder="<?=  Lang::get()->translate('Surname')?>"></p>
                <p><input class="secondary-input" name="middlename" type="text" placeholder="<?=  Lang::get()->translate('Middle name')?>"></p>
                <p>
                  <select name="activity_id" class="custom-select">
                      <option value="0"><?=Lang::get()->translate('Activity');?></option>
                      <?php foreach ($this->data->activities as $act): ?>
                      <option value="<?=$act->id?>"><?= Lang::get()->translate($act->name); ?></option>
                      <?php endforeach;?>
                  </select>
                </p>
                <p>
                  <select name="country" class="custom-select">
                      <option value="0"><?=Lang::get()->translate('Country');?></option>
                      <?php foreach ($this->data->countries as $country): ?>
                      <option value="<?=$country->id?>"><?= Lang::get()->translate($country->name); ?></option>
                      <?php endforeach;?>
                  </select>
                </p>
                <p>
                    <label><?=  Lang::get()->translate('Add other social networks'); ?>:</label>
                    <a href="<?=$this->vars['fblink'];?>"><img src="../assets/images/fb-icon.png"></a>
                    <a href="<?=$this->vars['vklink'];?>"><img src="../assets/images/vk-icon.png"></a>
                    <a href="<?=$this->vars['instalink'];?>"><img src="../assets/images/insta-icon.png"></a>
                    <a href="<?=$this->vars['glink'];?>"><img src="../assets/images/gplus-icon.png"></a>
                    <a href="<?=$this->vars['twlink'];?>"><img src="../assets/images/tw-icon.png"></a>
                    <a href="<?=$this->vars['oklink'];?>"><img src="../assets/images/ok-icon.png"></a>
                    <a href="<?=$this->vars['maillink'];?>"><img src="../assets/images/mail-icon.png"></a>
                </p>
                <p class="load-photo-wrapper">
                    <input type="file" name="photo" id="loadAvatar">
                    <label for="loadAvatar"><span class="helper"></span><span class="title"><?=Lang::get()->translate('Load photo');?></span></label>
                </p>
                <p>
                    <button class="btn standard-button"><?=Lang::get()->translate('Review');?></button>
                    <button class="btn standard-button"><?=  Lang::get()->translate('Save');?></button>
                </p>
            </form>
        </div>    
    </div>
    <div class="main-content-text">
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. </p>
        <p>In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. </p>
    </div>    
</section>
<?php 
 include_once 'footer.php';
?>
