<?php
include_once 'header.php';
include_once 'auth-aside.php';
?>
<section class="main-content">
      <div class="mainTablePeople-wrapper">  
          <table class="people-list" id="mainTablePeople">
              <thead class="main-header-block">
                  <tr>
                      <th class="mt-position"><?=Lang::get()->translate('Place');?></th>
                      <th class="mt-name"><?=Lang::get()->translate('People (stars)');?></th>
                      <th class="mt-rating"><?=Lang::get()->translate('Rating');?></th>
                      <th class="mt-activity"><?=Lang::get()->translate('Field of activity');?></th>
                      <th class="mt-country"><?=Lang::get()->translate('Country');?></th>
                  </tr>
                  <tr class="hidden for-copy">
                      <td class="mt-position"></td>
                      <td class="mt-name">
                          <img data-src="http://<?=$GLOBALS['config']['domain'];?>/files/avatars/">     
                          <span></span>
                      </td>
                      <td class="mt-rating">
                            <span></span>
                            <i class=""></i>
                      </td>
                      <td class="mt-activity"></td>
                      <td class="mt-country"></td>
                  </tr>
              </thead>
              <tbody>
                 <?php
                  $i = 1;
                  foreach($this->data->users as $user): ?>
                  <tr>
                      <td class="mt-position">
                          <?= $i;?>
                      </td>
                      <td class="mt-name">
                         <img src="http://<?= $GLOBALS['config']['domain'] ?>/files/avatars/<?=(isset($user->avatar))?$user->avatar:'unknown.png'?>">     
                         <span><?=$user->name;?></span>
                      </td>
                      <td class="mt-rating">
                          <span><?=$user->rating;?></span>
                          <i class="rating-up"></i>
                      </td>
                      <td class="mt-activity">
                         <?=$user->activ_name;?>
                      </td>
                      <td class="mt-country">
                          <?=$user->country_name?>
                      </td>
                  </tr>
                  <?php 
                  $i++; 
                  endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="emptyResults hidden">
                    <td colspan="5"><?=Lang::get()->translate('Nothing found');?></td>
                </tr>
              </tfoot>    
          </table>
      </div>
      <div class="main-content-text">
          <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. </p>
          <p>In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. </p>
      </div>
  </section>
<?php 
include_once 'footer.php';
?>
