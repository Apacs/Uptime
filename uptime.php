<?php

//Config

$basepath = '/uptime/';
$filename = 'db.json';
$minsame = 5;
$maxsame = 50;
$percentok = 50;
$sleep = 60;

// Action

$db = array();
$uri = str_replace($basepath, '', $_SERVER['REQUEST_URI']);
$count = rand($minsame, $maxsame);

if(file_exists($filename)) { $db = json_decode(file_get_contents($filename), true); }

if(array_key_exists($uri, $db))
{
    if($db[$uri]['count'] > 1) { $db[$uri]['count']--; }
    else
    {
        if($db[$uri]['code'] == '200') { unset($db[$uri]); }
        else { $db[$uri] = get_error($count, $ok = '200'); }
    }
}

if(!array_key_exists($uri, $db))
{
    if(rand(1, 100) <= $percentok) { $db[$uri] = get_error($count, $ok = '200'); }
    else { $db[$uri] = get_error($count, $ok = NULL); }
}

file_put_contents($filename, json_encode($db));

if($db[$uri]['code'] == 'time') { sleep($sleep); }
else
{
    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

    header($protocol . ' ' . $db[$uri]['code'] . ' ' . $db[$uri]['description']);

    echo $protocol . ' ' . $db[$uri]['code'] . ' ' . $db[$uri]['description'] . ' (' . $db[$uri]['count'] . ')';
}

exit();

// Returns one random error code, or a 200 if requested

function get_error($count, $ok)
{
    if($ok == '200')
    {
        $errorcode = array(
            'code'          => '200',
            'description'   => 'Ok',
            'count'         => $count
        );
    }
    else
    {
        $codes = array(
            '400'   => 'Bad Request',
            '401'   => 'Unauthorized',
            '402'   => 'Payment Required',
            '403'   => 'Forbidden',
            '404'   => 'Not Found',
            '405'   => 'Method Not Allowed',
            '406'   => 'Not Acceptable',
            '407'   => 'Proxy Authentication Required',
            '408'   => 'Request Time-out',
            '409'   => 'Conflict',
            '410'   => 'Gone',
            '411'   => 'Length Required',
            '412'   => 'Precondition Failed',
            '413'   => 'Request Entity Too Large',
            '414'   => 'Request-URI Too Large',
            '415'   => 'Unsupported Media Type',
            '500'   => 'Internal Server Error',
            '501'   => 'Not Implemented',
            '502'   => 'Bad Gateway',
            '503'   => 'Service Unavailable',
            '504'   => 'Gateway Time-out',
            '505'   => 'HTTP Version not supported',
            'time'  => 'Timeout'
        );

        $key = array_rand($codes, 1);

        $errorcode = array(
            'code'          => $key,
            'description'   => $codes[$key],
            'count'         => $count
        );
    }

    return $errorcode;
}