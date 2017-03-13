<?php

/**
 * Модель стран
 * 
 */
class CountryModel {

    /**
     * Получение списка всех стран
     * 
     * @return array Массив объектов стран
     */
    public function getCountries() {
        return DB::get()->select('SELECT `countries`.`id`, `countries`.`country_name_en` as name FROM `countries`', DB::OBJECT, false, 86400);
    }

    public function getCountriesArrayID() {
        $countries = $this->getCountries();
        $tmpCountries = [];
        if ($countries && count($countries)) {
            foreach ($countries as $country) {
                $tmpCountries[intval($country->id)] = $country;
            }
        }
        return $tmpCountries;
    }

    /**
     * Проверка id на наличие в базе такой страны
     */
    public function issetCountry($id) {
        if ($result = DB::get()->select('SELECT `country_name` FROM `countries` WHERE `id`=' . $id, DB::ASSOC)) {
            return $result;
        }
        return false;
    }
    
    /*
     * Взять страну по ISO коду страны
     */
    public function getCountryIdByISO($iso) {
        if ($result = DB::get()->select('SELECT `id` FROM `countries` WHERE `country_code`="' . strtoupper($iso) . '";', DB::ASSOC)) {
            return $result[0]['id'];
        }
        return false;
    }
    

}
