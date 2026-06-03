<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);


$projectPath = __DIR__ . '/..';


$autoloader = require $projectPath . '/vendor/autoload.php';


$app = require_once $projectPath . '/bootstrap/app.php';


$app->useStoragePath($projectPath . '/storage');


$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    $response->send();
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
    echo "\nFile: " . $e->getFile();
    echo "\nLine: " . $e->getLine();
}