<?php
/**
 * Сервис для поддержки БД MySQL.
 * Оболочка над MySQLi.
 * 
 * @author Damir Mukhamedshin <d.muhamedshin@m-artkzn.ru>
 * @copyright (c) 2013, Damir Mukhamedshin
 * @version 1.2
 * @since 1.1 Реализованы транзакции и постоянное подключение к серверу
 * @since 1.2 Реализовано кэширование для select
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/ «Attribution-NonCommercial-ShareAlike»
 * @uses mysqli Драйвер MySQL
 */
class DB {
    /**
     * @var resource Соединение с БД
     */
    private $connection = null,
            /**
             * @var string Сервер БД по умолчанию
             */
            $server = 'localhost',
            /**
             * @var string Логин по умолчанию
             */
            $login = 'root',
            /**
             * @var string Пароль по умолчанию
             */
            $pass = '',
            /**
             * @var string База данных по умолчанию
             */
            $database = 'topstar',
            /**
             * @var integer Порт по умолчанию
             */
            $port = 3306,
            /**
             * @var string Кодировка по умолчанию
             */
            $charset = 'utf8mb4',
            /**
             * @var array Очередь запросов
             */
            $queue = [],
            /**
             * @var array Очередь данных для добавления в таблицы
             */
            $values = [],
            $operation = 0;
    
    public  $found_rows = 0,
            $timer = true;
    /**
     * @var integer Константы типов выдачи результатов выполнения запросов
     */
    const   OBJECT = 1,
            ASSOC = 2,
            COLLECT = 3;
    
    /**
     * Singleton для сервиса
     * @staticvar DB $db
     * @return \DB
     */
    public static function get() {
        static $db;
        if ($db === null)
            $db = new DB();
        return $db;
    }
    /**
     * Конструктор сервиса. Инициирует подключение к БД.
     * 
     * @uses config.inc.php Конфиги
     * @uses Cache Кэширующий сервис
     * @throws Exception
     */
    public function __construct() {
        $conf = $GLOBALS['config']['db'];
        foreach ($conf as $param => $val) {
            $this->$param = $val;
        }
    }
    
    private function connect() {
        try {
            $this->operation("connecting");
            if ($this->connection = new mysqli('p:' . $this->server, $this->login, $this->pass, $this->database, $this->port)) {
                $this->connection->set_charset($this->charset);
            }
            else {
                //throw new Exception('Can\'t connect to DB');
            }
            $this->stopOperation("connecting");
        } catch (Exception $e) {
            ErrorControl::get()->writeError( $e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine() );
        }
    }
    /**
     * Добавление запроса в очередь.
     * @param string $q Запрос
     */
    public function addQuery($q) {
        array_push($this->queue, $q);
    }
    /**
     * Удаление запроса из очереди.
     * @param string $q Запрос
     */
    public function removeQuery($q) {
        if ($index = array_search($q, $this->queue))
            unset($this->queue[$index]);
    }
    /**
     * Замена запроса в очереди.
     * @param string $replacement Заменяемый запрос
     * @param string $q Запрос
     */
    public function replaceQuery($replacement, $q) {
        if ($index = array_search($replacement, $this->queue))
            $this->queue[$index] = $q;
        else
            $this->addQuery($q);
    }
    /**
     * Выполнение одного запроса.
     * @param string $q Запрос
     * @return mixed Результат выполнения
     */
    public function query($q) {
        try {
            if ($this->connection === null) {
                $this->connect();
            }
            $this->operation($q);
            $return = $this->connection->query($q);
            $this->stopOperation($q);
            if ($return === false) {
                throw new Exception('[BD] Query with error ' . $q);
            }
            return $return;
        } catch (Exception $e) {
            ErrorControl::get()->writeError($e->getMessage(), $e->getTraceAsString(), "File: " . $e->getFile() . "\nLine: " . $e->getLine());
            return false;
        }
    }
    /**
     * Выполнение мультизапроса.
     * @param array $a Массив запросов, может быть пустым, сливается с массивом очереди запросов
     * @return boolean
     */
    public function multiQuery(array $a = [], $transaction = false, $free = false, $return_type = 'num') {
        if ($this->connection === null) {
            $this->connect();
        }
        $this->operation();
        $q = implode(";\n", array_values(array_merge($this->queue, $a)));
        $this->queue = array();
        if ($transaction) {
            $this->connection->autocommit(false);
        }
        $return = $this->connection->multi_query($q);
        if ($transaction) {
            if ($return) {
                if ($free) {
                    $errors = 0;
                    if ($return_type == 'num') {
                        $return = 0;
                    }
                    else {
                        $return = [];
                    }
                    do {
                        if ($r = $this->connection->store_result()) {
                            if ($return_type == 'num') {
                                $return += $r->num_rows;
                            }
                            else {
                                array_push($return, $r->num_rows);
                            }
                        }
                        else {
                            if ($return_type == 'num') {
                                $return += $this->connection->affected_rows;
                            }
                            elseif ($return_type == 'ids') {
                                array_push($return, $this->connection->insert_id);
                            }
                            else {
                                array_push($return, $this->connection->affected_rows);
                            }
                        }
                        $errors += $this->connection->errno;
                    }
                    while ($this->connection->more_results() && $this->connection->next_result());
                    $this->connection->commit();
                    $return = !$errors ? $return : false;
                }
                else
                    $return = $this->connection->commit();
            }
            else
                $this->connection->rollback();
            $this->connection->autocommit(true);
        }
        $this->stopOperation();
        return $return;
    }
    /**
     * Выполнение выборки по запросу.
     * @param string $q Запрос
     * @param integer $mode Тип выдачи OBJECT, ASSOC, COLLECT
     * @return mixed
     * @throws Exception
     */
    public function select($q, $mode = self::OBJECT, $once = false, $cache = 0) {
        //echo $q;
        $qKey = 'query_' . $mode . '_' . md5($q);
        if ($cache && ($return = Cache::redis()->get($qKey)) !== false) {
            if (isset($return['found_rows'])) {
                $this->found_rows = $return['found_rows'];
                unset($return['found_rows']);
            }
            if ($once)
                return current($return);
            else
                return $return;
        }
        else {
            if (is_object($result = $this->query($q))) {
                $this->operation();
                $return = $this->fetch($result, $mode);
                if ($cache) {
                    $addToCache = $return;
                }
                if (preg_match("/\bSQL_CALC_FOUND_ROWS\b/", $q)) {
                    $this->found_rows = $this->select("SELECT FOUND_ROWS()", self::COLLECT, true, 0)[0];
                    if ($cache) {
                        $addToCache['found_rows'] = $this->found_rows;
                    }
                }
                if ($cache) {
                    Cache::redis()->set($qKey, $addToCache, ['nx', 'ex' => $cache]);
                    unset($addToCache);
                }
                $this->stopOperation();
                $this->free();
                if ($once)
                    return current($return);
                else
                    return $return;
            }
            else {
                throw new Exception('Select error: ' . $this->connection->error);
            }
        }
    }
    /**
     * Выполнение мультивыборки по мультизапросу.
     * @param array $a Массив запросов, может быть пустым, сливается с массивом очереди запросов
     * @param integer $mode Тип выдачи OBJECT, ASSOC, COLLECT
     * @throws Exception
     */
    public function multiSelect(array $a = [], $mode = self::OBJECT, $cache = 0) {
        $qKey = 'query_' . $mode . '_' . md5(implode(";\n", $a));
        if ($cache && ($return = Cache::redis()->get($qKey)) !== false) {
            return $return;
        }
        else {
            if ($this->multiQuery($a)) {
                $this->operation();
                $return = [];
                do {
                    array_push($return, $this->fetch($this->connection->store_result(), $mode));
                }
                while ($this->connection->next_result());
                if ($cache) {
                    Cache::redis()->set($qKey, $return, ['nx', 'ex' => $cache]);
                }
                $this->stopOperation();
                return $return;
            }
            else {
                throw new Exception('Multi select error:' . $this->connection->error);
            }
        }
    }
    /**
     * Выдача по результату запроса.
     * @param mysqli_result $result Результат запроса
     * @param integer $mode Тип выдачи OBJECT, ASSOC, COLLECT
     * @return mixed
     */
    public function fetch(mysqli_result $result, $mode = self::OBJECT) {
        if ($mode == self::OBJECT) {
            $return = [];
            while ($obj = $result->fetch_object())
                array_push($return, $obj);
            $result->free();
            return $return;
        }
        else {
                $return = $result->fetch_all(($mode == self::ASSOC ? MYSQLI_ASSOC : MYSQLI_NUM));
                $result->free();
                return $return;
        }
    }
    /**
     * Добавление записи в БД.
     * @param string $to Название таблицы
     * @param array $columns Ассоциативный массив колонок
     * @param boolean $multi Флаг мультидобавления
     * @return mixed Возвращает ID добавленой записи и FALSE в случае ошибки, ничего не возращает в мультирежиме
     */
    public function insert($to, array $columns, $multi = false) {
        $iq = "INSERT INTO `$to` SET ";
        foreach ($columns as $col => &$val) {
            $val = "`$col` = " . $this->escape($val, true);
        } unset($val);
        $iq .= implode(', ', $columns);
        if ($multi)
            $this->addQuery($iq);
        elseif ($this->query($iq)) 
            return $this->connection->insert_id;
        else
            return false;
    }
    /**
     * Мультидобавление записей
     * @param string $to Название таблицы
     * @param array $columns Массив ассоциативных массивов колонок
     * @param boolean $transaction Флаг, указывающий, нужно ли использовать транзакцию
     * @return mixed
     */
    public function multiInsert($to, array $columns, $transaction = true) {
        foreach ($columns as $col) {
            $this->insert($to, $col, true);
        }
        return $this->multiQuery([], $transaction, true, 'ids');
    }
    /**
     * Добавление данных для добавления в БД
     * @param string $to Название таблицы
     * @param array $values Данные
     */
    public function addValues($to, array $values) {
        if (!isset($this->values[$to]))
            $this->values[$to] = [];
        array_push($this->values[$to], $values);
    }
    /**
     * Добавление данных в БД
     * @param mixed $to Название таблицы, может быть null, в этом случае добавление данных произойдет во все таблицы в очереди
     * @param array $values Данные
     * @param boolean $now Выполнить добавление сейчас или добавить в очередь
     * @param boolean $transaction Нужно ли использовать транзакцию
     * @return mixed
     */
    public function insertValues($to = null, array $values = [], $now = true, $transaction = true, $return = 'affected') {
        if ($to === null) {
            foreach (array_keys($this->values) as $to) {
                $this->insertValues($to, [], false);
            }
            return  $this->multiQuery([], true, true);
        }
        elseif (isset($this->values[$to])) {
            if ($values)
                $this->addValues($to, $values);
            $values = [];
            foreach ($this->values[$to] as $val) {
                if (!count($val))
                    continue;
                $current = [];
                foreach ($val as $v) {
                    if (is_bool($v))
                        array_push($current, $v ? 1 : 0);
                    elseif (is_null($v))
                        array_push($current, 'NULL');
                    else
                        array_push($current, $this->escape($v, true));
                }
                array_push($values, '(' . implode(', ', $current) . ')');
            }
            unset($this->values[$to]);
            $iq = "INSERT INTO `$to` VALUES " . implode(', ', $values);
            if ($now) {
                if ($transaction) {
                    $this->connection->autocommit(false);
                }
                $result = $this->query($iq);
                if ($transaction) {
                    if ($result) {
                        $result = 0;
                        do {
                            $result += $this->connection->affected_rows;
                        }
                        while ($this->connection->more_results() && $this->connection->next_result());
                        $this->connection->commit();
                    }
                    else {
                        $this->connection->rollback();
                    }
                    $this->connection->autocommit(true);
                    return $result > 0 ? $result : false;
                }
                return $this->connection->affected_rows > 0 ? ($return == 'affected' ? $this->connection->affected_rows : $this->connection->insert_id) : false;
            }
            else
                $this->addQuery($iq);
        }
        elseif ($values) {
            $this->addValues($to, $values);
            return $this->insertValues($to, array(), $now, $transaction, $return);
        }
        else
            return false;
    }
    
    public function free() {
        do {
            if ($r = $this->connection->store_result())
                $r->free_result();
        }
        while ($this->connection->more_results() && $this->connection->next_result());
    }
    
    public function escape($str, $quotes = false) {
        if ($this->connection === null) {
            $this->connect();
        }
        if (is_string($str) || is_scalar($str)) {
            $is_bit = is_string($str) && preg_match("/^1[01]+$/", $str);
            return ($is_bit ? "b" : "") . ($quotes || $is_bit ? "'" : '') . $this->connection->real_escape_string($str) . ($quotes || $is_bit ? "'" : '');
        }
        elseif (is_null($str))
            return 'NULL';
        elseif (is_array($str))
            return ($quotes ? "'" : '') . $this->connection->real_escape_string(json_encode($str)) . ($quotes ? "'" : '');
        elseif (is_bool($str))
            return ($quotes ? "'" : '') . ($str ? 1 : 0) . ($quotes ? "'" : '');
        else
            return ($quotes ? "'" : '') . $this->connection->real_escape_string(serialize($str)) . ($quotes ? "'" : '');
    }
    
    public function lastError() {
        return $this->connection->errno . " " . $this->connection->error;
    }
    
    public function affected() {
        return $this->connection->affected_rows;
    }
    
    private function operation($q = '') {
        $this->operation++;
        if (class_exists('Timer') && $this->timer) {
            Timer::get()->start('DB operation ' . $this->operation . ($q ? ' ' . $q : ''));
        }
    }
    
    private function stopOperation($q = '') {
        if (class_exists('Timer') && $this->timer) {
            Timer::get()->stop('DB operation ' . $this->operation . ($q ? ' ' . $q : ''));
        }
    }

}