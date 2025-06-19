<?php
if (!function_exists('colour_status')) {
    function colour_status($code, $text) {
        $green  = "\033[1;32m";
        $red    = "\033[1;31m";
        $yellow = "\033[1;33m";
        $reset  = "\033[0m";

        if ($code >= 200 && $code < 300) {
            $colour = $green;
        } elseif ($code >= 400 && $code < 500) {
            $colour = $red;
        } else {
            $colour = $yellow;
        }

        return $colour . $text . $reset;
    }
}

if (!function_exists('log_request')) {
    function log_request($status_code) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $port = $_SERVER['REMOTE_PORT'];
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        $status_line = "$ip:$port [$status_code]: $method $uri";
        $coloured = colour_status($status_code, $status_line);
        error_log($coloured);

        $stderr = fopen('php://stderr', 'w');
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            fwrite($stderr, "                 HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n");
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            fwrite($stderr, "                 HTTP_X_FORWARDED_FOR: " . $_SERVER['HTTP_X_FORWARDED_FOR'] . "\n");
        }
        fclose($stderr);
    }
}

$request_uri = $_SERVER['REQUEST_URI'];
$path = $_SERVER['DOCUMENT_ROOT'] . parse_url($request_uri, PHP_URL_PATH);

if (file_exists($path) && !is_dir($path)) {
    log_request(200);
    return false;
}

if ($request_uri === '/' || is_dir($path)) {
    log_request(200);
    echo '<h1>Directory Listing</h1>';
    echo '<ul>';
    foreach (scandir('.') as $file) {
        if ($file !== '.' && $file !== '..') {
            echo '<li><a href="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</a></li>';
        }
    }
    echo '</ul>';
    exit;
}

http_response_code(404);
log_request(404);
echo '<h1>404 Not Found</h1>';
