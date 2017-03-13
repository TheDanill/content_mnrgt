<?php
 include_once 'header.php';
 include_once 'standart-aside-button.php';?>
<section class="main-content">
    <div class="user-info-block-wrapper">
        <div class="user-info-block">
            <div class="user-info-photo-block">
                <div class="photo">
                    <img src="http://<?= $GLOBALS['config']['domain']?>/files/avatars/unknown.png">
                </div>
                <div class="user-account-info">
                    <div class="left socnetworks">
                        <a><i class="icon-mini icon-fb-mini"></i></a>
                        <a><i class="icon-mini icon-undef-mini"></i></a>
                        <a><i class="icon-mini icon-insta-mini"></i></a>
                    </div>
                    <div class="right stats-like">
                        <div class="likes-block stats-in-block">
                            <i class="icon icon-like"></i>
                            <span class="count-likes">3 233</span>    
                        </div>
                        <div class="dislikes-block stats-in-block">
                            <i class="icon icon-dislike"></i>
                            <span class="count-dislikes">
                               3
                            </span>
                        </div>
                    </div>    
                </div>
                <div class="fill-edit-block">
                    <button class="standard-button btn edit-btn"><?=  Lang::get()->translate('Edit');?></button>
                </div>    
            </div>
            <div class="user-info-text-block">
                <div class="user-info-name-block">
                    <div class="name">
                        <span>Justin Bieber</span>
                    </div>
                    <div class="place">
                        <span>6 <?=  Lang::get()->translate('Place');?></span>
                    </div>    
                </div>  
                <div class="user-about-block">
                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque 
penatibus et magnis dis parturient montes, nascetur ridiculus mus. 
Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. 
Nulla consequat massa quis enim. Donec pede justo, fringilla vel, 
aliquet nec, vulputate eget, arcu. </p>
                </div>    
            </div>    
        </div>
        <div class="user-info-comments-block">
            <div class="comment-block">
                <div class="comment-block-inner">
                <div class="avatar">
                    <img src="http://<?=$GLOBALS['config']['domain']?>/files/avatars/unknown.png">
                </div>
                <div class="comment-body">
                    <div class="comment-info">
                        <div class="author-name">
                            <span>Selena Gomez</span>
                        </div>
                        <div class="datetime">
                            <span>8 апреля в 4:49</span>
                        </div>   
                    </div>
                    <div class="comment-text">
                        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. </p>
                    </div>    
                </div>
                </div>
                <div class="comment-actions">
                    <div class="likes">
                        <a id="comment-like"><i class="icon icon-like"></i></a>
                        <a id="comment-dislike"><i class="icon icon-dislike"></i></a>
                    </div>
                    <div class="reply">
                        <a><?=  Lang::get()->translate('Reply')?></a>
                    </div>
                    <div class="all-replies">
                        <a><?=Lang::get()->translate('All replies')?></a>
                    </div>
                </div>    
            </div>  
            <div class="add-comment-block">
                <div class="avatar">
                    <img src="http://<?=$GLOBALS['config']['domain']?>/files/avatars/unknown.png">
                </div>
                <div class="comment-field">
                    <input id="addComment" type="text" placeholder="<?= Lang::get()->translate('Your comment')?>">
                    <div class="add-comment-act">
                        <span><i class="icon icon-photo"></i></span>
                        <span><i class="icon icon-smile"></i></span>
                    </div>    
                </div>    
            </div>
        </div>   
    </div>  
    <div class="main-content-text">
          <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. </p>
          <p>In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. </p>
      </div>
</section>
<?php include_once 'footer.php'; ?>
