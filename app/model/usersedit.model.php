<?php
class UsersEditModel extends AdminModel {
    
    protected   $order = "`users`.`id` ASC",
                $orders = array(
                    'id'                => '`users`.`id` ASC',
                    'id_desc'           => '`users`.`id` DESC',
                    'login'             => '`users`.`login` ASC',
                    'login_desc'        => '`users`.`login` DESC',
                    'group_id'          => '`users`.`group_id` ASC',
                    'group_id_desc'     => '`users`.`group_id` DESC',
                    'group'             => '`groups`.`name` ASC',
                    'group_desc'        => '`groups`.`name` DESC',
                    'name'              => '`users`.`name` ASC',
                    'name_desc'         => '`users`.`name` DESC',
                    'permissions'       => '`users`.`permissions` ASC',
                    'permissions_desc'  => '`users`.`permissions` DESC',
                    'last_visit'        => '`users`.`last_visit` ASC',
                    'last_visit_desc'   => '`users`.`last_visit` DESC'
                );
    
    public  $users;

    public function __construct($page, $per_page, $order = "id") {
        parent::__construct($page, $per_page, $order);
    }

    public function getUsers($status = 'active') {
        $where = "(`users`.`status` = '{$status}')";
        $result = DB::get()->select("
            SELECT
                    `users`.`id`,
                    `users`.`name`,
                    `users`.`surname`,
                    `users`.`birthday`,
                    `users`.`country_id`,
                    `users`.`city_id`,
                    `users`.`gender`,
                    `users`.`email`,
                    DATE_FORMAT(`users`.`datetime`, '%d.%m.%Y %H:%i:%s') AS `datetime`,
                    IFNULL(`users`.`likes`, 0) AS `likes`,
                    IFNULL(`users`.`dislikes`, 0) AS `dislikes`,
                    IFNULL(`users`.`google_count`, 0) AS `google_count`,
                    `users`.`google_profile_url`,
                    IFNULL(`users`.`instagram_count`, 0) AS `instagram_count`,
                    `users`.`instagram_profile_url`,
                    IFNULL(`users`.`facebook_count`, 0) AS `facebook_count`,
                    `users`.`facebook_profile_url`,
                    IFNULL(`users`.`vkontakte_followers_count`, 0) AS `vkontakte_followers_count`,
                    IFNULL(`users`.`vkontakte_friends_count`, 0) AS `vkontakte_friends_count`,
                    `users`.`vkontakte_profile_url`,
                    IFNULL(`users`.`odnoklassniki_count`, 0) AS `odnoklassniki_count`,
                    `users`.`odnoklassniki_profile_url`,
                    IFNULL(`users`.`mailru_count`, 0) AS `mailru_count`,
                    `users`.`mailru_profile_url`,
                    IFNULL(`users`.`linkedin_count`, 0) AS `linkedin_count`,
                    `users`.`linkedin_profile_url`,
                    IFNULL(`users`.`qzone_count`, 0) AS `qzone_count`,
                    `users`.`qzone_profile_url`,
                    IFNULL(`users`.`renren_count`, 0) AS `renren_count`,
                    `users`.`renren_profile_url`,
                    IFNULL(`users`.`twitter_followers_count`, 0) AS `twitter_followers_count`,
                    IFNULL(`users`.`twitter_friends_count`, 0) AS `twitter_friends_count`,
                    `users`.`twitter_profile_url`,
                    IFNULL(`users`.`weibo_count`, 0) AS `weibo_count`,
                    `users`.`weibo_profile_url`,
                    IFNULL(`users`.`keywords_count`, 0) AS `keywords_count`,
                    IFNULL(`users`.`rating`, 0) AS `rating`,
                    IFNULL(`users`.`place`, 0) AS `place`,
                    IFNULL(`users`.`last_place`, 0) AS `last_place`,
                    `users`.`permissions`,
                    `users`.`activity_id`,
                    `users`.`status`
            FROM `users`
            WHERE   {$where}
            ORDER BY {$this->order}
            LIMIT {$this->offset}, {$this->limit}
        ");
        if ($result) {
            $this->users = $result;
            $this->found = DB::get()->select("SELECT COUNT(*) FROM `users` WHERE {$where}", DB::COLLECT, true, 3600)[0];
            return true;
        }
        else
            return false;
    }
    
    public function getStats($status = 'active') {
        $where = $status == 'active' ? "(`users`.`status` = 'active' AND `groups`.`status` = 'active')" : "(`users`.`status` = '{$status}'
                    OR `groups`.`status` = '{$status}')";
        $result = DB::get()->select("
            SELECT  STRAIGHT_JOIN SQL_CALC_FOUND_ROWS
                    `users`.`id`,
                    `users`.`name`,
                    `groups`.`name` AS `group_name`,
                    IFNULL(SUM(`exercises`.`points` * IF(`history`.`action` = 'true', 1, 0)), 0) AS `points`,
                    SUM(IF(`history`.`action` = 'true', 1, 0)) AS `trues`,
                    COUNT(DISTINCT `exercises`.`id`) AS `count`,
                    IFNULL(SUM(`exercises`.`level` * IF(`history`.`action` = 'true', 1, 0)), 0) AS `level`
            FROM `groups`
            STRAIGHT_JOIN `users` ON (`users`.`group_id` = `groups`.`id`)
            LEFT JOIN `history` ON (`history`.`user_id` = `users`.`id` AND (`history`.`action` IN ('true', 'false') OR `history`.`action` IS NULL))
            LEFT JOIN `exercises` ON (`exercises`.`id` = `history`.`ex_id`)
            WHERE   `groups`.`owner_id` = " . UserModel::get()->info->id . "
                    AND {$where}
            GROUP BY `users`.`id`
            ORDER BY {$this->order}
            LIMIT {$this->offset}, {$this->limit}
        ");
        if ($result) {
            $this->found = DB::get()->found_rows;
            return $result;
        }
        else {
            return false;
        }
    }
    
    public function add($users, $now = true) {
        $added = 0;
        foreach ($users as $user) {
            $add = UserModel::get()->add($user, false);
            if ($add) {
                $added++;
            }
        }
        if ($now) {
            if ($added == count($users)) {
                return $added;
            }
            else {
                return $added + DB::get()->insertValues('users');
            }
        }
    }

    public function update($users, $now = true) {
        foreach ($users as $user) {
            UserModel::get()->update($user, false);
        }
        if ($now)
            return DB::get()->multiQuery([], true, true);
    }
    
    public function activate($ids, $now = true) {
        $q = "UPDATE `users` SET `status` = 'active' WHERE `id` IN (" . implode(",", $ids) . ")";
        if ($now)
            return DB::get()->query($q);
        else
            DB::get()->addQuery($q);
    }
    
    public function ban($ids, $now = true) {
        $q = "UPDATE `users` SET `status` = 'banned' WHERE `id` IN (" . implode(",", $ids) . ")";
        if ($now)
            return DB::get()->query($q);
        else
            DB::get()->addQuery($q);
    }
    
    public function trash($ids, $now = true) {
        $q = "UPDATE `users` SET `status` = 'trash' WHERE `id` IN (" . implode(",", $ids) . ")";
        if ($now)
            return DB::get()->query($q);
        else
            DB::get()->addQuery($q);
    }
    
    public function remove($ids, $now = true) {
        $q = "DELETE FROM `users` WHERE `id` IN (" . implode(",", $ids) . ")";
        if ($now)
            return DB::get()->query($q);
        else
            DB::get()->addQuery($q);
    }
    
}