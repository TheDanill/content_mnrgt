<?php
/**
 * Класс для работы с HTTP(s)-протоколом.
 * @uses curl cURL
 */
class HTTP {
    /**
     * Создает подключение и устанавливает параметры для него.
     * @param string $uri URI запрашиваемого документа.
     * @param array $params GET-параметры, передаваемые вместе с URI.
     * @param string $userAgent Подставляемый User-Agent.
     * @param string $type Ожидаемый тип документов
     * @param string $proxy Прокси-сервер, используемый для подключения.
     * @param integer $headers Флаг, указывающий, нужно ли включать заголовки в результат выполнения запроса.
     * @param array $options Дополнительные параметры cURL.
     * @return resource Указатель на ресурс подключения cURL.
     */
    private static function connect($uri, $params, $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', $type = 'text/html', $proxy = false, $headers = 1, $options = array()) {
	if (sizeof($params))
	    $uri .= '?' . http_build_query($params);
	$con = curl_init($uri);
	//curl_setopt($con, CURLOPT_HEADER, 1);
	curl_setopt($con, CURLOPT_HTTPHEADER, array(
	    "Accept: " . $type,
            "Pragma: no-cache",
            "Accept-Language: en-US;q=0.8,en;q=0.4"
            
	));
        curl_setopt($con, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($con, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($con, CURLINFO_HEADER_OUT, $headers);
        if ($proxy !== false) {
            curl_setopt($con, CURLOPT_PROXY, $proxy);
            curl_setopt($con, CURLOPT_TIMEOUT, 5);
        }
        if (count($options)) {
            foreach ($options as $opt => $val) {
                curl_setopt($con, $opt, $val);
            }
        }
	return $con;
    }
    /**
     * Выполняет запрос.
     * @param resource $con Указатель на подключение cURL.
     * @param boolean $error_stop Флаг, указывающий, нужно ли останавливать выполнение при ошибке.
     * @return boolean|string|array Возвращает false, если произошла необрабатываемая ошибка, иначе строку с результатом выполнения запроса или массив с информацией, если результата нет.
     * @throws Exception
     */
    private static function result($con, $error_stop = false) {
	$result = curl_exec($con);
	if (!curl_errno($con) && curl_getinfo($con, CURLINFO_HTTP_CODE) < 400) {
	    return $result;
	} elseif (curl_errno($con)) {
	    if (_DEBUG)
		throw new Exception('HTTP Error: ' . curl_errno($con) . ' ' . curl_error($con));
	    elseif ($error_stop)
		throw new Exception('HTTP Error');
	    return false;
	}
	else {
	    return array(
		'info' => curl_getinfo($con),
		'result' => $result
	    );
	}
    }
    /**
     * Возвращает результат в виде декодированного JSON (ассоциативный массив).
     * @param string|array $result Результат запроса.
     * @return array Декодированный JSON.
     */
    private static function return_json($result) {
	if (is_string($result))
	    return json_decode($result, true);
	elseif (is_array($result)) {
	    $result['result'] = json_decode($result['result'], true);
	}
	return $result;
    }
    /**
     * Выполняет GET-запрос.
     * @param string $uri URI запрашиваемого документа.
     * @param array $params Параметры GET-запроса.
     * @param string $type Тип ожидаемого документа.
     * @param string $userAgent Подставляемый User-Agent.
     * @param string $proxy Прокси-сервер, используемый для подключения.
     * @param integer $headers Флаг, указывающий, нужно ли включать заголовки в результаты запроса.
     * @param array $options Дополнительные параметры cURL.
     * @return string Результат запроса.
     */
    public static function get($uri, $params = array(), $type = 'text/html', $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', $proxy = false, $headers = 1, $options = array()) {
	$con = self::connect($uri, $params, $userAgent, $type, $proxy, $headers, $options);
	return self::result($con);
    }
    /**
     * Выполняет GET-запрос и возвращает результат в виде декодированного JSON.
     * @param string $uri URI запрашиваемого файла.
     * @param array $params Параметры GET-запроса.
     * @param string $userAgent Подставляемый User-Agent.
     * @param string $proxy Прокси-сервер, используемый для подключения.
     * @param integer $headers Флаг, указывающий, нужно ли включать заголовки в результаты запроса.
     * @param array $options Дополнительные параметры cURL.
     * @return array Декодированный JSON.
     */
    public static function get_json($uri, $params = array(), $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', $proxy = false, $headers = 1, $options = array()) {
        $result = self::get($uri, $params, 'application/json', $userAgent, $proxy, $headers, $options);
	return self::return_json($result);
    }
    /**
     * Выполняет POST-запрос
     * @param string $uri URI запрашиваемого документа.
     * @param string|array $data Данные, отправляемые в запросе.
     * @param array $params Параметры запроса.
     * @param string $type Тип ожидаемого контента.
     * @param string $content_type Тип отправляемых данных.
     * @param string $userAgent Подставляемый User-Agent.
     * @param string $proxy Прокси-сервер, используемый для подключения.
     * @param integer $headers Флаг, указывающий, нужно ли включать заголовки в результаты запроса.
     * @param array $options Дополнительные параметры cURL.
     * @return string Результат запроса.
     */
    public static function post($uri, $data = "", $params = array(), $type = 'text/html', $content_type = '', $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', $proxy = false, $headers = 1, $options = array()) {
	$con = self::connect($uri, $params, $userAgent, $type, $proxy, $headers, $options);
	curl_setopt($con, CURLOPT_POST, 1);
	curl_setopt($con, CURLOPT_POSTFIELDS, $data);
        if ($content_type)
            curl_setopt($con, CURLOPT_HTTPHEADER, array('Content-type: ' . $content_type, 'Accept: ' . $type));
	return self::result($con);
    }
    /**
     * Выполняет POST-запрос и возвращает результат в виде декодированного JSON.
     * @param string $uri URI запрашиваемого файла.
     * @param array $data Данные, отправляемые в запросе.
     * @param array $params Параметры запроса.
     * @param string $type Тип ожидаемого контента.
     * @param string $content_type Тип отправляемых данных.
     * @param string $userAgent Подставляемый User-Agent.
     * @param string $proxy Прокси-сервер, используемый для подключения
     * @param integer $headers Флаг, указывающий, нужно ли включать заголовки в результаты запроса.
     * @param array $options Дополнительные параметры cURL.
     * @return array Декодированный JSON.
     */
    public static function post_json($uri, $data = array(), $params = array(), $type = 'application/json', $content_type = '', $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', $proxy = false, $headers = 1, $options = array()) {
        if ($content_type == 'application/json')
            $result = self::post($uri, json_encode($data), $params, $type, $content_type, $userAgent, $proxy, $headers, $options);
        else
            $result = self::post($uri, http_build_query($data), $params, $type, $content_type, $userAgent, $proxy, $headers, $options);
	return self::return_json($result);
    }
    
    public static function delete($uri, $params = array(), $type = 'text/html', $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', $proxy = false) {
	$con = self::connect($uri, $params, $userAgent, $type, $proxy);
	curl_setopt($con, CURLOPT_CUSTOMREQUEST, 'DELETE');
	$result = self::result($con);
	if ($result == '')
	    return true;
	else
	    return $result;
    }

    public static function put($uri, $data = array(), $params = array(), $type = 'application/json', $content_type = 'application/json', $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', $proxy = false) {
	$con = self::connect($uri, $params, $userAgent, $type, $proxy);
        curl_setopt($con, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($con, CURLOPT_POSTFIELDS, $content_type == 'application/json' ? json_encode($data) : http_build_query($data));
        if ($content_type)
            curl_setopt($con, CURLOPT_HTTPHEADER, array('Content-type: ' . $content_type, 'Accept: ' . $type));
	$result = self::result($con);
	if ($result == '')
	    return true;
	else
	    return $result;
    }
    /**
     * Загружает файл с удаленного сервера.
     * @param string $uri URI загружаемого файла.
     * @param string $to Путь, куда нужно сохранить файл.
     * @param array $params Параметры запроса.
     * @param string $type Ожидаемый тип файла.
     * @param string $userAgent Подставляемый User-Agent.
     * @param string $proxy Прокси-сервер, используемый для подключения.
     * @param integer $headers Флаг, определяющий, нужно ли включать заголовки в результат запроса.
     * @return mixed Результат запроса или путь к сохраненному файлу.
     */
    public static function load($uri, $to, $params = array(), $type = '*/*', $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36', $proxy = false, $headers = 1) {
        $f = fopen($to, 'w');
        $con = self::connect($uri, $params, $userAgent, $type, $proxy, $headers, array(CURLOPT_FILE => $f, CURLOPT_TIMEOUT => 0));
        $result = self::result($con);
        fclose($f);
        if (!$result)
            return $result;
        else
            return $to;
    }

}