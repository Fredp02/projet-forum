<?php
session_start();
//on génère un nouvel id de session
session_regenerate_id();

define("URL", str_replace("index.php", "", (isset($_SERVER['HTTPS']) ? "https" : "http") .
  "://" . $_SERVER['HTTP_HOST'] . $_SERVER["PHP_SELF"]));


require '../vendor/autoload.php';


use Controllers\Services\Securite;
use Core\Router;



if (empty($_SESSION['tokenCSRF'])) {
  Securite::tokenCSRF();
}

$route = new Router();
$route->routes();
