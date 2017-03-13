<?php

/**
 * Контроллер пользователей
 */
class UserControl extends PageControl {

    /**
     * @var array Массив допустимых действий
     */

    protected   $actions = ['favorite', 'settings'];
    
    protected   $fields = [
        'settings' => [
            'secure_key'    => ['md5',      true],
            'avatar'        => ['image',    false,  200,    200,    5000,   7000],
            'name'          => ['text',     true],
            'surname'       => ['text',     true],
            'nickname'      => ['text',     false],
            'name_en'       => ['text',     false],
            'surname_en'    => ['text',     false],
            'birthday'      => ['date',     false,  '-130 years',   '-1 day',   'Y-m-d'],
            'activity_id'   => ['activity', false],
            'country_id'    => ['country',  false],
            'city_id'       => ['city',     false,  'country_id'],
            'default_lang'  => ['language', false],
            'gender'        => ['oneOf',    false,  [
                                                        'M',
                                                        'W',
                                                        'A'
                                                    ]
                               ],
            'description'   => ['text',     false],
            'wikipedia'     => ['text',     false]
        ]
    ];


    /**
     * Конструктор. Определяет и запускает необходимые действия
     */
    public function __construct() {
        parent::__construct();
        $this->addSocLinks();
        
        $activities = new ActivityModel();
        $countries = new CountryModel();
        $likes = new UserLikesModel();
        

        $this->view->data->activities = $activities->getActivitys();
        $this->view->data->countries = $countries->getCountries();

        // Проверка авторизация
        $this->view->data->authorized = AuthModel::get()->isAuthorized();
        // Информация для пользователя
        $this->view->data->whois = AuthModel::get()->whoIsAuthorized();
        
        if ($this->action == '') {
            $user = new UserModel();
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                if (is_numeric($_GET['id'])) {
                    // Заранее устанавливаем Owner ID, для того, чтобы показывать незалогиненным пользователям лайки
                    $likes->setOwnerUserId($_GET['id']);
                    if (is_array(UserModel::get()->info) && isset(UserModel::get()->info['id'])) {
                        $likes->setMakeUserId(UserModel::get()->info['id']);
                        $type = $likes->getCurrentPageLike();
                    } else {
                        $type = null;
                    }

                    $this->view->data->userInfo = $user->handlingUserInfo($user->getInfoById(intval($_GET['id'])));
                    if ($this->view->data->userInfo) {
                        if (!empty($type)) {
                            $this->view->data->userInfo->likeType = $type[0]['type'];
                        }
                        $this->view->data->comments = (new CommentsModel())->getComments(intval($_GET['id']));
                    } else {
                        $this->view->data = array();
                        View::get()->error(404);
                        die();
                    }
                }
                else {
                    $this->view->error(404);
                    die();
                }
            } elseif (is_array(UserModel::get()->info) && isset(UserModel::get()->info['id'])) {
                $this->view->data->userInfo = $user->handlingUserInfo($user->getInfoById(UserModel::get()->info['id'])); // Если в url нет id, то получаем информацию о текущем залогиненном пользователе
                $this->view->data->comments = (new CommentsModel())->getComments(UserModel::get()->info['id']);
            }
            if (!isset($this->view->data->userInfo) || empty($this->view->data->userInfo)) {
                $this->view->error(400);
            }
            
            $this->view->data->userInfo->likes = $likes->getUserLikes();
            $this->view->data->userInfo->dislikes = $likes->getUserDislikes();
            // Список городов
            if ($this->view->data->userInfo->country_id) {
                $city = new CityModel();
                $this->view->data->cities = $city->getCities($this->view->data->userInfo->country_id);
            }
            
            // Meta title
            if (Lang::get()->lang === 'ru_RU') {
                if (!empty($this->view->data->userInfo->name) && !empty($this->view->data->userInfo->surname)) {
                    $this->view->title = $this->view->data->userInfo->name . ' - ' . Lang::get()->translate('Ranking of the most popular people in the world');
                } else {
                    $this->view->title = Lang::get()->translate('Ranking of the most popular people in the world');
                }
            } else {
                if (!empty($this->view->data->userInfo->name_en) && !empty($this->view->data->userInfo->surname_en)) {
                    $this->view->title = $this->view->data->userInfo->name_en . ' ' . $this->view->data->userInfo->surname_en . ' - ' . Lang::get()->translate('Ranking of the most popular people in the world');;
                } else {
                    $this->view->title = Lang::get()->translate('Ranking of the most popular people in the world');
                }
            }
            
            // Meta description
            if (!empty($this->view->data->userInfo->wikipedia_description)) {
                $this->view->description = substr( $this->view->data->userInfo->wikipedia_description, 0, 157 );
            } else if (!empty($this->view->data->userInfo->description)) {
                $this->view->description = substr( $this->view->data->userInfo->description, 0, 157 );
            } else {
                $this->view->description = '';
            }
                
            // Meta keywords
            $this->view->keywords = '';
            
            $this->view->page('user');
            
        } else {
            switch ($this->action) {
                case 'favorite':
                    $this->view->page('favorite');
                    break;
                case 'settings':
                    if (AuthModel::get()->isAuthorized()) {
                        if (isset($_POST['secure_key'])) {
                            if ($this->checkVars()) {
                                // Проверяем, что пользователь имеет статус Approved
                                if ($this->view->data->whois['permissions'] == 2) {
                                    $wikipedia = new WikipediaModel();
                                    $description = $wikipedia->getDescriptionByLink($this->vars['wikipedia']);
                                    if ($description != false) {
                                        $this->vars['wikipedia_description'] = Filter::sanitizeHTML( strip_tags( $description ) );
                                    }
                                } else {
                                    $this->vars['wikipedia_description'] = '';
                                    $this->vars['nickname'] = '';
                                }
                                if ($this->vars['secure_key'] == md5($GLOBALS['config']['password_salt'] . UserModel::get()->info['id'] . $GLOBALS['config']['domain'])) {
                                    if (UserModel::get()->edit($this->vars, true)) {
                                        $this->view->redirect("/me");
                                    }
                                } else {
                                    $this->view->error(403);
                                }
                            } else {
                                $this->showProfilePage(400);
                            }
                        } else {
                            $this->showProfilePage();
                        }
                    } else {
                        $this->view->error(403);
                    }
                    break;
            }
        }
    }
    
    private function showProfilePage($error = null) {
        
        $this->view->data->secure_key = md5($GLOBALS['config']['password_salt'] . UserModel::get()->info['id'] . $GLOBALS['config']['domain']);
        $this->view->data->languages = Lang::get()->getAllLangs();
        $user = new UserModel();
        $this->view->data->userInfo = $user->getInfoById(UserModel::get()->info['id']); // Если в url нет id, то получаем информацию о текущем залогиненном пользователе
        
        if (!isset($this->view->data->userInfo) || empty($this->view->data->userInfo)) {
            $this->view->error(400);
        } else {
            
            if ($this->view->data->userInfo->country_id) {
                $city = new CityModel();
                $this->view->data->cities = $city->getCities($this->view->data->userInfo->country_id);
            }
            
            if ($error === null) {
                $this->view->page('profile');
            } else {
                $this->view->error($error, 'profile');
            }
            
        }
        
    }

}
