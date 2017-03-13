<?php
/**
 * Модель комментариев
 */
class CommentsModel{
    /**
     *
     * @var integer Идентификатор пользователя 
     */
    protected $user_id,
            /**
             * @var array Массив с комментариями к странице пользователя
             */
              $resultComments;
    
    public function __construct() {}
    
    /**
     * Получение комментариев по идентификатору пользователей
     * @param integer $user_id Идентификатор пользователя
     * @return mixed Массив комментариев, либо false
     */
    public function getComments($user_id){
        if($array = DB::get()->select('SELECT     `comments`.*, 
                                                  IFNULL(SUM(`comments_likes_dislikes`.`like_or_dislike` = "like"), "0") AS `likes_sum`, 
                                                  IFNULL(SUM(`comments_likes_dislikes`.`like_or_dislike` = "dislike"), "0") AS `dislikes_sum`,
                                                  COUNT(DISTINCT(`comments_2`.`author`)) AS `users_count`,
                                                  ((IFNULL(SUM(`comments_likes_dislikes`.`like_or_dislike` = "like"), "0") - IFNULL(SUM(`comments_likes_dislikes`.`like_or_dislike` = "dislike"), "0")) + COUNT(DISTINCT(`comments_2`.`author`)) *2) AS `sort`,
                                                  `users`.`name`,
                                                  `users`.`surname`,
                                                  `users`.`name_en`,
                                                  `users`.`surname_en`,
                                                  `users`.`nickname`,
                                                  `users`.`avatar`
                                          FROM `comments`
                                          LEFT JOIN `comments_likes_dislikes` ON `comments_likes_dislikes`.`comment_id` = `comments`.`id`
                                          LEFT JOIN `comments` AS `comments_2` ON `comments_2`.`parent` = `comments`.`id`
                                          LEFT JOIN `users` ON (`users`.`id` = `comments`.`author`)
                                          WHERE `comments`.`user_id` = "'.$user_id.'"
                                          GROUP BY `comments`.`id` 
                                          ORDER BY `sort` DESC'
                                          ,DB::ASSOC, false, 0)) {
            foreach ($array as $key => $value) {
                if ( Lang::get()->lang === 'ru_RU' ) {
                    $value['name'] = $value['name'] . ' ' . (!empty($value['nickname']) ? $value['nickname'] . ' ' : '') . $value['surname'];
                } else {
                    $value['name'] = $value['name_en'] . ' ' . (!empty($value['nickname']) ? $value['nickname'] . ' ' : '') . $value['surname_en'];
                }
                
                $this->resultComments[$value['id']] = $value;
            }
            return $this->buildTree();
        }
        return false;
    }
    
    /**
     * Формирование дерева комментариев
     * @return array Массив комментариев
     */
    private function buildTree() {
	foreach($this->resultComments as $key=>$value) {
            $this->resultComments[$value['parent']]['child'][$key]=&$this->resultComments[$key];
        }
        return $this->resultComments[0]['child'];
    }
   
}

