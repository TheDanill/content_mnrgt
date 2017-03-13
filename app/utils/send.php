<?php
$message="";
function sendMessage($to, $subject, $message, $from = false){
    require '../vendor/phpmailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = "smtp.yandex.ru";
    $mail->SMTPAuth = true;
    $mail->Username = "noreply@m-artkzn.ru";
    $mail->Password = "4jHtOL2wfG";
    $mail->SMTPSecure = "ssl";
    $mail->Port = 465;
    
    if (!$from)
        $mail->From = 'noreply@m-artkzn.ru';
    else
        $mail->From = $from;
    $mail->FromName = "Magenta Art";
    $to = preg_split("/,\s*/", $to);
    foreach ($to as $t) {
        $mail->addAddress($t);
    }
    $mail->CharSet = 'utf-8';
    $mail->Subject = $subject;
    $mail->Body = $message;
    
    return $mail->send();
}
function v($v, $n=0, $r=1){
    if ($n==0) return trim(htmlspecialchars($_POST[$v], ENT_QUOTES)).($r ? "\n" : "");
    else return trim(htmlspecialchars($_POST[$v][$n], ENT_QUOTES)).($r ? "\n" : "");
}
function t($v, $n=0){
    if ($n==0) return trim($_POST[$v])!='';
    else return trim($_POST[$v][$n])!='';
}
function tr($v, $n=0){
    if ($n==0) return trim($_POST[$v]);
    else return trim($_POST[$v][$n]);
}
function n($v, $n=0){
    if ($n==0) return is_numeric($_POST[$v]);
    else return is_numeric($_POST[$v][$n]);
}
function i($v, $n=0){
    if ($n==0) return isset($_POST[$v]);
    else return isset($_POST[$v][$n]);
}
if (t('name')){
    $message .= "Заявку отправил: ".v('name');
}
if (t('email') && preg_match('/^[a-z0-9_\-\.]{1,}@[a-z0-9\-\.]{1,}\.[a-z]{2,4}$/i', tr('email'))) {
    $message .= "E-mail: " . v('email');
}
if (t('message')) {
    $message .= "Сообщение: " . v('message');
}
$message.="\n";
if (trim($message) && sendMessage("zelderon@mail.ru", "Feedback с сайта MyPop.Top", $message)){
    echo "ok";
}
else {
    echo "error";
}
