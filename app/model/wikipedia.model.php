<?php
/**
 * 
 * Модель для парсинга данных из википедии в формате
 * 
 */
class WikipediaModel {
    
    /**
     * 
     * @var string Название языка
     */
    private $lang,
    
            /**
             * 
             * @var string Ссылка на пользователя в Wikipedia
             */
            $link,
    
            /**
             * 
             * @var string Имя пользователя в Википедии 
             */
            $wiki_user_name,
            
            /**
             * 
             * @var curl Объекта для запроса
             */
            $curl,
            
            /**
             * 
             * @var mixed Ответ
             */
            $answer;
    
    public function getDescriptionByLink($link) {
        try {
            // Проверяем на корректность ссылку
            $this->link = $this->checkLinkOnCorrectness( $link );
            if ($this->link) {
                // Забираем данные 
                if ($this->getDataFromLink($this->link)) {
                    // Создаем объект для запроса
                    $this->curl = new HTTP();
                    $data = $this->curl->get_json('https://' . $this->lang . '.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&titles=' . $this->wiki_user_name, [], '', false, 0);
                    if (isset($data['query']['pages'])) {
                        foreach ($data['query']['pages'] as $wiki_data) {
                            return $wiki_data['extract'];
                        }
                    } else {
                        throw new Exception('[WIKIPEDIA] Не правильная ссылка. Данных нет:' . $link);
                    }
                } else {
                    throw new Exception('[WIKIPEDIA] Данные не спарсились');
                }
            } else {
                throw new Exception('[WIKIPEDIA] Ссылка не корректа: ' . $link);
            }
        } catch(Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }
    
    private function checkLinkOnCorrectness( $link ) {
        try {
            if ($link !== NULL) {
                $link =  urldecode( $link );
                if ( $link ) {
                    return $link;
                } else {
                    throw new Exception("[WIKIPEDIA] Конвертация не прошла.");
                }
            } else {
                throw new Exception("[WIKIPEDIA] Создан объект с пустой ссылкой.");
            }
        } catch(Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }
    
    private function getDataFromLink($link) {
        // REGEX для парсинга языка и имени 
        $regex = "/http[s]+:\\/\\/(.*?)\\.wikipedia\\.org\\/wiki\\/(.*)[?]*/s";
        preg_match($regex, $link, $matches);
        if (is_string($matches[0]) && is_string($matches[1]) && !empty($matches[0]) && !empty($matches[1])) {
            // Язык
            $this->lang = $matches[1];
            // Имя пользователя в Wikipedia
            $this->wiki_user_name = $matches[2];
            
            return true;
        } else {
            return false;
        }
    }
    
}