<?php

// Config
$basepath = '/uptime/';
$datafile = 'db.json';
$minsame = 5;
$maxsame = 100;
$percentok = 50;
$sleep = 120;

// Action
date_default_timezone_set('Europe/Berlin');
$db = array();
$uri = str_replace($basepath, '', $_SERVER['REQUEST_URI']);
$count = rand($minsame, $maxsame);

if(file_exists('data/' .$datafile)) { $db = json_decode(file_get_contents('data/' . $datafile), true); }

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
    else { $db[$uri] = get_error($count); }
}

if(!file_exists('data')) { mkdir('data', 0700); }
file_put_contents('data/' . $datafile, json_encode($db));

writelog($db[$uri]);

if($db[$uri]['code'] == 'time') { sleep($sleep); }
else
{
    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
    header($protocol . ' ' . $db[$uri]['code'] . ' ' . $db[$uri]['description']);
    echo $protocol . ' ' . $db[$uri]['code'] . ' ' . $db[$uri]['description'] . ' (' . $db[$uri]['count'] . ')';
}

exit();

// Returns one random error code, or a 200 if requested
function get_error($count, $ok = NULL)
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

// Writes hit to log
function writelog($response)
{
    $logtext = implode(' ', array(
        'ip'            => $_SERVER['REMOTE_ADDR'],
        'time'          => date('Y-m-d H:i:s'),
        'agent'         => $_SERVER['HTTP_USER_AGENT'],
        'method'        => $_SERVER['REQUEST_METHOD'],
        'uri'           => $_SERVER['REQUEST_URI'],
        'port'          => $_SERVER['SERVER_PORT'],
        'protocol'      => $_SERVER['SERVER_PROTOCOL'],
        'code'          => $response['code'],
        'description'   => $response['description'],
        'count'         => $response['count']
    ));

    $logtext .= "\n";

    if(!file_exists('logs')) { mkdir('logs', 0700); }
    $logfile = date('Y-m-d') . '_uptime_log.txt';
    $log = fopen('logs/' . $logfile, 'a');
    fwrite($log, $logtext);
    fclose($log);

    return true;
}