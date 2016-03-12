<?php

use Mail\Model\Mail;

chdir(dirname(__DIR__));

$app = include 'application/start.php';

$db = app('db');
$db instanceof Micro\Database\Adapter\AdapterAbstract;

sendMail('имало едно време');
die;

$result = $db->fetchAll("SELECT * FROM Brands WHERE reNewDate IS NOT NULL ORDER BY reNewDate ASC");

define('NL', PHP_SAPI === 'cli' ? "\n" : "<br />");

$config = array(
    array('d' => -5, 'm' => 0, 'y' => 0),
    array('d' => -10, 'm' => 0, 'y' => 0),
    array('d' => -15, 'm' => 0, 'y' => 0),
    array('d' => 0, 'm' => -1, 'y' => 0),
    array('d' => 0, 'm' => -2, 'y' => 0),
    array('d' => 0, 'm' => -3, 'y' => 0)
);

foreach ($config as $c) {
    echo 'Day: ' . $c['d'] . ', Month: '  . $c['m'] . ', Year: ' . $c['y'] . NL;
    echo str_repeat('.', 100) . NL;
    $text = checkBrand($c);
    if ($text) {
        try {
            sendMail($text);
            echo 'Mail sended: ' . $text . NL;
        } catch (\Exception $e) {
            echo 'ERROR: ' . $e->getMessage() . NL;
        }
    }
    echo NL;
}

function checkBrand($c)
{
    global $result;

    $text = '';

    $now = date('Y-m-d');

    foreach($result as $set) {

        $date = new \DateTime($set['reNewDate']);

        $y = $date->format('Y');
        $m = $date->format('m');
        $d = $date->format('d');

        $expire = date('Y-m-d', mktime(0, 0, 0, $m + $c['m'], $d + $c['d'], $y + $c['y']));

        echo $set['id'] . ', renew: ' . $date->format('Y-m-d') . ', expire: ' . $expire . ', now: ' . $now . NL;

        if ($expire == $now) {
            $text .=  'Brand' . ': '. $set['name'] . " expires on " . $set['reNewDate'] . "\n";
        }
    }

    return $text;
}

function sendMail($text)
{
    $mail = new Mail();

    $to = array(
        'vchavdarov@icygen.com'
    );

    $mail->send('Изтичащи марки', $text, $to, 'vchavdarov@icygen.com');

    /*
     $mail->addTo('k.taneva78@gmail.com');
     $mail->addTo('tousheto@gmail.com');
     $mail->addTo('359899997111@sms.mtel.net');
     $mail->addTo('359888701404@sms.mtel.net');
     */
}