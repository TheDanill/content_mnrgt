<?php

/**
 * Модель словаря
 */
class Vocabulary {
    /**
     * @var string Тип словаря
     */
    private $type,
            /**
             * @var array Возможные типы словаря
             */
            $types = ['unprintable', 'intolerable'],
            /**
             * @var array Массив словарных слов
             */
            $words = [],
            /**
             * @var Регулярное выражение для проверки слов
             */
            $pattern = '/';
                
    /**
     * Конструктор объекта
     * @param string $type Тип словаря
     */
    public function __construct($type) {
        $this->setType($type);
    }
    
    /**
     * Установка типа словаря
     * @param string $type Тип словаря из массива types
     */
    public function setType($type){
        if(in_array($type, $this->types)){
            $this->type = $type;
            if($pattern = Cache::redis()->get('vocabulary_'.$this->type.'_pattern')){
                $this->pattern = $pattern;
            }
        }
    }


    /**
     * Загрузка слов из файла в БД
     * @return boolean Результат загрузки
     */
    public function loadVocabulary() {
        if(!empty($_FILES)){
            if(isset($_FILES['file']['tmp_name'])) {
                DB::get()->query('TRUNCATE TABLE `vocabulary_'.$this->type.'`');
                $this->words = file($_FILES['file']['tmp_name']);
                $values = '';
                foreach ($this->words as $value){
                    $value = str_replace('\n', "", $value);
                    $values .= '("'.trim($value).'"), ';
                }
                $values = substr(trim($values), 0, -1);
                $sql = 'INSERT INTO `vocabulary_'.$this->type.'` (`word`) VALUES '.$values;
                if(DB::get()->query($sql)) {
                    $this->formPattern();
                    Cache::redis()->set('vocabulary_'.$this->type.'_pattern', $this->pattern);
                    return true;
                }
            }   
        }   
        return false;
    }
    
    /**
     * Получение слов из БД
     * @return boolean Результат запроса
     */
    public function getWords(){
        if($this->words = DB::get()->select('SELECT `word` FROM `vocabulary_'.$this->type.'`')){
            return true;
        }
        return false;
    }
    
    /**
     * Формирование рег.выражения
     * @return string Строка регулярного выражения
     */
    private function formPattern(){
        $this->pattern = '/';
        foreach ($this->words as $value){
            $this->pattern .= ''.str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '],'',$value).'|';
        } 
        $this->pattern = substr(trim($this->pattern), 0, -1);
        $this->pattern = $this->pattern.'/isU';
        return $this->pattern;
    }
    
    /**
     * Проверка входящего текста
     * @param string $text Исходный текст
     * @return string $text Отфильтрованный текст
     */
    public function checkText($text){
        preg_match_all($this->pattern, $text, $matches);
        if(!empty($matches[0])) {
            switch ($this->type) {
                case 'intolerable':
                    $text = preg_replace($this->pattern, '', $text);
                break;
                case 'unprintable':
                    $text = preg_replace($this->pattern, '!@#$%^&*', $text);
                break;
            }            
            return $text;
        }

    }
    
}

