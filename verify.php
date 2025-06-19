<?php
// verify.php

// where we’ll stick our marker files
$dir = __DIR__ . '/verifications';
if (!file_exists($dir)) {
    mkdir($dir, 0700, true);
}

// sanitize inputs
$vid    = isset($_GET['vid'])     ? preg_replace('/[^a-z0-9]/i','',$_GET['vid']) : '';
$whoami = isset($_GET['whoami']) ? $_GET['whoami'] : '';

if (!$vid) {
    // no vid → bad request
    header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
    exit('Missing vid');
}

$fn = "$dir/$vid.txt";

if ($whoami) {
    // HTA callback: record that this vid has run
    file_put_contents($fn, $whoami);
    header('Content-Type: text/plain');
    echo 'OK';
} else {
    // browser polling: only return 200 once we see the file
    if (file_exists($fn)) {
        header('Content-Type: text/plain');
        echo 'VERIFIED';
    } else {
        // still waiting
        header($_SERVER["SERVER_PROTOCOL"]." 204 No Content");
    }
}
