<?php
/**
 *  Модель для получения рейтинга, исходя из статистики запросов с ресурса seobook.com
 */
class KeyWordsUpdateModel extends AbstractRatingUpdateModel {
    
    /**
     * @var string Строка для поиска
     */
    private $query;    
    
    public function __construct($userID) {
        parent::__construct($userID);
        $this->query = mb_strtolower($this->getNameForQuery());
    }
    
    /**
     * Отдает имя для запроса
     * 
     * @return mixed Имя для поиска, либо false в случае неудачи 
     */
    private function getNameForQuery(){
        $array = DB::get()->select('SELECT `name`, `surname`, `name_en`, `surname_en`, `nickname` FROM `users` WHERE `id`='.$this->userID, DB::ASSOC, true);
        $string = '';
        if ($array['name_en'] != '' && $array['surname_en'] != '') {
            $string = $array['name_en'].' '.$array['surname_en'];
        }
        elseif ($array['name'] != '' && $array['surname'] != '') {
            $string = $array['name'] . ' ' . $array['surname'];
        }
        elseif ($array['nickname'] != '') {
                $string = $array['nickname'];
        }
        if ($string != '')
            return $string;
        else        
            return false;    
    }
    
    /**
     * Логинится на seobook.com и пишет куки
     */
    private function login(){
        $page = $this->curl->post('http://seobook.com/user', '', [], 'text/html', '', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3', false, 1, 
                          [CURLOPT_COOKIESESSION => true, CURLOPT_POST => 1, CURLOPT_HEADER => 1, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_FOLLOWLOCATION => 1,
                           CURLOPT_HTTPHEADER => ['Accept-Charset: utf-8',
                                                  'Accept-Language: en-us,en;q=0.7,bn-bd;q=0.3',
                                                  'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                                                  'Origin: http://www.seobook.com',
                                                  'Upgrade-Insecure-Requests: 1'],
                           CURLOPT_COOKIEFILE => getcwd ().'/cookies/cook_keywords.txt',  CURLOPT_COOKIEJAR => getcwd ().'/cookies/cook_keywords.txt',
                           CURLOPT_RETURNTRANSFER => 1]);
        preg_match('/<input type="hidden" name="form_build_id" value="(.*)"/', $page, $matches);
        $form_build_id = trim($matches[1]);
        $page =  $this->curl->post('http://seobook.com/user', ['name' => 'svmatulis@gmail.com', 'pass' => 'magentaart', 'form_build_id' => $form_build_id, 'form_id' => 'user_login', 'op' => 'Log in'], [], 'text/html', '', 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0', false, 1, 
                          [CURLOPT_COOKIESESSION => true, CURLOPT_POST => 1, CURLOPT_HEADER => 1, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_FOLLOWLOCATION => 1,
                           CURLOPT_HTTPHEADER => ['Host: www.seobook.com',
                                                  'Accept-Charset: utf-8',
                                                  'Accept-Language: en-us,en;q=0.7,bn-bd;q=0.3',
                                                  'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                                                  'Origin: http://www.seobook.com',
                                                  'Upgrade-Insecure-Requests: 1',
                                                  'Connection: keep-alive'],
                           CURLOPT_REFERER => 'http://www.seobook.com/user',  
                           CURLOPT_COOKIEFILE => getcwd ().'/cookies/cook_keywords.txt',  CURLOPT_COOKIEJAR => getcwd ().'/cookies/cook_keywords.txt',
                           CURLOPT_RETURNTRANSFER => 1]);   
    }

    /**
     * Получение числа запросов путем парсинга
     * 
     * @return boolean Результат выполнения операции
     */
    
    public function getCount() {
        $this->login();
        $tr = new Translit();
        $page = $this->curl->get('http://tools.seobook.com/keyword-tools/seobook/', ['keyword' => $tr->cyrToLat($this->query)], 'text/html', 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0', false, 1, [CURLOPT_COOKIEFILE => getcwd ().'/cookies/cook_keywords.txt',  CURLOPT_COOKIEJAR => getcwd ().'/cookies/cook_keywords.txt']);
        $p = '/<table class="table table-hover table-condensed">.*<td style="max-width: 220px;">.*<\/td>.*<td>(.*)<\/td>.*<\/table>/isU';
        preg_match($p, $page, $matches);
        if(isset($matches[1])){
            $this->count = str_replace(',', '', $matches[1]);
            return true;   
        }
        else {
            $this->count = 0;
            return false;
        }
    }
}

