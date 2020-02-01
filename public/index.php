<?php


use app\Router;

require_once '../app/helpers/session_helper.php';
require_once('../app/libraries/Database.php');
require_once '../vendor/autoload.php';
require_once '../app/config/config.php';


if($_GET)
{
    $request = $_GET['action'];
}
else
{
    $request = '';
}


$routeur = new Router($request);
$routeur->renderController();



