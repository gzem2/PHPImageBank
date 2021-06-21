<?php

declare(strict_types=1);

namespace PHPImageBank\Controllers;

use PHPImageBank\App\Controller;
use PHPImageBank\App\Router;
use PHPImageBank\Models\Comment;

/**
 * Controller for comments
 */
class CommentController extends Controller
{
    /**
     * Create new comment or show error if not authorized
     * @param array $data comment table row
     */
    public function create(array $data)
    {
        if (isset($_SESSION['logged_in'])) {
            echo var_dump($data) . "<br>";
            $model = Comment::fromRow($data);
            $model->setFieldValue("poster_id", $_SESSION['logged_in']->id);
            echo var_dump($model) . "<br>";

            if (!$model->save()) {
                return $this->error("Comment creation failed", 400);
            }
            Router::redirect("/images/" . $data["image_id"]);
        } else {
            return $this->error("Not authorized", 401);
        }
    }

    /**
     * Update existing comment or show error if user is not authorized/comment doesnt exist
     * @param array $data comment table row
     */
    public function update(array $data)
    {
        if (isset($_SESSION['logged_in'])) {
            $comment = Comment::getByField("id", $data["id"])->one();
            if ($comment) {
                if ($comment->poster_id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
                    return $this->create($data);
                } else {
                    return $this->error("Not authorized", 401);
                }
            } else {
                return $this->error("Comment does not exist", 400);
            }
        } else {
            return $this->error("Not authorized", 401);
        }
    }

    /**
     * Delete comment by id or show error if not authorized
     * @param int $id comment id
     */
    public function delete(int $id)
    {
        if (!isset($_SESSION['logged_in'])) {
            return $this->error("User not logged in", 401);
        }
        $comment = Comment::getByField('id', $id)->one();
        if ($comment->id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
            Comment::deleteByField("id", $id);
        } else {
            return $this->error("Not authorized", 401);
        }
        Router::redirect("/");
    }
}
