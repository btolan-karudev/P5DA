<?php
session_start();

//Load helpers, librairies and controllers etc
require_once '../vendor/autoload.php';
require_once 'config/config.php';
require_once 'helpers/session_helper.php';
require_once('libraries/Database.php');
require_once('controllers/AbstractController.php');
require_once('controllers/BaseController.php');
require_once('controllers/AdminController.php');

class RouterNew
{

    //------------------------------------------------------------------------------------------------------------------
    private $request;
    private $error;
    //------------------------------------------------------------------------------------------------------------------
    public function __construct($request)
    {
        $this->request = $request;
    }
    //------------------------------------------------------------------------------------------------------------------

    const ROUTES = [
        [
            '' => [BaseController::class, 'home'],
            "home" => [BaseController::class],
            "contact" => [BaseController::class],
            "chapters" => [BaseController::class],
            "showChapter" => [BaseController::class],
            "editComment" => [BaseController::class],
            "bio" => [BaseController::class],
            "unapprouve" => [BaseController::class],
            "adminLogin" => [BaseController::class],
            "sendMail" => [BaseController::class],
        ],
        [
            "adminView" => [AdminController::class],
            "adminComments" => [AdminController::class],
            "approuve" => [AdminController::class],
            "adminChapters" => [AdminController::class],
            "addChapter" => [AdminController::class],
            "editChapter" => [AdminController::class],
            "deleteChapter" => [AdminController::class],
            "deleteComment" => [AdminController::class],
            "logout" => [AdminController::class],

        ],
        [
            //"adminView" => [AdminController::class], superadmin
        ]

    ];



    //------------------------------------------------------------------------------------------------------------------
    public function renderController()
    {
        $request = $this->request;

        $routes = $this->getAllowedRoutes();
        $_SESSION['role'] = "member";
        //var_dump($routes);die;

        foreach ($routes as $levelRoutes) {
            foreach ($levelRoutes as $method => $controllers) {
                $methodName = $controllers[1] ?? $method;
                $controller = new $controllers[0]();

                return $controller->$methodName();
            }
        }
    }

    //------------------------------------------------------------------------------------------------------------------
    public function isLoggedIn()
    {
        if (isset($_SESSION['admin_id'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllowedRoutes(): array
    {
        $roleByLevel = [
            'visitor' => 0,
            'member' => 1,
            'admin' => 2,
        ];

        $role = $roleByLevel[$_SESSION['role'] ?? 'visitor']; // 0, 1, 2

        $allowedRoutes = [];
        foreach (self::ROUTES as $key => $routes) {
            if ($role < $key) {
                continue;
            }

            $allowedRoutes[] = $routes;
        }

        return $allowedRoutes;
    }
    //------------------------------------------------------------------------------------------------------------------
}