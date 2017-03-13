<?php include_once 'header.php';
      include_once 'aside.php';
      //echo '<pre>';
      //print_r($this->data->userInfo);
      //echo '</pre>';?>
<section class="main-content">
    <div class="user-info-block-wrapper">
        <h1><?= __('Profile edit'); ?></h1>
        <form action="http://<?= $GLOBALS['config']['domain']; ?>/me/edit" method="post" enctype="multipart/form-data">
            <input type="hidden" name="secure_key" value="<?= $this->data->secure_key; ?>">
            <table class="profile-edit">
                <tr>
                    <td><?= __('Avatar'); ?>:</td>
                    <td>
                        <label for="avatar" class="file">
                            <img src="http://<?= $GLOBALS['config']['domain']?>/files/avatars/40x40/<?=isset($this->data->userInfo->avatar)?$this->data->userInfo->avatar:'unknown.png'?>"><br>
                            <input id="avatar" type="file" name="avatar" accept="image/*">
                        </label>
                    </td>
                </tr>
                <tr>
                    <td><?= __('Name'); ?>:</td>
                    <td>
                        <input type="text" name="name" value="<?= addslashes($this->data->userInfo->name); ?>">
                    </td>
                </tr>
                <tr>
                    <td><?= __('Surname'); ?>:</td>
                    <td>
                        <input type="text" name="surname" value="<?= addslashes($this->data->userInfo->surname); ?>">
                    </td>
                </tr>
                <?php if ($this->data->userInfo->verified == 2 ) { ?>
                    <tr>
                        <td><?= __('Nickname'); ?>:</td>
                        <td>
                            <input type="text" name="nickname" value="<?= addslashes($this->data->userInfo->nickname); ?>">
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><span data-hint="<?= __('For the international rating'); ?>"><?= __('Name') . " " . __('in English'); ?></span>:</td>
                    <td>
                        <input type="text" name="name_en" value="<?= addslashes($this->data->userInfo->name_en); ?>">
                    </td>
                </tr>
                <tr>
                    <td><span data-hint="<?= __('For the international rating'); ?>"><?= __('Surname') . " " . __('in English'); ?></span>:</td>
                    <td>
                        <input type="text" name="surname_en" value="<?= addslashes($this->data->userInfo->surname_en); ?>">
                    </td>
                </tr>
                <tr>
                    <td><?= __('Birthday'); ?>:</td>
                    <td>
                        <input type="date" name="birthday" value="<?= addslashes($this->data->userInfo->birthday); ?>">
                    </td>
                </tr>
                <tr>
                    <td><?= __('Activity'); ?>:</td>
                    <td>
                        <select name="activity_id" class="custom-select">
                            <option value=""><?= Lang::get()->translate('Activity'); ?></option>
                            <?php foreach ($this->data->activities as $act): ?>
                                <option value="<?= $act->id; ?>"<?php if ($this->data->userInfo->activity_id == $act->id) { echo " selected"; } ?>><?= Lang::get()->translate($act->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?= __('Country'); ?>:</td>
                    <td>
                        <select name="country_id" class="custom-select">
                            <option value=""><?= __('Country'); ?></option>
                            <?php foreach ($this->data->countries as $country): ?>
                                <option value="<?= $country->id; ?>"<?php if ($country->id == $this->data->userInfo->country_id) { echo " selected"; }?>><?= __($country->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?= __('City'); ?>:</td>
                    <td>
                        <select name="city_id" class="custom-select">
                            <option value=""><?= Lang::get()->translate('City'); ?></option>
                            <?php
                            if (count($this->data->cities)) {
                                foreach ($this->data->cities as $city) {
                            ?>
                                <option value="<?= $city->id; ?>"<?php if ($city->id == $this->data->userInfo->city_id) { echo " selected"; }?>><?= __($city->name); ?></option>
                            <?php
                                }
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <label><?=  Lang::get()->translate('Add other social networks'); ?>:</label>
                        <?php if (!$this->data->userInfo->facebook_profile_url) { ?><a href="<?=$this->vars['fblink'];?>"><img src="../assets/images/fb-icon.png"></a><?php } ?>
                        <?php if (!$this->data->userInfo->vkontakte_profile_url) { ?><a href="<?=$this->vars['vklink'];?>"><img src="../assets/images/vk-icon.png"></a><?php } ?>
                        <?php if (!$this->data->userInfo->instagram_profile_url) { ?><a href="<?=$this->vars['instalink'];?>"><img src="../assets/images/insta-icon.png"></a><?php } ?>
                        <?php if (!$this->data->userInfo->google_profile_url) { ?><a href="<?=$this->vars['glink'];?>"><img src="../assets/images/gplus-icon.png"></a><?php } ?>
                        <?php if (!$this->data->userInfo->twitter_profile_url) { ?><a href="<?=$this->vars['twlink'];?>"><img src="../assets/images/tw-icon.png"></a><?php } ?>
                        <?php if (!$this->data->userInfo->odnoklassniki_profile_url) { ?><a href="<?=$this->vars['oklink'];?>"><img src="../assets/images/ok-icon.png"></a><?php } ?>
                        <?php if (!$this->data->userInfo->mailru_profile_url) { ?><a href="<?=$this->vars['maillink'];?>"><img src="../assets/images/mail-icon.png"></a><?php } ?>
                    </td>
                </tr>
                <tr>
                    <td><?= __('Default language'); ?>:</td>
                    <td>
                        <select name="default_lang" class="custom-select">
                            <option value=""><?= __('Default language'); ?></option>
                            <?php foreach ($this->data->languages as $language): ?>
                                <option value="<?= $language->id; ?>"<?php if ($language->id == $this->data->userInfo->default_lang) { echo " selected"; }?>><?= __($language->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?= __('Gender'); ?>:</td>
                    <td>
                        <select name="gender" class="custom-select">
                            <option value="0"><?= Lang::get()->translate('Gender'); ?></option>
                            <option value="M"<?php if ($this->data->userInfo->gender == "M") { echo " selected"; } ?>><?= Lang::get()->translate('Male'); ?></option>
                            <option value="W"<?php if ($this->data->userInfo->gender == "W") { echo " selected"; } ?>><?= Lang::get()->translate('Female'); ?></option>
                            <!--<option value="A"><?= Lang::get()->translate('Any'); ?></option>-->
                        </select>
                    </td>
                </tr>
                <?php if ($this->data->userInfo->verified == 2 ) { ?>
                    <tr>
                        <td><?= __('Wikipedia'); ?>:</td>
                        <td>
                            <input type="text" name="wikipedia" value="<?= addslashes($this->data->userInfo->wikipedia); ?>">
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?= __('Description'); ?>:</td>
                    <td>
                        <textarea name="description" rows="5"><?= $this->data->userInfo->description; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <a href="http://<?= $GLOBALS['config']['domain']; ?>/me" class="btn btn-text"><?= __('Cancel'); ?></a>
                        <button type="submit" class="btn standard-button"><?=  Lang::get()->translate('Save');?></button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</section>
<?php 
 include_once 'footer.php';
?>