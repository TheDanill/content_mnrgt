<?php
/**
 * Модель деятельности пользователей
 * 
 */
class ActivityModel {
    
    public function getByID($id) {
        return DB::get()->select('SELECT * FROM `activity` WHERE `id` = ' . DB::get()->escape($id), DB::OBJECT, true);
    }

    /**
     * Получение всех видов деятельности из БД
     * @return object Объекты видов деятельности
     */
    public function getActivitys() {
        return DB::get()->select('SELECT * FROM `activity`', DB::OBJECT, false);
    }
    
    public function getActivitiesArrayID() {
        $activities = $this->getActivitys();
        $tmpActivities = [];
        if ($activities && count($activities)) {
            foreach ($activities as $activity) {
                $tmpActivities[intval($activity->id)] = $activity;
            }
        }
        return $tmpActivities;
    }
}

