<?php

class MainControl {
    
    /**
     * @var array Массив соответствий действий и контроллеров
     */
    private $actions = [
                'index'         => 'IndexControl',
                'user'          => 'UserControl',
                'auth'          => 'AuthControl',
                'vkauth'        => 'VkAuthControl',
                'fbauth'        => 'FbAuthControl',
                'instaauth'     => 'InstaAuthControl',
                'googleauth'    => 'GoogleAuthControl',
                'twitterauth'   => 'TwitterAuthControl',
                'okauth'        => 'OdnoklAuthControl',
                'mailauth'      => 'MailAuthControl',
                'linkedauth'    => 'LinkedInAuthControl',
                'weiboauth'     => 'WeiboAuthControl',
                'qzoneauth'     => 'QzoneAuthControl',
                'rating'        => 'RatingControl',
                'comment'       => 'CommentControl',
                'comments'      => 'CommentsControl',
                'vocabulars'    => 'VocabularyControl',
                'static'        => 'StaticControl',
                'search'        => 'SearchControl', 
                'word'          => 'WordControl',
                'staticedit'    => 'StaticEditControl',
                'stat'          => 'StatControl',
                'security'      => 'SecurityControl',
                'users'         => 'UsersControl',
                'user'          => 'UserControl',
                'city'          => 'CityControl',
                'lang'          => 'LangControl',
                'userlikes'     => 'UserLikesControl',
                'usersedit'     => 'UsersEditControl'
            ],
            /**
             * @var string Выбранное действие
             */
            $action;
    
    /**
     * Конструктор контроллера. Выполняет все задачи, возложенные на главный контроллер.
     * 
     * @uses ShieldModel Модель защиты
     * @uses View Вид
     */
    public function __construct() {
        $isAct = $this->getAction();
        if (ShieldModel::get($this->action)->userCanSee()) {
            if ($isAct) {
                $this->doAction();
            }
            else {
                View::get()->error(404);
            }
        }
        elseif (ShieldModel::get()->threat_level >= ShieldModel::HACKER) {
            View::get()->redirect(ShieldModel::get()->redirect_url);
        }
        else {
            View::get()->error(ShieldModel::get()->http_error_code);
        }
    }
    
    /**
     * Получение запрошенного действия
     * 
     * @uses actions Массив соответствий
     * @return boolean Флаг, было ли получено запрошенное действие и имеется ли оно в массиве соответствий
     */
    private function getAction() {
	if (isset($_GET['action'])){
	    if (isset($this->actions[trim($_GET['action'])]) && $this->action = trim($_GET['action'])) {
                return true;
            }
            else {
                return false;
            }
	} else {
	    return false;
        }
    }
    
    /**
     * Выполнение действия
     */
    private function doAction() {
        new $this->actions[$this->action]();
        ShieldModel::get()->saveNavigation();
    }
}