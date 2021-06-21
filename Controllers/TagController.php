<?php

declare(strict_types=1);

namespace PHPImageBank\Controllers;

use PHPImageBank\App\Controller;
use PHPImageBank\App\Router;
use PHPImageBank\Models\User;
use PHPImageBank\Models\Tag;

/**
 * Controller for tags
 */
class TagController extends Controller
{
    /**
     * Create new tag
     * @param array $data tag table row
     * @return view
     */
    public function create(array $data)
    {
        if (isset($_SESSION['logged_in'])) {
            $model = Tag::fromRow($data);
            $model->setFieldValue("creator_id", $_SESSION['logged_in']->id);

            if (!$model->save()) {
                return $this->error("Tag creation failed", 400);
            }
            Router::redirect("/my-tags");
        } else {
            return $this->error("Not authorized", 401);
        }
    }

    /**
     * Update existing tag
     * @param array $data tag table row
     * @return view
     */
    public function update(array $data)
    {
        if (isset($_SESSION['logged_in'])) {
            $tag = Tag::getByField("id", $data["id"])->one();
            if ($tag) {
                if ($tag->creator_id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
                    return $this->create($data);
                } else {
                    return $this->error("Not authorized", 401);
                }
            } else {
                return $this->error("Tag does not exist", 400);
            }
        } else {
            return $this->error("Not authorized", 401);
        }
    }

    /**
     * Delete tag by id
     * @param int $id tag id
     * @return view
     */
    public function delete(int $id)
    {
        if (!isset($_SESSION['logged_in'])) {
            return $this->error("User not logged in", 401);
        }
        $tag = Tag::getByField('id', $id)->one();
        if ($tag->id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
            Tag::deleteByField("id", $id);
        } else {
            return $this->error("Not authorized", 401);
        }
        Router::redirect("/my-tags");
    }


    /**
     * Show tags created by user or error if user is not authorized
     * @return view
     */
    public function myTags()
    {
        if (!isset($_SESSION['logged_in'])) {
            return $this->error("User not logged in", 401);
        }

        return $this->getTagsByCreatorId(intval($_SESSION['logged_in']->id));
    }

    /** 
     * Show list of tags created by specified user id
     * @param int $id user id
     * @return view
     */
    public function getTagsByCreatorId(int $id)
    {

        $models = Tag::getByField("creator_id", $id);
        $user = User::getByField("id", $id)->one();
        if (empty($models->data())) {
            return $this->view("tag-list", [
                'data' => $models->data(),
                'title' => $user->username . " tags"
            ]);
        } else {
            return $this->view("tag-list", [
                'data' => $models->data(),
                'title' => $user->username . " tags"
            ]);
        }
    }

    /**
     * Show tag by specified tag id
     * @param int $id tag id
     * @return view
     */
    public function getById(int $id)
    {
        $model = Tag::getByField("id", $id)->one();
        if (!$model) {
            return $this->error("Tag not found", 400);
        }
        return $this->view("tag-details", [
            'data' => $model,
            'title' => $model["tagname"]
        ]);
    }
}
