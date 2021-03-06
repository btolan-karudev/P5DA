<?php

namespace app;

use app\controllers\AdminController;
use app\controllers\BaseController;
use app\controllers\CommentController;
use app\controllers\PostController;
use app\controllers\SecurityController;
use app\controllers\UserController;
use app\services\Auth;
use app\services\Redirect;
use app\services\Request;
use app\services\Session;


class Router
{
    private $request;

    /**
     * Router constructor.
     *
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->action = $this->getAction();
    }

    const ROUTES = [
        [
            '' => [BaseController::class, 'home'],
            'showRegisterForm' => [BaseController::class],
            'contact' => [BaseController::class],
            'sendMail' => [BaseController::class],
            'registerUser' => [UserController::class],
            'login' => [SecurityController::class],
            'showLogoutMessage' => [SecurityController::class],
            'forgotPass' => [SecurityController::class],
            'requestReset' => [SecurityController::class],
            'resetPass' => [SecurityController::class],
            'resetPassword' => [SecurityController::class],
            'indexAction' => [AdminController::class],
            'posts' => [PostController::class],
            'showPost' => [PostController::class],
            'unapprouve' => [CommentController::class],
            'showLoginForm' => [BaseController::class]
        ],
        [
            'profile' => [UserController::class],
            'logout' => [SecurityController::class],
            'editProfile' => [UserController::class],
            'addComment' => [PostController::class],

        ],
        [
            'adminPosts' => [AdminController::class],
            'addPost' => [AdminController::class],
            'editPost' => [AdminController::class],
            'deletePost' => [AdminController::class],
        ],
        [
            'grantRoleAdmin' => [AdminController::class],
            'superAdminView' => [AdminController::class],
            'deleteUser' => [AdminController::class],
            'adminComments' => [AdminController::class],
            'deleteComment' => [AdminController::class],
            'approuve' => [AdminController::class],
        ]
    ];

    /**
     * render Controller
     */
    public function renderController()
    {
        foreach ($this->getAllowedRoutes() as $levelRoutes) {
            foreach ($levelRoutes as $method => $controllers) {
                $methodName = $controllers[1] ?? $method;

                if ($this->action !== $methodName) {
                    continue;
                }

                $controller = new $controllers[0]();

                return $controller->$methodName();
            }
        }

        if (Auth::isLogged()) {
            Session::addMessage('Cette page vous est inaccessible', 'info');

            return Redirect::to('home');
        }

        return Auth::requireLogin();
    }

    /**
     * @return array
     */
    public function getAllowedRoutes(): array
    {
        $roleByLevel = [
            'visitor' => 0,
            'member' => 1,
            'admin' => 2,
            'superuser' => 3
        ];

        $user = Auth::getUser();

        if ($user) {
            $roleUser = $user->role;
        }

        $role = $roleByLevel[$roleUser ?? $roleUser = 'visitor']; // 0, 1, 2

        $allowedRoutes = [];
        foreach (self::ROUTES as $key => $routes) {
            if ($role < $key) {
                continue;
            }

            $allowedRoutes[] = $routes;
        }

        return $allowedRoutes;
    }

    public function getAction()
    {

        if (isset($_GET['action'])) {
            return $_GET['action'];
        }

        return 'home';
    }

}
