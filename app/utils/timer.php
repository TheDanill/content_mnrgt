<?php
/**
 * Сервис для подсчета времени исполнения отдельных кусков кода.
 * 
 * @author Damir Mukhamedshin <d.muhamedshin@m-artkzn.ru>
 * @copyright (c) 2013, Damir Mukhamedshin
 */
class Timer {
    /**
     * Singleton для сервиса
     * @staticvar null $timer
     * @return \Timer
     */
    public static function get() {
	static $timer = null;
	if ($timer === null)
	    $timer = new Timer();
	return $timer;
    }
    /**
     *
     * @var array Массив таймеров
     */
    private $timers = array(),
            /**
             * @var string Последний таймер
             */
	    $last = 'main';
    /**
     * Конструктор, запускает основной таймер.
     */
    public function __construct() {
	$this->timers['main']['start'] = microtime(true);
    }
    /**
     * Запускает таймер.
     * @param string $name Название таймера
     * @return null
     */
    public function start($name) {
	if (isset($this->timers[$name]['stop']))
	    return;
	$this->timers[$name]['start'] = microtime(true);
	$this->last = $name;
    }
    /**
     * Останавливает таймер.
     * @param string $name Название таймера, если не указан, останавливается последний вызванный таймер
     */
    public function stop($name = null) {
	if ($name === null && !isset($this->timers[$this->last]['stop'])) {
	    $this->timers[$this->last]['stop'] = microtime(true);
	} elseif (!isset($this->timers[$name]['stop']) && isset($this->timers[$name]['start'])) {
	    $this->timers[$name]['stop'] = microtime(true);
	}
    }
    /**
     * Получить текущее значение таймера.
     * @param string $name Название таймера
     * @return mixed Возвращает количество микросекунд, если произошла ошибка, возвращает FALSE.
     */
    public function get_name($name) {
	if (isset($this->timers[$name]['stop']))
	    return $this->timers[$name]['stop'] - $this->timers[$name]['start'];
	elseif (!isset($this->timers[$name]['start']))
	    return false;
	else
	    return microtime(true) - $this->timers[$name]['start'];
    }
    /**
     * Останавливает все таймеры.
     */
    public function stop_all() {
	$gt = microtime(true);
	foreach ($this->timers as &$timer) {
	    if (!isset($timer['stop']))
		$timer['stop'] = $gt;
	} unset($timer);
    }
    /**
     * Выводит информацию о таймере.
     * @param string $name Название таймера, если не указано, выводится информация обо всех таймерах
     */
    public function dump($name = null) {
	if ($name === null) {
	    $this->stop_all();
	    foreach ($this->timers as $name => $timer) {
		echo $name . " - " . number_format($timer['stop'] - $timer['start'], 6, ".", "") . " sec.\n";
		unset($name, $timer);
	    }
	} elseif (isset($this->timers[$name])) {
	    $this->stop($name);
	    echo $name . " - " . ( $this->timers[$name]['stop'] - $this->timers[$name]['start'] ) . " sec.";
	}
    }

}