<?php
/**
 * Контроллер запросов
 * 
 * @author Дамир Мухамедшин <damirmuh@gmail.com>
 * @package CorpusManager
 * @subpackage CorpusManagerUtilities
 * @version 1.2
 */
trait RequestControl {
    
    /**
     * @var array Массив отфильтрованных значений
     */
    protected   $vars = array(),
                /**
                 * @var array Массив ошибок при проверке
                 */
                $errors = array();

    /**
     * Проверка значений по правилам из fields
     * 
     * @param string|array $from Массив проверяемых данных, может быть равно "$_POST", тогда будет использоваться массив $_POST
     * @param string|array $vars Массив отфильтрованных значений, куда будут записываться правильные данные, может быть равно "$this", тогда будет использоваться массив $this->vars
     * @param string|array $errors Массив ошибок, куда будут записываться ошибки при проверке, может быть равно "$this", тогда будет использоваться массив $this->errors
     * @return boolean Правильность данных в массиве $from
     */
    protected function checkVars($from = '$_POST', &$vars = '$this', &$errors = '$this') {
        if ($from == '$_POST')
            $from = $_POST;
        if ($vars == '$this')
            $vars = &$this->vars;
        if ($errors == '$this')
            $errors = &$this->errors;
        foreach ($this->fields[$this->action] as $n => $r) {
            if (isset($from[$n])) {
                $v = $from[$n];
                /**
                 * @since 1.1
                 */
                if (is_array($v)) {
                    $vars[$n] = $errors[$n] = [];
                    $tmp = $this->fields[$this->action];
                    if ($r[0] == 'array') {
                        $this->fields[$this->action] = $r[1];
                        $this->checkVars($v, $vars[$n], $errors[$n]);
                    }
                    elseif ($r[0] == 'arrayOf') {
                        $this->fields[$this->action] = $r[1];
                        foreach ($v as $key => $var) {
                            $this->checkVars($var, $vars[$n][$key], $tmpErrors);
                            if ($tmpErrors) {
                                $errors[$n][$key] = $tmpErrors;
                            }
                            unset($tmpErrors);
                        }
                    }
                    else {
                        foreach ($v as $key => $var) {
                            $this->fields[$this->action] = [$n => $r];
                            $this->checkVars([$n => $var], $tmpVars, $tmpErrors);
                            if ($tmpErrors) {
                                $errors[$n][$key] = $tmpErrors[$n];
                            }
                            if (isset($tmpVars[$n])) {
                                $vars[$n][$key] = $tmpVars[$n];
                            }
                            unset($tmpErrors, $tmpVars);
                        }
                    }
                    $this->fields[$this->action] = $tmp;
                    if (!count($errors[$n])) {
                        unset($errors[$n]);
                    }
                }
                else {
                    switch ($r[0]) {
                        case 'text':
                            if ($this->notNull($v) && isset($r[2]) && !$this->lt($v, $r[2]))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = htmlspecialchars(Filter::mb_trim($v));
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'text_en':
                            if ($this->notNull($v) && ((isset($r[2]) && !$this->lt($v, $r[2])) || !preg_match("/^[a-zA-Z\-\s]+$/", Filter::mb_trim($v))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = htmlspecialchars(Filter::mb_trim($v));
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        /**
                         * @since 1.2
                         */
                        case 'word':
                            //if ($this->notNull($v) && (!mb_ereg("^(\"[а-яА-ЯёЁӘәӨөҮүҖҗҢңҺһ0-9\*\-]+\"|[а-яА-ЯёЁӘәӨөҮүҖҗҢңҺһ0-9\*\-]+)(\s+\-([а-яА-ЯёЁӘәӨөҮүҖҗҢңҺһ0-9\-\*]+|\"[а-яА-ЯёЁӘәӨөҮүҖҗҢңҺһ0-9\-\*]+\"))*$", Filter::mb_trim($v)) || (isset($r[2]) && !$this->lt($v, $r[2])))) {
                            if ($this->notNull($v) && (!preg_match("/^([a-zA-Zа-яА-ЯёЁӘәӨөҮүҖҗҢңҺһ0-9\*\.\!\?,:;—\-\(\)\[\]\{\}“”\"\'»«]*)(\s+\-([a-zA-Zа-яА-ЯёЁӘәӨөҮүҖҗҢңҺһ0-9\*\.\!\?,:;—\-\(\)\[\]\{\}“”\"\'»«]*))*$/u", Filter::mb_trim($v)) || (isset($r[2]) && !$this->lt($v, $r[2])))) {
                                $errors[$n] = true;
                            }
                            elseif ($this->notNull($v)) {
                                $vars[$n] = mb_strtolower(Filter::mb_trim($v));
                            }
                            elseif ($r[1]) {
                                $errors[$n] = true;
                            }
                            break;
                        case 'html':
                            if ($this->notNull($v) && isset($r[2]) && !$this->lt($v, $r[2]))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = Filter::sanitizeHTML(Filter::mb_trim($v));
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'email':
                            if ($this->notNull($v) && !preg_match("/^[a-z0-9_\-\.]{1,}@[a-z0-9\-\.]{1,}\.[a-z]{2,4}$/i", Filter::mb_trim($v)))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = Filter::mb_trim($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'md5':
                            if ($this->notNull($v) && !preg_match("/^[a-z0-9]{32}$/i", Filter::mb_trim($v))) {
                                $errors[$n] = true;
                            }
                            elseif ($this->notNull($v)) {
                                $vars[$n] = mb_strtolower(Filter::mb_trim($v));
                            }
                            elseif ($r[1]) {
                                $errors[$n] = true;
                            }
                            break;
                        case 'phone':
                            if ($this->notNull($v) && !preg_match("/^\+?[0-9]{10,15}$/i", Filter::phone(Filter::mb_trim($v))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = Filter::mb_trim($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'time':
                            if ($this->notNull($v) && (!(int)strtotime(Filter::mb_trim($v)) || (isset($r[2]) && (int)strtotime(Filter::mb_trim($v))>(int)strtotime($r[2]))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = strtotime(Filter::mb_trim($v));
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'date':
                            if ($this->notNull($v) && (!strtotime(Filter::mb_trim($v)) || (isset($r[2]) && (($lt = (int)strtotime($r[2])) || $lt = (int)strtotime($vars[$r[2]])) && (int)strtotime(Filter::mb_trim($v)) < $lt) || (isset($r[3]) && (($gt = (int)strtotime($r[3])) || $gt = (int)strtotime($vars[$r[3]])) && (int)strtotime(Filter::mb_trim($v)) > $gt))) {
                                $errors[$n] = true;
                            }
                            elseif ($this->notNull($v)) {
                                $vars[$n] = Filter::toDateFormat(Filter::mb_trim($v), isset($r[4]) ? $r[4] : 'd.m.Y');
                            }
                            elseif ($r[1]) {
                                $errors[$n] = true;
                            }
                            break;
                        case 'number':
                            if ($this->notNull($v) && (!is_numeric($v) || (isset($r[2]) && intval($v) < (is_int($r[2]) ? $r[2] : $vars[$r[2]])) || (isset($r[3]) && intval($v) > (is_int($r[3]) ? $r[3] : $vars[$r[3]]))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = intval($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'float':
                            if ($this->notNull($v) && !preg_match("/^\d+([\.,]\d+)?$/", Filter::mb_trim($v)))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = floatval(Filter::replaceFloat($v));
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'activity':
                            if ($this->notNull($v) && (!is_numeric($v) || !(new ActivityModel())->getByID(intval($v))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = intval($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'country':
                            if ($this->notNull($v) && (!is_numeric($v) || !(new CountryModel())->issetCountry(intval($v))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = intval($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'city':
                            if ($this->notNull($v) && (!is_numeric($v) || !($city = (new CityModel())->issetCity(intval($v))) || (isset($r[2], $vars[$r[2]]) && $city->country_id != $vars[$r[2]])))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = intval($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'language':
                            if ($this->notNull($v) && (!is_numeric($v) || !Lang::get()->getLangById(intval($v))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = intval($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'user':
                            if ($this->notNull($v) && (!is_numeric($v) || !UserModel::get()->issetUser(intval($v))))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = intval($v);
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        case 'oneOf':
                            if ($this->notNull($v) && !in_array($v, $r[2]))
                                $errors[$n] = true;
                            elseif ($this->notNull($v))
                                $vars[$n] = $v;
                            elseif ($r[1])
                                $errors[$n] = true;
                            break;
                        /**
                         * @since 1.2
                         */
                        case 'morph':
                            if ($this->notNull($v) && !preg_match("/^((\(+|[\!\~])*(N|ADJ|V|ADV|NUM|PN|CNJ|POST|INTRJ|MOD|IMIT|PART|PROPNAME|KORAN|NOM|GEN|DIR|DIR_LIM|ACC|ABL|LOC|SG|PL|ACT|PASS|REFL|CAUS|RECP|POSS_1SG|POSS_1PL|POSS_2SG|POSS_2PL|POSS_3SG|POSS_3PL|PRES|PST_DEF|PST_INDF|FUT_DEF|FUT_INDF|FUT_INDF_NEG|1SG|1PL|2SG|2PL|3SG|3PL|ADVV_ACC|ADVV_NEG|ADVV_ANT|ADVV_SUCC|ATTR_MUN|ATTR_ABES|ATTR_LOC|ATTR_GEN|VN_1|VN_2|COND|OBL|PSBL|DESID|PREM|DIM|AFC|PROF|NMLZ|MSRE|DISTR|NUM_COLL|NUM_ORD|NUM_DISR|NUM_APPR|HOR_SG|HOR_PL|IMP_SG|IMP_PL|JUS_SG|JUS_PL|PREC_1|PREC_2|INT|INT_MIR|PROB|SIM_1|SIM_2|SIM_3|INF_1|INF_2|PCP_PR|PCP_PS|PCP_FUT|USIT|NEG|RAR_1|RAR_2|PRO1_SING|PRO1_PLU|PRO2_SING|PRO2_PLU|PRO3_SING|PRO3_PLU|COMP|ADV_COMP|PARTIC|3PS_PLU|CASE_ACC|CASE_DIR|CASE_LOC|3POSS_SING)(\)+|[,\|])?)+$/i", $v)) {
                                $errors[$n] = true;
                            }
                            elseif ($this->notNull($v)) {
                                $vars[$n] = $v;
                            }
                            elseif ($r[1]) {
                                $errors[$n] = true;
                            }
                            break;
                        case 'file':
                            $v = $_FILES[$n];
                            if ($this->notNull($v['tmp_name']) && ($v['error'] !== UPLOAD_ERR_OK || !filesize($v['tmp_name']))) {
                                $errors[$n] = true;
                            }
                            elseif ($this->notNull($v['tmp_name'])) {
                                $vars[$n] = [
                                    'filename'  => $v['name'],
                                    'content'   => file_get_contents($v['tmp_name'])
                                ];
                            }
                            elseif ($r[1]) {
                                $errors[$n] = true;
                            }
                            break;
                        case 'image':
                            $v = $_FILES[$n];
                            if ($this->notNull($v['tmp_name']) && ($v['error'] !== UPLOAD_ERR_OK || !filesize($v['tmp_name'])) && !Image::check($v['tmp_name']))
                                $this->errors[$n] = true;
                            elseif ($this->notNull($v['tmp_name']))
                                $vars[$n] = [
                                    'filename'  => $v['name'],
                                    'tmp_name'  => $v['tmp_name']
                                ];
                            elseif ($r[1])
                                $this->errors[$n] = true;
                            break;
                        case 'any':
                            if ($this->notNull($v)) {
                                $vars[$n] = $v;
                            }
                            elseif ($r[1]) {
                                $errors[$n] = true;
                            }
                            break;
                    }
                }
            }
        }
        return !count($errors);
    }
    
    /**
     * Проверяет наличие переменной
     * 
     * @param mixed $v Проверяемая переменная
     * @return boolean Наличие переменной
     */
    private function thereIs($v) {
        return $v !== null;
    }
    
    /**
     * Проверяет переменную на пустоту
     * 
     * @param mixed $v Проверяемая переменная
     * @return boolean Пустота переменной (null или пустая строка)
     */
    private function notNull($v) {
        return $this->thereIs($v) && !$this->lt($v, 1);
    }
    
    /**
     * Проверяет длину строки
     * 
     * @param string $v Проверяемая строка
     * @param integer $t Максимальная длина
     * @return boolean TRUE, если длина строки меньше $t, иначе FALSE
     */
    private function lt($v, $t) {
        return mb_strlen(Filter::mb_trim($v))<$t;
    }
    
}