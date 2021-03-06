<?php

namespace app\controllers;

use app\models\User;
use app\services\Auth;
use app\services\CSRFToken;
use app\services\Redirect;
use app\services\Request;
use app\services\Session;
use app\services\UploadFile;
use app\services\ValidateRequest;
use app\services\View;


class UserController
{

    /**
     * @var User
     */
    private $userModel;


    public function __construct()
    {
        $this->userModel = new User();
    }


    public function registerUser()
    {
        if (false === Request::has('post')) {
            View::renderTemplate('User/register.html.twig', []);
            return false;
        }

        $request = Request::get('post');

        if (false === CSRFToken::verifyCSRFToken($request->token, false)) {
            throw new \Exception('Token incorect');
        }

        $validate = new ValidateRequest();
        $validate->abide(
            $_POST, [
                'Nom' => ['required' => true, 'minLength' => 3, 'maxLength' => 20],
                'Prénom' => ['required' => true, 'minLength' => 3],
                'email' => ['required' => true, 'uniqueEmail' => true, 'email' => 6],
                'MotDePasse' => ['required' => true]
            ]
        );

        if ($validate->hasError()) {
            $errors = $validate->getErrorMessages();

            View::renderTemplate(
                'User/register.html.twig', [
                    'errors' => $errors
                ]
            );
            return false;
        }

        $request = Request::get('post');

        $data = [
            'last_name' => trim($request->Nom),
            'first_name' => trim($request->Prénom),
            'password' => password_hash($request->MotDePasse, PASSWORD_BCRYPT),
            'email' => trim($request->email),
            'role' => 'member'
        ];

        $this->userModel->addUser($data);

        View::renderTemplate(
            'User/login.html.twig', [
                'success' => 'Inscription faite avec succèss, veuilliez vous connectez',
            ]
        );

        return true;
    }

    public function profile()
    {
        Auth::requireLogin();

        $user = Auth::getUser();

        return View::renderTemplate('User/profile.html.twig', ['user' => $user]);

    }

    public function editProfile()
    {
        Auth::requireLogin();

        $user = Auth::getUser();

        if (false === Request::has('post')) {
            View::renderTemplate('User/edit.profile.html.twig', ['user' => $user]);
            return false;
        }

        $request = Request::get('post');

        if (false === CSRFToken::verifyCSRFToken($request->token, false)) {
            throw new \Exception('Token incorect');
        }

        $rules = [
            'txtLastName' => ['required' => true, 'minLength' => 3],
            'txtFirstName' => ['required' => true, 'minLength' => 3],
            'txtEmail' => ['required' => true, 'uniqueEmail' => true, 'minLength' => 3],
        ];

        $validate = new ValidateRequest();
        $validate->abide($_POST, $rules);

        if ($validate->hasError()) {
            $errors = $validate->getErrorMessages();

            View::renderTemplate(
                'User/edit.profile.html.twig', [
                    'errors' => $errors,
                    'user' => $user
                ]
            );
            return false;
        }

        if ($this->updateProfile()) {
            Request::refresh();
            View::renderTemplate(
                'User/profile.html.twig', [
                    'success' => 'Informations éditées avec succès',
                    'user' => Auth::getUser()
                ]
            );
            return true;
        }

        throw new \Exception('Un error est sourvenu, veuilez essayer plus tard');


    }

    public function updateProfile()
    {
        $user = Auth::getUser();

        $file = Request::get('file');
        $filename = $file->userImage->name;

        if (!empty($filename) and !UploadFile::isImage($filename)) {
            Session::addMessage('Le format de votre fichier est non conforme. Veuillez resseyer!');
            return Redirect::to('editProfile');
        }

        if (!empty($filename)) {
            $ds = DIRECTORY_SEPARATOR;
            $temp_file = $file->userImage->tmp_name;
            $user_photo_path = UploadFile::move($temp_file, "uploads{$ds}users", $filename)->path();
        }

        $request = Request::get('post');

        $data = [
            'last_name' => trim($request->txtLastName),
            'first_name' => trim($request->txtFirstName),
            'password' => $request->password != null ? password_hash($request->password, PASSWORD_BCRYPT) : $user->password,
            'email' => trim($request->txtEmail),
            'role' => $user->role,
            'user_photo_path' => !empty($filename) ? $user_photo_path : $user->user_photo_path,
            'id' => $user->id
        ];

        return $this->userModel->updateUser($data);

    }

}
