<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods GET, OPTIONS')

// Setup app.
require_once realpath(__DIR__) . '/../config/init.php';
require_once realpath(__DIR__) . '/../config/dependencies.php';

/** Load routes **/
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath(__DIR__) . '/../routes'));
$routes = array_keys(array_filter(iterator_to_array($iterator), function($file) {
    return $file->isFile();
}));
foreach ($routes as $route) {
    if (strpos($route, '.php') !== false) {
        require_once(realpath($route));
    }
}
/***/

$app->run();
