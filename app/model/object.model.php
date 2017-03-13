<?php
/**
 * Интерфейс объекта системы
 * 
 * @author Дамир Мухамедшин <damirmuh@gmail.com>
 * @package CorpusManager
 * @subpackage CorpusManagerModels
 * @version 1.0
 */
interface ObjectModel {
    
    /**
     * Добавление объекта в БД
     * 
     * @param array $vars Массив параметров нового объекта
     * @param boolean $now Указывает, нужно ли записать в БД прямо сейчас, или добавить в очередь
     */
    public function add(array $vars, $now = true);
    
    /**
     * Редактирование объекта
     * 
     * @param array $vars Массив новых параметров объекта
     * @param boolean $now Указывает, нужно ли записать в БД прямо сейчас, или добавить в очередь
     */
    public function edit(array $vars, $now = true);
    
    /**
     * Удаление объекта из БД
     * 
     * @param boolean $now Указывает, нужно ли удалить из БД прямо сейчас, или добавить в очередь
     */
    public function remove($now = true);
    
}