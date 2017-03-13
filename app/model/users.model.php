<?php
/**
 * Модель пользователей
 */
class UsersModel {
    
    /**
     *
     * @var string Сортировка пользователей 
     */
    protected $order = '`users`.`id` ASC';
    
    /**
     *
     * @var array Виды сортировки
     */
    protected $orders = [
        'id' => '`users`.`id` ASC',
        'rating' => '`users`.`rating` ASC',
        'rating_desc' => '`users`.`rating` DESC'
        ];
    
    /**
     *
     * @var array Массив дат для поиска людей в интервале возраста
     */
    protected $dates = [];

    /**
     *
     * @var array Массив объектов пользователей
     */   
    public $users;
    
    /**
     * Конструктор объекта. Инициализация массива дат
     * @param string $order 
     */
    public function __construct($order = 'id') {
        $this->dates = [
            1 => "BETWEEN '".date('Y-m-d', strtotime('-20 year', time()))."' AND '".date('Y-m-d')."'",
            2 => "BETWEEN '".date('Y-m-d', strtotime('-25 year', time()))."' AND '".date('Y-m-d', strtotime(date('Y-m-d').'-20 year'))."'",
            3 => "BETWEEN '".date('Y-m-d', strtotime('-30 year', time()))."' AND '".date('Y-m-d', strtotime(date('Y-m-d').'-25 year'))."'", 
            4 => "BETWEEN '".date('Y-m-d', strtotime('-35 year', time()))."' AND '".date('Y-m-d', strtotime(date('Y-m-d').'-30 year'))."'", 
            5 => "BETWEEN '".date('Y-m-d', strtotime('-40 year', time()))."' AND '".date('Y-m-d', strtotime(date('Y-m-d').'-35 year'))."'", 
            6 => "BETWEEN '".date('Y-m-d', strtotime('-45 year', time()))."' AND '".date('Y-m-d', strtotime(date('Y-m-d').'-40 year'))."'", 
            7 => "BETWEEN '".date('Y-m-d', strtotime('-50 year', time()))."' AND '".date('Y-m-d', strtotime(date('Y-m-d').'-45 year'))."'", 
            8 => "< '". date('Y-m-d', strtotime('-50 year', time()))."' AND `users`.`birthday` != '0000-00-00'"];
        $this->order = $this->orders[$order];
    }
    
    /**
     * Получение пользователей с сортировкой по рейтингу, с учетом условий.
     * @param array $conds Массив с условиями поиска
     * @param string $status Статус пользователя
     * @param integer $limit Количество выводимых пользователей
     * @param type $offset На какое число сместить вывод 
     * @return array $this->users Массив пользователей
     */
    public function getUsersByRating($conds = [], $status = 'active', $limit = 20, $offset = 0) {
        $where = "1";
        if (!empty($conds['search'])) {
            $search = explode(' ', $conds['search']);
            foreach ($search as $value) {
                if(!empty($value)) 
                    $where .= " AND MATCH(`users`.`name`, `users`.`surname`, `users`.`name_en`, `users`.`surname_en`, `users`.`nickname`) AGAINST ('" . DB::get()->escape($value) . "*' IN BOOLEAN MODE) ";
            }
        }
        if (!empty($conds['activity_id'])) 
            $where .= " AND `users`.`activity_id` = " . DB::get()->escape($conds['activity_id'], true);
        if (!empty($conds['country'])) {
            if (isset($conds['city']) && $conds['city'] != 0) {
                //$cities = DB::get()->query("SELECT `citys`.`id` FROM `lang_ru_RU` LEFT JOIN `citys` ON (`citys`.`name` = `lang_ru_RU`.`en_US` OR `citys`.`name` = `lang_ru_RU`.`ru_RU`) LEFT JOIN `citys` `cities2` ON (`cities2`.`id` = 15) WHERE `lang_ru_RU`.`en_US` = `cities2`.`name` OR `lang_ru_RU`.`ru_RU` = `cities2`.`name`");
                $where .= " AND (`users`.`city_id` = " . DB::get()->escape($conds['city'], true) . " OR `users`.`city_id` IN (SELECT `citys`.`id` FROM `lang_ru_RU` LEFT JOIN `citys` ON (`citys`.`name` = `lang_ru_RU`.`en_US` OR `citys`.`name` = `lang_ru_RU`.`ru_RU`) LEFT JOIN `citys` `cities2` ON (`cities2`.`id` = " . DB::get()->escape($conds['city'], true) . ") WHERE `lang_ru_RU`.`en_US` = `cities2`.`name` OR `lang_ru_RU`.`ru_RU` = `cities2`.`name`))";
            }
            else {
                $where .= " AND `users`.`country_id` = " . DB::get()->escape($conds['country'], true);
            }
        }
        if (isset($conds['age']) && $conds['age'] != 0 && key_exists($conds['age'], $this->dates)) {
            $where .= " AND `users`.`birthday` " . $this->dates[$conds['age']];
        }
        if (!empty($conds['gender'])) {
                $where .= " AND `users`.`gender` = " . DB::get()->escape($conds['gender'], true);   
        }
        $where .= " AND `users`.`status` = '" . DB::get()->escape($status) . "'";
        
        $result = DB::get()->select("SELECT `users`.`id`,
                                            `users`.`name`, 
                                            `users`.`surname`, 
                                            `users`.`name_en`,
                                            `users`.`surname_en`,
                                            `users`.`nickname`,
                                            `users`.`avatar`,
                                            `users`.`rating`,
                                            `users`.`permissions` AS `verified`,
                                            `activity`.`name` AS activ_name,
                                            `countries`.`country_name_en` AS country_name
                                    FROM `users`      
                                    FORCE INDEX (`FK_users_countries`, `FK_users_citys`, `FK_users_activity`, `rating`, `birthday`, `gender`, `name`)
                                    LEFT JOIN `activity` ON `users`.`activity_id` = `activity`.`id`
                                    LEFT JOIN `countries` ON `users`.`country_id` = `countries`.`id`
                                    WHERE {$where}
                                    ORDER BY `users`.`rating` DESC
                                    LIMIT {$offset}, {$limit}", DB::OBJECT, false, 600);
        $this->users = $result;
        $this->handleNames();
        // Перевод названия страны и рода деятельности
        $this->translate();
        return $this->users;
    }
    
    /**
     * Обработка имени и никнейма пользователей в зависимости от наличия соответствующих полей
     * @return array Массив пользователей
     */
    private function handleNames() {
        // Переменная, обозначающая, что язык не английский
        $lang = false;
        
        if (isset($this->users) && !empty($this->users)) {
            if (Lang::get()->lang === 'en_US') {
                $lang = true;
            }
            
            foreach ($this->users as $id => $user) {
                if ($lang && !empty($user->name_en) && !empty($user->surname_en)) {
                    if (!empty($user->nickname) && ($user->verified == 2)) {
                        $user->name = $user->name_en . ' ' . $user->nickname . ' ' . $user->surname_en;
                    } else {
                        $user->name = $user->name_en . ' ' . $user->surname_en;
                    }
                } else if (!empty($user->name) && (!empty($user->surname))) {
                    if (!empty($user->nickname) && ($user->verified == 2)) {
                        $user->name = $user->name . ' ' . $user->nickname . ' ' . $user->surname;
                    } else {
                        $user->name = $user->name . ' ' . $user->surname;
                    }
                }
            }
            return $this->users;
        } else {
            // exception
            return false;
        }
    }
    
    /**
     * Перевод необходимых свойств пользователей
     * @return array Массив пользователей
     */
    private function translate() {
        foreach ($this->users as $key => $value) {
            $this->users[$key]->activ_name = Lang::get()->translate($value->activ_name);
            $this->users[$key]->country_name = Lang::get()->translate($value->country_name);
        }
        return $this->users;
    }

    /**
     * Добавление n-ого количества пользователей с рандомными свойствами для тестирования
     */
    public function addALotOfUsers() {
        set_time_limit(0);
        $citys = [
           1 => [1,2,3,4], 2 => [5,6,7,8], 3 => [9,10,11,12] 
        ];
        $gender = ['M', 'W', 'A'];
        for($i = 0;$i<500000;$i++) {
            $country = rand(1,3);
            $numberG = rand(0, 2);
            $numberC = rand(0, 3);
            $numberA = rand(1, 4);
            DB::get()->insert('users',[
               'name'           =>      'Name_'.$i,
               'surname'        =>      'Surname_'.$i,
               'surname_en'     =>      'Surname_en_'.$i,
               'name_en'        =>      'Name_en_'.$i,
               'nickname'       =>      'Nickname_'.$i,
               'birthday'       =>      date("Y-m-d", mt_rand(-934364084,1464013516)),
               'country_id'     =>      $country,
               'city_id'        =>      $citys[$country][$numberC], 
               'gender'         =>      $gender[$numberG],
               'rating'         =>      mt_rand(0, 200000),
               'activity_id'    =>      $numberA,
               'status'         =>      'active'
            ]);
        }
    }
}
