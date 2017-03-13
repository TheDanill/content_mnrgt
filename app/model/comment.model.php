<?php

class CommentModel {
    /**
     * @var int $id Идентификатор комментария
     */
    public  $id,
            /**
             * @var string $text Текст комментария
             */
            $text,
            /**
             * @var int $author Идентификатор автора комментария
             */
            $author,
            /**
             * @var int $user_id Идентификатор пользователя, кому был написан комментарий
             */
            $user_id,
            /**
             * @var int $parent Идентификатор "родителя" комментария 
             */
            $parent = 0,
            /**
             * @var string $like_or_dislike Тип (Лайк или дизлайк)
             */
            $like_or_dislike;
    
    public function __construct(){}
    
    /**
     * "Магический" метод. Присваивает, либо отдает значения свойств объекта
     * 
     * @return mixed Текущий объект, либо значение свойства
     */
    public function __call($name, $arguments){
        $args = preg_split('/(?<=\w)(?=[A-Z])/', $name);
        $action = array_shift($args);
        $property_name = strtolower(implode('_', $args));
        switch ($action) {
            case 'set':
                $this->$property_name = $arguments[0];
                return $this;
            break;
            case 'get':
                return isset($this->$property_name) ? $this->$property_name : null;
            break;
        }
    }


    /**
     * Добавление нового комментария
     * 
     * @return object Текущий объект
     */
    public function addComment() {
        if (!empty($this->author) && !empty($this->user_id) && !empty($this->text)) {
            if (DB::get()->query('INSERT INTO `comments` (`parent`, `author`, `user_id`, `text`) VALUES (' . DB::get()->escape($this->parent, true) . ', ' . DB::get()->escape($this->author, true) . ', ' .  DB::get()->escape($this->user_id, true) . ', ' . DB::get()->escape( $this->text, true) . ')'))
                return $this;   
        }
        return false;
    }
    
    /**
     * Обновление комментария
     * 
     * return object Текущий объект
     */
    public function updateComment() {
        if(!empty($this->id) && !empty($this->text)){
            if(DB::get()->query('UPDATE `comments` SET `text` = "'.$this->text.'" WHERE `id` = "'.$this->id.'"'))
                return $this;   
        }
        return false;
    }
    
    /**
     * Удаление комментария
     * 
     * @return boolean Результат выполнения операции
     */
    public function removeComment(){
        if(!empty($this->id)){
            if(DB::get()->query('DELETE FROM `comments` WHERE `id` = "'.$this->id.'"'))
                return true;   
        }
        return false;
    }
    
    /**
     * Получение комментариев по id или id пользователя
     * 
     * @return mixed Возвращает объект комментария либо false
     */
    public function getComment(){
        if($this->id != 0 || $this->user_id != 0){
            $where = '';
            if(!empty($this->id))
                $where = '`id` = "'.$this->id.'"';
            if(!empty($this->user_id))
                $where = '`user_id` = "'.$this->user_id.'"';
            $sql = 'SELECT * FROM `comments` WHERE '.$where;
            if($comm = DB::get()->select($sql)){
                return $comm;   
            }
        }
        return false;
    }
    
    /**
     * Добавление лайка или дизлайка к комментарию
     * 
     * @return boolean Результат выполнения операции
     */
    public function addLikeDislike() {
        if(!empty($this->id) && !empty($this->user_id) && !empty($this->like_or_dislike)){
            if(DB::get()->query('INSERT INTO `comments_likes_dislikes` (`comment_id`, `user_id`, `like_or_dislike`) VALUES ("'.$this->id.'", "'.$this->user_id.'", "'.$this->like_or_dislike.'")'))
                return true;   
        }
        return false;
    }
    
    /**
     * Удаление лайка или дизлайка
     * 
     * @return boolean Результат выполнения операции
     */
    public function removeLikeDislike() {
        if(!empty($this->id) && !empty($this->user_id)){
            if(DB::get()->query('DELETE FROM `comments_likes_dislikes` WHERE `comment_id` = "'.$this->id.'" AND `user_id` = "'.$this->user_id.'"'))  
                return true;
        }
        return false;
    }
    
    /**
     * Получение лайков - дизлайков к комментарию
     * @return array Массив лайков - дизлайков
     */   
    public function getLikesDislikes(){
        if($likes = DB::get()->select('SELECT * FROM `comments_likes_dislikes` WHERE `comment_id` = "'.$this->id.'" ', DB::ASSOC)){
            return $likes;
        }
        return false;
    }       

    /**
     * Проверка текста через словари
     */
    private function validateText($text){
        
    }
}
