<?php

/**
 * Контроллер главной страницы
 */
class IndexControl extends PageControl {

    /**
     * @var array Доступные действия
     */
    protected   $actions = [
                    'index',
                    'about',
                    'privacy',
                    'feedback',
                    'help'
                ];
    
    protected $fields = [
        'index' => [
            'search'        => ['text',     false,  255],
            'activity_id'   => ['number',   false,  0],
            'country'       => ['number',   false,  0],
            'city'          => ['number',   false,  0],
            'age'           => ['number',   false,  0,      8],
            'gender'        => ['oneOf',    false,  [
                                                        '0',
                                                        'M',
                                                        'W'
                                                    ]
                               ]
        ]
    ];

    public function __construct() {
        parent::__construct();
        $this->addSocLinks();

        // title
        $this->view->title = Lang::get()->translate('Ranking of the most popular people in the world');
        // description
        $this->view->description = 'Рейтинг популярности высчитывается по числу подписчиков в соцсетях и по запросам в поисковике. Самые популярные люди выходят на первые места. Есть общий рейтинг и отдельные категории: спорт, актеры, певцы, блогеры и т.д.';
        // keywords
        $this->view->keywords = 'Рейтинг звёзд, рейтинг популярности, популярные люди, топ звёзд.';

        $activities = new ActivityModel();
        $countries = new CountryModel();
        $users = new UsersModel('rating_desc');
        $this->view->data->activities = $activities->getActivitys();
        $this->view->data->countries = $countries->getCountries();
        
        // Проверка авторизация
        $this->view->data->authorized = AuthModel::get()->isAuthorized();
        // Информация для пользователя
        $this->view->data->whois = AuthModel::get()->whoIsAuthorized();
        
        if ($this->action == "index") {
            if ($this->checkVars(isset($_POST['search']) ? $_POST : $_GET)) {
                if (count($this->vars) > 0) {
                    $this->view->data->users = $users->getUsersByRating($this->vars);
                    if (isset($this->vars['country'])) {
                        $city = new CityModel();
                        $this->view->data->cities = $city->getCities($this->vars['country']);
                    }
                } else {
                    $this->view->data->users = $users->getUsersByRating();
                }
            }
            $this->view->page('index');
        } else {
            switch ($this->action) {
                case 'about':
                    $this->view->page('about');
                    break;
                case 'privacy':
                    $this->view->page('privacy');
                    break;
                case 'feedback':
                    $this->view->page('feedback');
                    break;
                case 'help':
                    $this->view->page('help');
                    break;
            }
        }
    }
}