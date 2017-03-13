<?php

/**
 * Модель лайков пользователей
 */
class UserLikesModel {

    /**
     * 
     * @var integer Идентификатор пользователя кому поставлен лайк
     */
    protected $ownerUserId,
            /**
             * @var integer Идентификатор пользователя кто поставил лайк
             */
            $makeUserId,
            /**
             * @var integer Тип оценки 0 - дизлайк, 1 - лайк 
             */
            $type,
            /**
             * @var array Массив соответствий 
             */
            $types = [0 => 'dislikes', 1 => 'likes'];

    /**
     * Конструктор объекта 
     * @param integer $ownerUserId Идентификатор пользователя кому поставлен лайк
     * @param integer $makeUserId Идентификатор пользователя кто поставил лайк
     * @param integer $type Тип оценки
     */
    public function __construct($ownerUserId = 0, $makeUserId = 0, $type = null) {
        if ($makeUserId != 0) {
            $this->makeUserId = $makeUserId;
        }
        if ($ownerUserId != 0) {
            $this->ownerUserId = $ownerUserId;
        }
        if ($type != null) {
            $this->type = $type;
        }
    }

    /**
     * Установка типа 
     * @param integer $type Тип оценки
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Установка идентификатора пользователя кому поставлен лайк
     * @param integer $id
     */
    public function setOwnerUserId($id) {
        if (!empty($id)) {
            $this->ownerUserId = $id;
        }
    }

    /**
     * Установка идентификатора пользователя, кто поставил лайк
     * @param integer $ids
     */
    public function setMakeUserId($id) {
        if (!empty($id)) {
            $this->makeUserId = $id;
        }
    }

    /**
     * Проверка: поставил ли пользователь лайк на странице
     */
    public function getCurrentPageLike() {
        if ($this->makeUserId !== null) {
            return DB::get()->select('SELECT `type` FROM `users_likes_dislikes` WHERE `owner_user_id` = ' . $this->ownerUserId . ' AND `make_user_id` = ' . $this->makeUserId, DB::ASSOC);
        }
    }

    /**
     * "Поставить" лайк 
     * @return integer|boolean Количество лайков/дизлайков на странице | false - в случае ошибки добавления в БД
     */
    public function addLike() {
        // Забираем дату последнего лайка с таким пользователем такому человеку с таким типом
        $resultDateTime = DB::get()->select('SELECT `datetime` FROM `users_likes_dislikes` WHERE `owner_user_id` = ' . DB::get()->escape($this->ownerUserId, true) . ' AND `make_user_id` = ' . DB::get()->escape($this->makeUserId, true) . ' AND `type` = ' . DB::get()->escape($this->type, true) . ' LIMIT 1', DB::ASSOC);
        if (isset($resultDateTime[0]['datetime'])) {
            $lastLikeDateTime = new DateTime($resultDateTime[0]['datetime']);
            $nowDateTime = new DateTime();
            // Проверяем прошли ли сутки с последнего лайка.
            if ((Filter::toInt($nowDateTime->format('U')) - Filter::toInt($lastLikeDateTime->format('U'))) > 86400) {
                // Добавляем лайк если прошли
                $result = DB::get()->insert('users_likes_dislikes', ['owner_user_id' => $this->ownerUserId, 'make_user_id' => $this->makeUserId, 'type' => $this->type]);
                if ($result) {
                    Cache::redis()->incr($this->types[$this->type] . '_' . $this->ownerUserId);
                    return Cache::redis()->get($this->types[$this->type] . '_' . $this->ownerUserId);
                }
            }
            return false;
        } else {
            // Добавляем первый лайк
            $result = DB::get()->insert('users_likes_dislikes', ['owner_user_id' => $this->ownerUserId, 'make_user_id' => $this->makeUserId, 'type' => $this->type]);
            if ($result) {
                Cache::redis()->incr($this->types[$this->type] . '_' . $this->ownerUserId);
                return Cache::redis()->get($this->types[$this->type] . '_' . $this->ownerUserId);
            }
        }
    }

    /**
     * Получить количество лайков пользователя
     * @return integer 
     */
    public function getUserLikes() {
        return Cache::redis()->get($this->types[1] . '_' . $this->ownerUserId);
    }

    /**
     * Получить количество дизлайков
     * @return integer 
     */
    public function getUserDislikes() {
        return Cache::redis()->get($this->types[0] . '_' . $this->ownerUserId);
    }

    /**
     * Удалить лайк или дизлайк
     * @return integer|boolean Количество лайков/дизлайков | false - если произошла ошибка при удалении из БД
     */
    public function removeLike() {

        $result = DB::get()->query('DELETE FROM `users_likes_dislikes` WHERE `owner_user_id` = ' . $this->ownerUserId . ' AND `make_user_id` = ' . $this->makeUserId . ' LIMIT 1');
        if (DB::get()->affected() === 1) {
            Cache::redis()->decr($this->types[$this->type] . '_' . $this->ownerUserId);
            return Cache::redis()->get($this->types[$this->type] . '_' . $this->ownerUserId);
        }
        return false;
    }

}
