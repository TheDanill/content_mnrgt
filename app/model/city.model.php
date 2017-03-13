<?php

/**
 * Модель городов
 */
class CityModel {
    
    /**
     * @var array Массив с объектами городов
     */
    protected $cities;
    
    
    /**
     * Получение списка городов. Либо всех, либо по определенной стране
     * 
     * @param int Идентификатор страны
     * 
     * @return array Объекты городов
     */
    public function getCities($countryId = 0) {
        $query = 'SELECT * FROM `citys` ';
        if($countryId !=0)
            $query .= 'WHERE `citys`.`country_id` = '.$countryId;
        $query .= ' ORDER BY `citys`.`name` ASC';
      
        $this->cities = DB::get()->select($query, DB::OBJECT, false, 600);
        $this->translateNames();
        return $this->cities;
    }
    
    public function getCitiesArrayID() {
        $cities = $this->getCities();
        $tmpCities = [];
        if ($cities && count($cities)) {
            foreach ($cities as $city) {
                $tmpCities[intval($city->id)] = $city;
            }
        }
        return $tmpCities;
    }
    
    public function issetCity($id) {
        return DB::get()->select("SELECT * FROM `citys` WHERE `id` = " . DB::get()->escape($id), DB::OBJECT, true);
    }
    
    /**
     * Перевод названия городов в текущий язык
     * 
     * @return array Объекты городов
     */
    private function translateNames() {
        $tmpCities = [];
        foreach ($this->cities as $key => $value) {
            $city_translated = Lang::get()->translate($value->name);
            if ($city_translated) {
                if (!isset($tmpCities[$city_translated])) {
                    $this->cities[$key]->name = $city_translated;
                    $tmpCities[$city_translated] = 1;
                }
                else {
                    unset($this->cities[$key]);
                }
            }
        }
    }
    
    
    
}

