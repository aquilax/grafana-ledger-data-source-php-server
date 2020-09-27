<?php

$config = [
    'ledgerCommandPath' => '/usr/bin/ledger',
    'ledgerFile' => getenv('LEDGER_FILE'),
];

function writeResponse($response, $code = 200) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

function getCommand($config, $params) {
    return implode(' ', array_merge([
        $config['ledgerCommandPath'],
        '-f',
        escapeshellarg($config['ledgerFile']),
    ], array_map('escapeshellarg', $params)));
}

function runAndReturn($command) {
    return shell_exec($command);
}

function searchHandler($config) {
    $command = getCommand($config, ['accounts']);
    $output = runAndReturn($command);
    $lines = explode("\n", $output);
    return writeResponse($lines);
}

function parseData($output) {
    $result = [];
    $lines = explode("\n", $output);
    foreach($lines as $line) {
        if ($line){
            $result[] = str_getcsv($line, ',');
        }
    }
    return $result;
}

function getDatapoints($dataRows) {
    $result = [];
    foreach($dataRows as $row) {
        $result[] = [floatval($row[5]), strtotime($row[0]) * 1000];
    }
    return $result;
}

function queryHandler($config) {
    $requestRaw = file_get_contents('php://input');
    $request = json_decode($requestRaw, true);
    $response = [];
    $from = strtotime($request['range']['from']);
    $to = strtotime($request['range']['to']);
    foreach ($request['targets'] as $target) {
        $account = $target['target'];
        $command = getCommand($config, [
            '-b',
            date('Y/m/d', $from),
            '-e',
            date('Y/m/d', $to),
            'csv',
            $account,
        ]);
        $output = runAndReturn($command);
        $response[] = [
            'target' => $account,
            'datapoints' => getDatapoints(parseData($output)),
        ];
    }
    return writeResponse($response);
}

function defaultHandler() {
    return writeResponse(['data' => 'OK']);
}


$route = $_SERVER["REQUEST_URI"];
$method = $_SERVER['REQUEST_METHOD'];

error_log($method .' ' .$route);

if ($method === 'OPTIONS') {
    return defaultHandler();
}

switch($route) {
    case '/search': return searchHandler($config);
    case '/query': return queryHandler($config);
    default: return defaultHandler();
}
