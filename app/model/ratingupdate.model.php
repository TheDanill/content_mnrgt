<?php

class RatingUpdateModel {

    /**
     * @var integer Идентификатор пользователя, для которого нужно обновить рейтинг
     */
    private $userID,
            /**
             * @var array Массив с необходимыми для обновления соц.сетями
             */
            $networks = [],
            /**
             * @var array Массив со значениями рейтинга из соц.сетей для подсчета глобального рейтинга
             */
            $values = [],
            /**
             * @var object Объект RatingModel
             */
            $rm;

    /**
     * Конструктор объекта
     * 
     * @param integer $userID Идентификатор пользователя
     */
    public function __construct($userID) {
        $this->rm = new RatingModel($userID);
        $this->userID = $userID;
        $this->ratios = $this->rm->getRatios();
    }

    /**
     * Получение значений подписчиков/друзей из соц.сетей
     * 
     * @return array $returnArray Массив со значениями рейтингов из соц.сетей
     */
    private function getValues() {
        $array = $this->rm->getNeededFields();
        $infoArray = []; //Результирующий массив с информацией о рейтингах;
        // Массив с текущими значениями (перед получением рейтингов из сетей) в RatingUpdateModel->getValues();
        $this->networks['facebook_form'] = $array[0]['facebookform_maxcol_name'];
        $this->networks['twitter_form'] = $array[0]['twitterform_maxcol_name'];
        
        switch ($this->networks['facebook_form']) {
            case 'facebook':
                // echo 'facebook';
                break;
            case 'vkontakte':
                $vk = new VkUpdateModel($this->userID);
                if ($vk->getCount()) {
                    $infoArray = array_merge($infoArray, $vk->count);
                }
                break;
            case 'odnoklassniki':
                $ok = new OdnoklassnikiUpdateModel($this->userID);
                if ($ok->getCount()) {
                    $infoArray['odnoklassniki_count'] = $ok->count;
                }
                break;
            case 'mailru':
                $mr = new MailruUpdateModel($this->userID);
                if ($mr->getCount()) {
                    $infoArray['mailru_count'] = $mr->count;
                }
                break;
            case 'linkedin':
                $li = new LinkedInUpdateModel($this->userID);
                if ($li->getCount()) {
                    $infoArray['linkedin_count'] = $li->count;
                }
                break;
            case 'qzone':
                $qz = new QzoneUpdateModel($this->userID);
                break;
            case 'renren':
                break;
        }

        switch ($this->networks['twitter_form']) {
            case 'twitter':
                $tw = new TwitterUpdateModel($this->userID);
                if ($tw->getCount()) {
                    $infoArray = array_merge($infoArray, $tw->count);
                }
                break;
            case 'weibo':
                $wb = new WeiboUpdateModel($this->userID);
                if ($wb->getCount()) {
                    $infoArray['weibo_count'] = $wb->count;
                }
                break;
        }

        if (isset($array[0]['instagram_count'])) {
            $in = new InstagramUpdateModel($this->userID);
            if ($in->getCount()) {
                $infoArray['instagram_count'] = $in->count;
            }
        }

        $kw = new KeyWordsUpdateModel($this->userID);
        $kw->getCount();
        $infoArray['keywords_count'] = $kw->count;
        // Массив с собранной информацией из соц. сетей в RatingUpdateModel->getValues();</br>';
        return $infoArray;
    }

    /**
     * Обновление рейтингов соц.сетей пользователя
     * 
     * @return boolean Результат запроса
     */
    public function updateNetworksRatings() {
        $array = $this->getValues();
        $set = '';
        if (is_array($array) && !empty($array)) {
            foreach ($array as $key => $value) {
                $set .= '`' . $key . '` = "' . $value . '", ';
            }
            $set = substr(trim($set), 0, -1);
            if ($set) {
                $sql = 'UPDATE `users` SET ' . $set . ' WHERE `id` = "' . $this->userID . '"';
                // Запрос на обновление информации о рейтинге в соц. сетях в RatingUpdateModel->updateNetworksRatings();
                if (DB::get()->query($sql)) {
                    return true;
                }
            }
        }
        return false;
    }

}
