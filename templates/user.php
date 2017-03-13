<?php include_once 'header_user.php';
      include_once 'aside.php';
      //echo '<pre>';
      //print_r($this->data->userInfo);
      //echo '</pre>';?>
<section class="main-content">
    <div class="user-info-block-wrapper">
        <?php if(empty($this->error)): ?>
        <div class="user-info-block">
            <div class="user-info-photo-block">
                <div class="photo">
                    <img src="http://<?= $GLOBALS['config']['domain']?>/files/avatars/<?=isset($this->data->userInfo->avatar)?$this->data->userInfo->avatar:'unknown.png'?>">
                </div>
                <div class="user-account-info">
                    <div class="left socnetworks">
                        <?php if (!empty($this->data->userInfo->google_profile_url)) { ?>
                            <a href="<?=$this->data->userInfo->google_profile_url;?>" target="_blank"><i class="icon-mini icon-google"></i></a>
                        <?php } ?>
                            
                        <?php if (!empty($this->data->userInfo->vkontakte_profile_url)) { ?>
                            <a href="<?=$this->data->userInfo->vkontakte_profile_url;?>" target="_blank"><i class="icon-mini icon-vkontakte"></i></a>
                        <?php } ?>
                            
                        <?php if (!empty($this->data->userInfo->facebook_profile_url)) { ?>
                            <a href="<?=$this->data->userInfo->facebook_profile_url;?>" target="_blank"><i class="icon-mini icon-facebook"></i></a>
                        <?php } ?>
                            
                        <?php if (!empty($this->data->userInfo->instagram_profile_url)) { ?>
                            <a href="<?=$this->data->userInfo->instagram_profile_url;?>" target="_blank"><i class="icon-mini icon-instagram"></i></a>
                        <?php } ?>
                            
                        <?php if (!empty($this->data->userInfo->odnoklassniki_profile_url)) { ?>
                            <a href="<?=$this->data->userInfo->odnoklassniki_profile_url;?>" target="_blank"><i class="icon-mini icon-ok"></i></a>
                        <?php } ?>
                            
                        <?php if (!empty($this->data->userInfo->mailru_profile_url)) { ?>
                            <a href="<?=$this->data->userInfo->mailru_profile_url;?>" target="_blank"><i class="icon-mini icon-mailru"></i></a>
                        <?php } ?>
                            
                        <?php if (!empty($this->data->userInfo->twitter_profile_url)) { ?>
                            <a href="<?=$this->data->userInfo->twitter_profile_url;?>" target="_blank"><i class="icon-mini icon-twitter"></i></a>
                        <?php } ?> 
                            
                    </div>
                    
                    <?php // if(AuthModel::get()->isAuthorized()) : ?>
                    <div data-user-id="<?=(isset($this->data->userInfo->id)?$this->data->userInfo->id:'');?>" class="right stats-like">
                        <div class="likes-block stats-in-block">
                            <?php if ( (!isset(UserModel::get()->info->id)) || (UserModel::get()->info->id != $this->data->userInfo->id) ): ?>
                               <?php if(isset($this->data->userInfo->likeType)): ?>
                                   <i id="user-like" class="icon <?= ($this->data->userInfo->likeType == 1)?'active':'';?> icon-like"></i>
                                <?php else: ?>
                                   <i id="user-like" class="icon icon-like"></i>
                               <?php endif; ?>
                             <?php endif; ?>      
                            <span class="count-likes"><?=($this->data->userInfo->likes)?$this->data->userInfo->likes:'0'?></span>    
                        </div>
                        <div class="dislikes-block stats-in-block">
                            <?php if ( ( !isset(UserModel::get()->info->id)) || (UserModel::get()->info->id != $this->data->userInfo->id) ): ?>
                              <?php if ( isset($this->data->userInfo->likeType)): ?>
                                <i id="user-dislike" class="icon <?= ($this->data->userInfo->likeType == 0)?'active':'';?> icon-dislike"></i>
                                <?php else: ?>
                                <i id="user-dislike" class="icon icon-dislike"></i>
                                <?php endif; ?>
                            <?php endif; ?>
                            <span class="count-dislikes">
                               <?=($this->data->userInfo->dislikes)?$this->data->userInfo->dislikes:'0'?>
                            </span>
                        </div>
                    </div>    
                    <?php // endif; ?>
                </div>
                <?php if (!empty($this->data->userInfo->percentage) && (isset(UserModel::get()->info->id) && UserModel::get()->info->id == $this->data->userInfo->id)) { ?>
                    <div class="profile-fill">
                        <a class="percentage" <?php if ($this->data->authorized) { ?>href="/me/edit" <?php } ?> style="width: <?= $this->data->userInfo->percentage ?>%">
                            <span><?= $this->data->userInfo->percentage ?>%</span>
                        </a>
                    </div>
                <?php } ?>
                <div class="fill-edit-block">
                    <?php if (AuthModel::get()->isAuthorized()) : ?>
                         <?php if (isset(UserModel::get()->info['id']) && UserModel::get()->info['id'] == $this->data->userInfo->id): ?>
                                <a href="http://<?= $GLOBALS['config']['domain']; ?>/me/edit" class="standard-button btn edit-btn"><?=  Lang::get()->translate('Edit');?></a>                     
                         <?php endif; ?>
                    <?php endif; ?>
                </div>    
            </div>
            <div class="user-info-text-block">
                <div class="user-info-name-block clearfix">
                    <div class="name">
                        <span><?=$this->data->userInfo->name;?></span>
                        <?php if ($this->data->userInfo->checked == 1) { ?><i class="icon-checkmark color-blue has-title " data-title="<?= __('Approved page'); ?>"></i><?php } ?>
                    </div>
                    <div class="place">
                        <?php if (!empty($this->data->userInfo->rating)) : ?>
                        <span class="rating"><?= Lang::get()->translate('Rating') . ": " . $this->data->userInfo->rating; ?></span>
                        <?php endif; ?>
                        <?php if(!empty($this->data->userInfo->place)):?>
                        <span><?= Lang::get()->translate('Place') . ": " . $this->data->userInfo->place;?></span>
                        <?php endif; ?>
                    </div>
                </div>  
                <div class="user-about-block">
                    <?php if (!empty($this->data->userInfo->birthday) && ($this->data->userInfo->birthday !== '0000-00-00')) { ?>
                        <p><?= __('Birthday'); ?>: <?= addslashes($this->data->userInfo->birthday); ?></p>
                    <?php } ?>
                     
                    <?php if (!empty($this->data->userInfo->country_id)) {
                            foreach ($this->data->countries as $country):
                                if ($country->id == $this->data->userInfo->country_id) { ?>
                                    <p><?= __('Country'); ?>: <?= __($country->name); ?></p>
                    <?php       }
                            endforeach; 
                        } ?>
                    <?php if (!empty($this->data->userInfo->city_id)) {
                            foreach ($this->data->cities as $city) {
                                if ($city->id == $this->data->userInfo->city_id) { ?>
                                    <p><?= __('City'); ?>: <?= __($city->name); ?></p>
                    <?php       }
                            } 
                        }
                    ?>
                        
                    <?php if (!empty($this->data->userInfo->wikipedia_description) && ($this->data->userInfo->checked == 1)) { ?>
                        <p><?= __('Wikipedia') ?>: <?php echo implode(' ', array_slice(explode(' ', $this->data->userInfo->wikipedia_description), 0, 100)) . "..."; ?></p>
                    <?php } else if (!empty($this->data->userInfo->description)) { ?>
                        <p><?= __('Description') ?>: <?= nl2br($this->data->userInfo->description); ?></p>
                    <?php } ?>
                </div>    
            </div>    
        </div>
        <div class="user-info-comments-block">
            <?php
            if ( $this->data->comments ) {
                foreach ($this->data->comments as $comment) { ?>
                    <div class="comment-block">
                        <div class="comment-block-inner">
                            <div class="avatar">
                                <a href="http://<?= $GLOBALS['config']['domain']; ?>/id<?= $comment['author']; ?>"><img src="http://<?=$GLOBALS['config']['domain']?>/files/avatars/40x40/<?= isset($comment['avatar']) && !empty($comment['avatar']) ? $comment['avatar'] : 'unknown.png'; ?>"></a>
                            </div>
                            <div class="comment-body">
                                <div class="comment-info">
                                    <div class="author-name">
                                        <span><a href="http://<?= $GLOBALS['config']['domain']; ?>/id<?= $comment['author']; ?>"><?= $comment['name']; ?></a></span>
                                    </div>
                                    <div class="datetime">
                                        <span><?= Filter::toDateFormat($comment['datetime'], 'd.m.Y H:i:s'); ?></span>
                                    </div>   
                                </div>
                                <div class="comment-text">
                                    <p><?= $comment['text']; ?></p>
                                </div>    
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            ?>
            <div class="comment-block hidden" data-href="http://<?= $GLOBALS['config']['domain']; ?>/id">
                <div class="comment-block-inner">
                    <div class="avatar" data-src="http://<?=$GLOBALS['config']['domain']?>/files/avatars/40x40/"></div>
                    <div class="comment-body">
                        <div class="comment-info">
                            <div class="author-name">
                                <span></span>
                            </div>
                            <div class="datetime">
                                <span></span>
                            </div>   
                        </div>
                        <div class="comment-text">
                            <p></p>
                        </div>    
                    </div>
                </div>
            </div>
            <div class="add-comment-block">
            <?php if (AuthModel::get()->isAuthorized()) { ?>
                <div class="avatar">
                    <?php
                        $whois = UserModel::get()->getInfoById(UserModel::get()->info['id']);
                    ?>
                    <img src="http://<?=$GLOBALS['config']['domain']?>/files/avatars/40x40/<?=isset($whois->avatar)?$whois->avatar:'unknown.png'?>">
                </div>
                <div class="comment-field">
                    <form action="http://<?= $GLOBALS['config']['domain']; ?>/comment/add" id="userCommentForm" method="post">
                        <textarea id="addComment" name="text" data-emojiable="true" maxlength="1000" placeholder="<?= Lang::get()->translate('Your comment')?>"></textarea>
                        <input type="hidden" name="user_id" value="<?= $this->data->userInfo->id; ?>">
                        <button type="submit" class="btn standard-button right"><?= __('Send'); ?></button>
                        <!--<div class="add-comment-act">
                            <span><i class="icon icon-photo"></i></span>
                            <span><i class="icon icon-smile"></i></span>
                        </div>-->
                    </form>
                </div> 
            <?php } else { ?>
                <div class="text-center"><?= Lang::get()->translate('Please sign in to add your comment.'); ?></div>
            <?php } ?>
            </div>
        </div>
    <?php else: ?>
        Error
    <?php endif; ?>   
    </div>
    <div class="main-content-text">
        <h1 class="text">Рейтинг популярности</h1>&nbsp;<p>высчитывается по количеству подписчиков пользователя в соцсетях и числу запросов в поисковике. Так формируется топ звезд. По средствам несложных расчетов самые популярные люди выходят на первые места рейтинга. Сам рейтинг звезд делится на отдельные категории: спорт, актеры, певцы, блогеры и т.д. Чем больше подписчиков набирает аккаунт в соцсетях, тем выше место в рейтинге. Таким образом, поднять рейтинг популярности можно через социальную сеть, добавляя друзей и набирая подписчиков.</p>
    </div>  
</section>
<?php include_once 'footer.php'; ?>
