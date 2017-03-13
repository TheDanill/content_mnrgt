<?php
if (!defined('_INITIALIZED')) {
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
    exit;
}
//error_reporting(E_PARSE);
include_once _BASEDIR . 'templates/header_admin.php';
?>
            <div class="panel panel-primary" data-page="<?= $this->data->page; ?>" data-type="<?= $this->data->type; ?>">
                <div class="panel-heading">
                    <?= $this->data->type == 'active' ? 'Пользователи' : ($this->data->type == 'banned' ? 'Забаненные пользователи' : ($this->data->type == 'trash' ? 'Корзина' : 'Пользователи')); ?>
                    <div class="btn-group btn-group-xs pull-right actions">
                        <button class="btn btn-default add"><span class="icon-plus"></span></button>
                        <button class="btn btn-default edit"><span class="icon-pencil"></span></button>
                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                        <ul class="dropdown-menu" role="menu">
                            <?php if ($this->data->type != 'active') { ?>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/users/">Активные пользователи</a></li>
                            <?php } if ($this->data->type != 'banned') { ?>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/users/?type=banned">Забаненные пользователи</a></li>
                            <?php } if ($this->data->type != 'trash') { ?>
                            <li><a href="http://<?= $GLOBALS['config']['domain']; ?>/users/?type=trash">Корзина</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <script type="text/plain" id="tmpl_users">
                    <tr class="default" data-action="edit">
                        <td data-action="choose"><input type="checkbox" id="checkuser_{{=it.id}}" name="users[{{=it.id}}][checked]" value="{{=it.id}}"><label for="checkuser_{{=it.id}}"></label></td>
                        <td data-content="{{=it.id}}" data-type="hidden" data-name="users[{{=it.id}}][id]">{{=it.id}}</td>
                        <td data-content="{{=it.login}}" data-disabled data-name="users[{{=it.login}}][login]">{{=it.login}}</td>
                        <td data-type="text" data-name="users[{{=it.id}}][pass]" class="hidden"></td>
                        <td data-content="{{=it.group_id}}" data-type="select" data-from="groups" data-name="users[{{=it.id}}][group_id]">{{=it.group}}</td>
                        <td data-content="{{=it.name}}" data-name="users[{{=it.id}}][name]">{{=it.name}}</td>
                        <td data-content="{{=it.permissions}}" data-type="select" data-from="permissions" data-name="users[{{=it.id}}][permissions]">{{? it.permissions==<?= UserModel::USER; ?>}}Пользователь{{?}}{{? it.permissions==<?= UserModel::EDITOR; ?>}}Модератор{{?}}{{? it.permissions==<?= UserModel::ADMIN; ?>}}Администратор{{?}}</td>
                        <td class="hidden-edit">{{=it.last_visit}}</td>
                        <td data-content="{{=it.status}}" data-type="select" data-from="statuses" data-name="users[{{=it.id}}][status]" class="hidden hidden-add">{{? it.status=='active'}}Активный{{?}}{{? it.status=='banned'}}Забанен{{?}}{{? it.status=='trash'}}В корзине{{?}}</td>
                        <td class="actions-hover hidden-edit text-right">
                            {{? it.status == 'banned' || it.status == 'trash'}}
                            <a class="btn btn-xs btn-success fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/{{=it.id}}/activate" data-remove="tr" data-container="body" title="Восстановить"><span class="icon-box-remove"></span></a>
                            {{?}}
                            <a class="btn btn-xs btn-success edit fade" href="#" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Редактировать"><span class="icon-pencil2"></span></a>
                            {{? it.status=='active'}}
                            <a class="btn btn-xs btn-warning fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/{{=it.id}}/ban" data-remove="tr" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Забанить"><span class="icon-blocked"></span></a>
                            {{?}}{{? it.status == 'active' || it.status == 'banned'}}
                            <a class="btn btn-xs btn-danger fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/{{=it.id}}/trash" data-remove="tr" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Удалить"><span class="icon-remove2"></span></a>
                            {{?}}{{? it.status == 'trash'}}
                            <a class="btn btn-xs btn-danger fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/{{=it.id}}/remove" data-remove="tr" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Удалить навсегда"><span class="icon-close"></span></a>
                            {{?}}
                        </td>
                    </tr>
                </script>
                <form>
                    <table class="table table-condensed table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="width-1"><input type="checkbox" id="checkall"><label for="checkall"></label></th>
                                <th>#</th>
                                <th>Имя</th>
                                <th>Фамилия</th>
                                <th>E-mail</th>
                                <th class="hidden">Пароль</th>
                                <th>ДР</th>
                                <th>Страна</th>
                                <th>Город</th>
                                <th>Пол</th>
                                <th>Активность</th>
                                <th>Информация</th>
                                <th>Рейтинг</th>
                                <th>Место</th>
                                <th>Зарегистрирован</th>
                                <th>Права</th>
                                <th class="hidden hidden-add">Статус</th>
                                <th class="width-1 hidden-edit"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($this->data->users)) {
                                foreach ($this->data->users as $user) {
                            ?>
                            <tr class="default" data-action="edit">
                                <td data-action="choose"><input type="checkbox" id="checkuser_<?= $user->id; ?>" name="users[<?= $user->id; ?>][checked]" value="<?= $user->id; ?>"><label for="checkuser_<?= $user->id; ?>"></label></td>
                                <td data-content="<?= $user->id; ?>" data-type="hidden" data-name="users[<?= $user->id; ?>][id]"><a href="http://<?= $GLOBALS['config']['domain']; ?>/popadmin/user/<?= $user->id; ?>/"><?= $user->id; ?></a></td>
                                <td data-content="<?= $user->name; ?>" data-name="users[<?= $user->id; ?>][name]"><a href="http://<?= $GLOBALS['config']['domain']; ?>/popadmin/user/<?= $user->id; ?>/"><?= $user->name; ?></a></td>
                                <td data-content="<?= $user->surname; ?>" data-name="users[<?= $user->id; ?>][surname]"><?= $user->surname; ?></td>
                                <td data-content="<?= $user->email; ?>" data-name="users[<?= $user->id; ?>][email]"><?= $user->email; ?></td>
                                <td data-type="text" data-name="users[<?= $user->id; ?>][password]" class="hidden"></td>
                                <td data-type="date" data-content="<?= $user->birthday; ?>" data-name="users[<?= $user->id; ?>][birthday]"><?= $user->birthday; ?></td>
                                <td data-content="<?= $user->country_id; ?>" data-type="select" data-from="countries" data-name="users[<?= $user->id; ?>][country_id]"><?= $this->data->countries[$user->country_id]->name; ?></td>
                                <td data-content="<?= $user->city_id; ?>" data-type="select" data-from="cities" data-name="users[<?= $user->id; ?>][city_id]"><?= $this->data->cities[$user->city_id]->name; ?></td>
                                <td data-content="<?= $user->gender; ?>" data-type="select" data-from="genders" data-name="users[<?= $user->id; ?>][gender]"><?= $user->gender; ?></td>
                                <td data-content="<?= $user->activity_id; ?>" data-type="select" data-from="activities" data-name="users[<?= $user->id; ?>][activity_id]"><?= $this->data->activities[$user->activity_id]->name; ?></td>
                                <td data-disabled data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="L: <?= $user->likes; ?>, D: <?= $user->dislikes; ?>, G: <?= $user->google_count; ?>, I: <?= $user->instagram_count; ?>, F: <?= $user->facebook_count; ?>, V: <?= $user->vkontakte_followers_count; ?>/<?= $user->vkontakte_friends_count; ?>, O: <?= $user->odnoklassniki_count; ?>, M: <?= $user->mailru_count; ?>, Li: <?= $user->linkedin_count; ?>, Q: <?= $user->qzone_count; ?>, R: <?= $user->renren_count; ?>, T: <?= $user->twitter_followers_count; ?>/<?= $user->twitter_friends_count; ?>, W: <?= $user->weibo_count; ?>, K: <?= $user->keywords_count; ?>">L: <?= $user->likes; ?>, D: <?= $user->dislikes; ?>, G: <?= $user->google_count; ?>, I: <?= $user->instagram_count; ?>, F: <?= $user->facebook_count; ?>, V: <?= $user->vkontakte_followers_count; ?>/<?= $user->vkontakte_friends_count; ?>, O: <?= $user->odnoklassniki_count; ?>, M: <?= $user->mailru_count; ?>, Li: <?= $user->linkedin_count; ?>, Q: <?= $user->qzone_count; ?>, R: <?= $user->renren_count; ?>, T: <?= $user->twitter_followers_count; ?>/<?= $user->twitter_friends_count; ?>, W: <?= $user->weibo_count; ?>, K: <?= $user->keywords_count; ?></td>
                                <td data-content="<?= $user->rating; ?>" data-type="number" data-name="users[<?= $user->id; ?>][rating]"><?= $user->rating; ?></td>
                                <td data-content="<?= $user->place; ?>" data-type="number" data-name="users[<?= $user->id; ?>][place]"><?= $user->place; ?> (<?= $user->last_place; ?>)</td>
                                <td data-content="<?= $user->datetime; ?>" data-disabled data-name="users[<?= $user->id; ?>][datetime]"><?= $user->datetime; ?></td>
                                <td data-content="<?= $user->permissions; ?>" data-type="select" data-from="permissions" data-name="users[<?= $user->id; ?>][permissions]"><?= ($user->permissions == UserModel::USER ? 'Пользователь' : ($user->permissions == UserModel::EDITOR ? 'Модератор' : ($user->permissions == UserModel::ADMIN ? 'Администратор' : 'Неизвестно'))); ?></td>
                                <td data-content="<?= $user->status; ?>" data-type="select" data-from="statuses" data-name="users[<?= $user->id; ?>][status]" class="hidden hidden-add"><?= ($user->status == 'active' ? 'Активный' : ($user->status == 'banned' ? 'Забанен' : ($user->status == 'trash' ? 'В корзине' : 'Неизвестно'))); ?></td>
                                <td class="actions-hover hidden-edit text-right">
                                    <a class="btn btn-xs btn-primary success edit fade" href="#" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Редактировать"><span class="icon-pencil2"></span></a>
                                    <a class="btn btn-xs btn-success fade" href="http://<?= $GLOBALS['config']['domain']; ?>/user/<?= $user->id; ?>/stats" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Cтатистика"><span class="icon-stats"></span></a>
                                    <a class="btn btn-xs btn-success fade" href="http://<?= $GLOBALS['config']['domain']; ?>/user/<?= $user->id; ?>/history" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="История"><span class="icon-drawer2"></span></a>
                                    <?php if ($user->status == 'active') { ?>
                                    <a class="btn btn-xs btn-warning fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/<?= $user->id; ?>/ban" data-remove="tr" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Забанить"><span class="icon-blocked"></span></a>
                                    <?php } elseif ($user->status == 'banned' || $user->status == 'trash') { ?>
                                    <a class="btn btn-xs btn-success fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/<?= $user->id; ?>/activate" data-remove="tr" data-container="body" title="Восстановить"><span class="icon-box-remove"></span></a>
                                    <?php } if ($user->status == 'active' || $user->status == 'banned') { ?>
                                    <a class="btn btn-xs btn-danger fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/<?= $user->id; ?>/trash" data-remove="tr" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Удалить"><span class="icon-remove2"></span></a>
                                    <?php } elseif ($user->status == 'trash') { ?>
                                    <a class="btn btn-xs btn-danger fade send-json" href="http://<?= $GLOBALS['config']['domain']; ?>/user/<?= $user->id; ?>/remove" data-remove="tr" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Удалить навсегда"><span class="icon-close"></span></a>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php
                                }
                            }
                            elseif ($this->data->error == 404) {
                            ?>
                            <tr class="default hidden-add">
                                <td colspan="9" class="text-center">Не найдено ни одного пользователя.</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="default new hidden form-group" data-action="add" data-name="users">
                                <td colspan="2"></td>
                                <td><input type="text" class="form-control input-xs" name="name" required></td>
                                <td><input type="text" class="form-control input-xs" name="surname" required></td>
                                <td><input type="text" class="form-control input-xs" name="email"></td>
                                <td><input type="password" class="form-control input-xs" name="password"></td>
                                <td><input type="date" class="form-control input-xs" name="birthday"></td>
                                <td>
                                    <select name="country_id" class="form-control input-xs countries">
                                        <?php
                                        foreach ($this->data->countries as $country) {
                                        ?>
                                        <option value="<?= $country->id; ?>"><?= $country->name; ?></option>
                                        <?php 
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="city_id" class="form-control input-xs cities">
                                        <?php
                                        foreach ($this->data->cities as $city) {
                                        ?>
                                        <option value="<?= $city->id; ?>"><?= $city->name; ?></option>
                                        <?php 
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="gender" class="form-control input-xs genders">
                                        <option value="M">M</option>
                                        <option value="W">W</option>
                                        <option value="A">A</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="activity_id" class="form-control input-xs activities">
                                        <?php
                                        foreach ($this->data->activities as $activity) {
                                        ?>
                                        <option value="<?= $activity->id; ?>"><?= $activity->name; ?></option>
                                        <?php 
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td colspan="4"></td>
                                <td>
                                    <select name="permissions" class="form-control input-xs permissions">
                                        <option value="<?= UserModel::USER; ?>">Пользователь</option>
                                        <?php if (UserModel::get()->info->permissions >= UserModel::EDITOR) { ?><option value="<?= UserModel::EDITOR; ?>">Модератор</option><?php } ?>
                                        <?php if (UserModel::get()->info->permissions >= UserModel::ADMIN) { ?><option value="<?= UserModel::ADMIN; ?>">Администратор</option><?php } ?>
                                    </select>
                                </td>
                                <td>
                                    <div class="hidden">
                                        <select name="users[][status]" class="form-control input-xs statuses">
                                            <option value="active">Активный</option>
                                            <option value="banned">Бан</option>
                                            <option value="trash">В корзине</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="actions-hover text-right">
                                    <button class="btn btn-xs btn-danger row-remove fade" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Удалить строку"><span class="icon-close"></span></button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
                <div class="panel-footer clearfix">
                    <div class="col-lg-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">С отмеченными:</span>
                            <div class="input-group-btn">
                                <?php if ($this->data->type != 'active') { ?>
                                <button class="btn btn-xs btn-success choose disabled" data-action="activate" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Восстановить"><span class="icon-box-remove"></span></button>
                                <?php } if ($this->data->type == 'active') { ?>
                                <button class="btn btn-xs btn-warning choose disabled" data-action="ban" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Забанить"><span class="icon-blocked"></span></button>
                                <?php } if ($this->data->type != 'trash') { ?>
                                <button class="btn btn-xs btn-danger choose disabled" data-action="trash" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="В корзину"><span class="icon-remove2"></span></button>
                                <?php } if ($this->data->type == 'trash') { ?>
                                <button class="btn btn-xs btn-danger choose disabled" data-action="remove" data-toggle="tooltip" data-placement="bottom" data-delay="300" data-container="body" title="Удалить навсегда"><span class="icon-close"></span></button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center">
                        <ul class="pagination pagination-sm">
                            <?= Pagination::gen($this->data->found, $this->data->page, $this->data->per_page, 5, "http://" . $GLOBALS['config']['domain'] . "/popadmin/users/page/", "/", "active", "disabled"); ?>
                        </ul>
                    </div>
                    <div class="col-lg-3 text-right">
                        <button id="cancel" class="btn btn-sm btn-default hidden">Отмена</button>
                        <div class="alert alert-warning alert-dismissable hidden nomargin-bottom">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            Проверьте правильность введенных данных.
                        </div>
                        <div class="alert alert-success alert-dismissable hidden nomargin-bottom">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            Изменения успешно сохранены. Изменено пользователей: <span class="content"></span>.
                        </div>
                        <div class="alert alert-danger alert-dismissable hidden nomargin-bottom">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            Произошла ошибка.
                        </div>
                        <button id="edit" class="btn btn-sm btn-primary hidden">Применить</button>
                        <div class="alert alert-warning alert-dismissable hidden nomargin-bottom">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            Проверьте правильность введенных данных.
                        </div>
                        <div class="alert alert-success alert-dismissable hidden nomargin-bottom">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            Пользователей добавлено: <span class="content"></span>.
                        </div>
                        <div class="alert alert-danger alert-dismissable hidden nomargin-bottom">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            Произошла ошибка. Возможно, Вы пытаетесь добавить пользователя с существующим логином.
                        </div>
                        <button id="add" class="btn btn-sm btn-primary hidden">Добавить</button>
                    </div>
                </div>
            </div>
<?php
include_once _BASEDIR . 'templates/footer_admin.php';
?>