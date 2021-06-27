<?php

namespace PHPImageBank\Routes;

use PHPImageBank\App\Router;
use PHPImageBank\Controllers\ImageController;
use PHPImageBank\Controllers\UserController;
use PHPImageBank\Controllers\TagController;
use PHPImageBank\Controllers\CommentController;

/*
Router::add('/', function () {
    Router::redirect('/images');
});
*/

Router::get('/', [ImageController::class, 'index']);

Router::post('/images', [ImageController::class, 'create']);

Router::get('/images/([0-9]*)', [ImageController::class, 'getById']);

Router::get('/upload', [ImageController::class, 'upload']);

Router::get('/images/delete/([0-9]*)', [ImageController::class, 'delete']);

Router::get('/images/edit/([0-9]*)', [ImageController::class, 'edit']);

Router::post('/images/update', [ImageController::class, 'update']);


Router::post('/users', [UserController::class, 'create']);

Router::put('/users', [UserController::class, 'update']);

Router::get('/users/([0-9]*)', [UserController::class, 'getById']);

Router::post('/login', [UserController::class, 'login']);

Router::get('/logout', [UserController::class, 'logout']);

Router::get('/register', [UserController::class, 'register']);

Router::get('/users/delete/([0-9]*)', [UserController::class, 'delete']);


Router::get('/my-tags', [TagController::class, 'myTags']);

Router::post('/tags', [TagController::class, 'create']);

Router::put('/tags', [TagController::class, 'update']);

Router::get('/tags/([0-9]*)', [TagController::class, 'getById']);

Router::get('/tags/delete/([0-9]*)', [TagController::class, 'delete']);


Router::post('/comments', [CommentController::class, 'create']);

Router::put('/comments', [CommentController::class, 'update']);

Router::get('/comments/delete/([0-9]*)', [CommentController::class, 'delete']);