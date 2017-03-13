<?php
include_once 'header.php';
include_once 'standart-aside.php';
?>
<section class="main-content">
    <div class="mainTablePeople-wrapper">  
        <table class="people-list" id="mainTablePeople">
            <thead class="main-header-block">
                <tr>
                    <th class="mt-position"><?= Lang::get()->translate('Place'); ?></th>
                    <th class="mt-name"><?= Lang::get()->translate('People (stars)'); ?></th>
                    <th class="mt-rating"><?= Lang::get()->translate('Rating'); ?></th>
                    <th class="mt-activity"><?= Lang::get()->translate('Field of activity'); ?></th>
                    <th class="mt-country"><?= Lang::get()->translate('Country'); ?></th>
                </tr>
                <tr class="hidden for-copy js-link" data-link="">
                    <td class="mt-position"></td>
                    <td class="mt-name">
                        <img src="http://mypop.top/files/avatars/40x40/unknown.png" data-src="http://<?= $GLOBALS['config']['domain']; ?>/files/avatars/" alt="New user">     
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
                $has_users = 0;
                if (isset($this->data->users) && !empty($this->data->users)):
                    $i = 1;
                    $has_users = 1;
                    foreach ($this->data->users as $user):
                        ?>
                        <tr class="js-link" data-link="/id<?= $user->id; ?>">
                            <td class="mt-position">
                                <?= $i; ?>
                            </td>
                            <td class="mt-name">
                                <a href="http://<?= $GLOBALS['config']['domain'] ?>/id<?= $user->id; ?>">
                                    <img src="http://<?= $GLOBALS['config']['domain'] ?>/files/avatars/40x40/<?= (isset($user->avatar)) ? $user->avatar : 'unknown.png' ?>" alt="<?= htmlspecialchars($user->name); ?>">     
                                    <span><?= $user->name; ?></span>
                                </a>
                            </td>
                            <td class="mt-rating">
                                <span><?= $user->rating; ?></span>
                                <i class="rating-up"></i>
                            </td>
                            <td class="mt-activity">
                                <?= $user->activ_name; ?>
                            </td>
                            <td class="mt-country">
                                <?= $user->country_name ?>
                            </td>
                        </tr>
                        <?php
                        $i++;
                    endforeach;
                endif;
                ?>
            </tbody>
            <tfoot>
                <tr class="emptyResults <?= ($has_users) ? 'hidden' : '' ?>">
                    <td colspan="5"><?= Lang::get()->translate('Nothing found'); ?></td>
                </tr>
            </tfoot>    
        </table>
    </div>
    <div class="main-content-text">
        <h1 class="text">Рейтинг популярности</h1>&nbsp;<p>высчитывается по количеству подписчиков пользователя в соцсетях и числу запросов в поисковике. Так формируется топ звезд. По средствам несложных расчетов самые популярные люди выходят на первые места рейтинга. Сам рейтинг звезд делится на отдельные категории: спорт, актеры, певцы, блогеры и т.д. Чем больше подписчиков набирает аккаунт в соцсетях, тем выше место в рейтинге. Таким образом, поднять рейтинг популярности можно через социальную сеть, добавляя друзей и набирая подписчиков.</p>
    </div>
</section>
<?php include_once 'footer.php'; ?>
