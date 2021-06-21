<?php

declare(strict_types=1);

namespace PHPImageBank\Controllers;

use PHPImageBank\App\Controller;
use PHPImageBank\App\Router;
use PHPImageBank\Models\User;

/**
 * Controller for users
 */
class UserController extends Controller
{
    /**
     * Create new user from data
     * @param array $data user table row
     */
    public function create(array $data)
    {
        $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
        $model = User::fromRow($data);

        if (!$model->save()) {
            return $this->error("Registration failed", 400);
        }
        $_SESSION['logged_in'] = $model;
        Router::redirect("/");
    }

    /**
     * Update existing user
     * @param array $data user table row
     * @return view
     */
    public function update(array $data)
    {
        if (isset($_SESSION['logged_in'])) {
            $user = User::getByField("id", $data["id"])->one();
            if ($user) {
                if($user->id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
                    return $this->create($data);
                } else {
                    return $this->error("Not authorized", 401);
                }
            } else {
                return $this->error("User does not exist", 400);
            }
        } else {
            return $this->error("Not authorized", 401);
        }
    }

    /**
     * Delete user by id
     * @param int $id user id
     * @return view
     */
    public function delete(int $id)
    {
        if(!isset($_SESSION['logged_in'])) {
            return $this->error("User not logged in", 401);
        }
        $user = User::getByField('id', $id)->one();
        if($user->id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
            User::deleteByField("id", $id);
        } else {
            return $this->error("Not authorized", 401);
        }
        Router::redirect("/");
    }

    /**
     * Show user by specified user id
     * @param int $id user id
     * @return view
     */
    public function getById(int $id)
    {
        $model = User::getByField("id", $id)->one();
        if (!$model) {
            return $this->error("User not found", 400);
        }
        return $this->view("user-details", [
            'data' => $model,
            'title' => $model->username
        ]);
    }

    /**
     * Signs user in, if correct credentials
     * @param array $data user table row
     * @return view
     */
    public function login(array $data)
    {
        $model = User::getByField("username", $data["username"])->one();
        if (!$model) {
            return $this->error("Incorrect username or password", 400);
        }

        if (password_verify($data["password"], $model->password)) {
            $_SESSION['logged_in'] = $model;
        } else {
            return $this->error("Incorrect username or password", 400);
        }

        Router::redirect("/");
    }

    /**
     * Sign user out
     * @return view
     */
    public function logout()
    {
        if (isset($_SESSION['logged_in'])) {
            unset($_SESSION['logged_in']);
        }
        Router::redirect("/");
    }

    /**
     * Show user register form
     * @return view
     */
    public function register()
    {
        return $this->view("register-form", [
            'title' => 'Register'
        ]);
    }
}
