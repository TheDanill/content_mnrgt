<?php

/**
 * Модель рейтинга пользователей
 */
class RatingModel {
    
    static function recalcAllUsers() {
        try {
            $users = new UsersModel();
            $u = $users->getUsersByRating([], 'active', 1000, 0);
            if (is_array($u) && !empty($u)) {
                foreach ($u as $us) {
                    $r = new RatingModel($us->id);
                    $rating = $r->calculateRating();
                    DB::get()->query('UPDATE `users` SET `rating` = "' . $rating . '" WHERE `id` = ' . DB::get()->escape( $us->id, true ) . ' LIMIT 1');
                    if (DB::get()->affected() !== 1) {
                        throw new Exception('[UPDATE RATING] Незапдейтились данные из функции recalcAllUsers.');
                    }
                }
            } else {
                throw new Exception('[UPDATE RATING] Пустые данные из функции getUsersByRating в функции recalcAllUsers.');
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }
    
    static function updateAllUsers() {
        try {
            set_time_limit(0);
            $users = new UsersModel();
            $u = $users->getUsersByRating([], 'active', 1000, 0);
            if (is_array($u) && !empty($u)) {
                foreach ($u as $us) {
                    $r = new RatingModel($us->id);
                    $r->updateRating();
                    echo $us->id . "<br>";
                    flush();
                    sleep(0.5);
                }
            } else {
                throw new Exception('[UPDATE RATING] Пустые данные из функции getUsersByRating в функции updateAllUsers.');
            } 
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }

    /**
     *@var array Коэффициенты для расчета рейтинга
     */
    private $ratios = [],
            /**
             * @var int Идентификатор пользователя
             */
            $userID;
    
    /**
     * Конструктор объекта
     * 
     * @param int $userID Идентификатор пользователя
     */
    public function __construct($userID) {
        $this->ratios = $this->getRatios();
        $this->userID = $userID;
    }

    /**
     * Расчет рейтинга пользователя исходя из значений соц.сетей
     * 
     * @return int $rating Значение рейтинга 
     */
   public function calculateRating() {
        $values = $this->getNeededFields();
        $values = $values[0];
        // Массив с текущими значениями (перед расчетом итогового рейтинга) в RatingModel->calculateRating()
        $rating = round($values['keywords_count'] * $this->ratios['Kg'] + $values['instagram_count'] / $this->ratios['Ki'] + $values['facebookform_count'] / $this->ratios['Kf'] + $values['twitterform_count'] / $this->ratios['Kt'] + max([0, $values['likes'] * $this->ratios['Kl'] - $values['dislikes'] * $this->ratios['Kd']]));
        return $rating;
    }
    
    /**
     * Получение рейтинга для конкретного пользователя
     * 
     * @param int $userID Идентификатор пользователя
     * 
     * @return mixed Результат запроса
     */
    public function getRating( $userID ) {
        return DB::get()->select('SELECT `rating` FROM `users` WHERE `id` = ' . $userID);
    }
    
    /**
     * Обновление рейтинга пользователя
     * 
     * @return boolean true - успешное обновление, false - неудача
     */
    public function updateRating() {
        try {
            $rateUpdModel = new RatingUpdateModel( $this->userID );
            if ($rateUpdModel->updateNetworksRatings()) {
                $rating = $this->calculateRating();
                DB::get()->query('UPDATE `users` SET `rating` = "' . $rating . '" WHERE `id` = "' . $this->userID . '" LIMIT 1');
                if (DB::get()->affected() !== 1) {
                    throw new Exception('[UPDATE RATING] Незапдейтились данные из функции updateRating.');
                }
            } else {
                throw new Exception('[UPDATE RATING] Пришли пустые данные из функции updateNetworksRatings в функции updateRating.');
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }
    
    /**
     * Получение массива с коэффициентами для соц.сетей
     * 
     * @return array $returnArray Массив коэффициентов
     */
    public function getRatios(){
        try {
            $arrays = DB::get()->select('SELECT `name`, `value` FROM `ratios`', DB::ASSOC, false, 0);
            $returnArray = [];
            if (is_array($arrays) && !empty($arrays)) {
                foreach ($arrays as $array){
                    $returnArray[$array['name']] = $array['value'];
                }
                return $returnArray;
            } else {
                throw new Exception('[UPDATE RATING] Запрос в функции getRatios вернул пустые данные.');
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
   /**
    * Получение необходимых полей соц.сетей из БД (заполненных пользователем), а также их значений.
    * 
    * @return mixed Результат запроса
    */
    public function getNeededFields() {
        return DB::get()->select('SELECT GREATEST(twitter_followers_count, weibo_count) as twitterform_count,
                                           CASE GREATEST(twitter_followers_count, weibo_count)
                                               WHEN twitter_followers_count THEN "twitter"
                                               WHEN weibo_count THEN "weibo" 
                                           END AS twitterform_maxcol_name,
                                           instagram_count,
                                           GREATEST(facebook_count, vkontakte_followers_count + vkontakte_friends_count, odnoklassniki_count, linkedin_count, qzone_count, renren_count) as facebookform_count,
                                           CASE GREATEST(facebook_count, vkontakte_followers_count, odnoklassniki_count, mailru_count, linkedin_count, qzone_count, renren_count, google_count)
                                               WHEN facebook_count THEN "facebook"
                                               WHEN vkontakte_followers_count THEN "vkontakte"
                                               WHEN odnoklassniki_count THEN "odnoklassniki"
                                               WHEN mailru_count THEN "mailru"
                                               WHEN linkedin_count THEN "linkedin"
                                               WHEN qzone_count THEN "qzone"
                                               WHEN renren_count THEN "renren"
                                               WHEN google_count THEN "google"
                                           END AS facebookform_maxcol_name,
                                           IF(`status` = "verified", keywords_count, 0) AS keywords_count,
                                           likes,
                                           dislikes
                                    FROM `users`       
                                    WHERE  `id` = ' . $this->userID, DB::ASSOC, false, 0);
    }
    
    public function recalcOneUser() {
        try {
            // Значения для пересчета
            $values = $this->getNeededFields();
            if (isset($values[0]) && !empty($values[0])) {
                $values = $values[0];

                // Массив с текущими значениями (перед расчетом итогового рейтинга) в RatingModel->calculateRating()
                $rating = round($values['keywords_count'] * $this->ratios['Kg'] + $values['instagram_count'] / $this->ratios['Ki'] + $values['facebookform_count'] / $this->ratios['Kf'] + $values['twitterform_count'] / $this->ratios['Kt'] + max([0, $values['likes'] * $this->ratios['Kl'] - $values['dislikes'] * $this->ratios['Kd']]));
                // Обновляем рейтинг пользователя
                if (DB::get()->query('UPDATE `users` SET `rating` = "' . $rating . '" WHERE `id` = "' . $this->userID . '" LIMIT 1')) {
                    return true;
                } else {
                    return false;
                }
            } else {
                throw new Exception('[UPDATE RATING] Пришли пустые данные из функции getNeededFields в функции recalcOneUser');
            }
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }
}
