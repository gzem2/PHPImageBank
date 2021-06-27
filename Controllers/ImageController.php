<?php

declare(strict_types=1);

namespace PHPImageBank\Controllers;

use PHPImageBank\App\Controller;
use PHPImageBank\App\Router;
use PHPImageBank\Models\Image;
use PHPImageBank\Models\User;
use PHPImageBank\Models\Tag;
use PHPImageBank\Models\ImageTag;
use PHPImageBank\Models\Comment;

/**
 * Controller for images
 */
class ImageController extends Controller
{
    public int $imagesPerPage = 42; /**< images to be displayed per page */

    /**
     * Display a page with images.
     * @return view
     */
    public function index()
    {
        /**
         * Get image data
         */
        if (isset($_GET['tags'])) {
            $taglist = explode(" ", $_GET['tags']);
            $imgcount = ImageTag::getCountForTags($taglist);
            $order = ["column" => "image_id", "sort" => "DESC", "last" => false];
        } else {
            $imgcount = Image::getCount();
            $order = ["column" => "id", "sort" => "DESC", "last" => false];
        }

        if (isset($_GET['pid'])) {
            $offset = $imgcount - ($this->imagesPerPage + $_GET['pid']);
        } else {
            $offset = $imgcount - $this->imagesPerPage;
        }
        if ($offset < 0) {
            $lastpage = $this->imagesPerPage + $offset;
            $offset = 0;
            $order["sort"] = "ASC";
            $order["last"] = true;
        }

        if (isset($_GET['tags'])) {
            $data = ImageTag::getImgsForTags($taglist, $lastpage ?? $this->imagesPerPage, $offset, $order)->data();
        } else {
            $data = Image::getAll($lastpage ?? $this->imagesPerPage, $offset, $order)->data();
        }

        /**
         * Get tags for sidebar
         */
        $imgtags = [];
        foreach ($data as $d) {
            $itags = ImageTag::getByField("image_id", $d->id)->data();
            if ($itags) {
                foreach ($itags as $itag)
                    if (!isset($imgtags[$itag->tag_id])) {
                        $t = Tag::getByField("id", $itag->tag_id)->one();
                        if ($t) {
                            $imgtags[$itag->tag_id] = [1, $t->tagname];
                        }
                    } else {
                        $imgtags[$itag->tag_id][0]++;
                    }
            }
        }

        /**
         * Create pagination
         */
        if (isset($_GET['tags'])) {
            $pages = $this->makePagination($imgcount, $taglist);
        } else {
            $pages = $this->makePagination($imgcount);
        }

        return $this->view("image-list", [
            'data' => $data,
            'tags' => $imgtags,
            'pages' => $pages,
            'title' => 'Gallery'
        ]);
    }

    /**
     * Create data for pagination page element
     * @param int $imgcount total images for tag/tags
     * @param array $taglist array of tags
     * @return array of pagination data
     */
    public function makePagination($imgcount, $taglist = null)
    {
        if ($taglist) {
            $linkprefix = "/?tags=" . implode("+", $taglist) . "&pid=";
        } else {
            $linkprefix = "/?pid=";
        }
        $pages = ["pages" => []];
        $offset = 0;
        $pid = 0;
        if (isset($_GET['pid'])) {
            $pid = $_GET['pid'];
            $offset = $_GET['pid'];
        }
        $maxcount = $this->imagesPerPage * 10;
        if ($maxcount > $imgcount) $maxcount = $imgcount;

        for ($offset; $offset < $maxcount; $offset = $offset + $this->imagesPerPage) {
            $pages["pages"][] = ["link" => $linkprefix . $offset, "text" => $offset / $this->imagesPerPage] + ($offset == $pid ? ["active" => 1] : []);
            if ($offset == $pid) {
                if ($offset - $this->imagesPerPage > -1) {
                    $pages["prev"] = $linkprefix . ($offset - $this->imagesPerPage);
                } else {
                    $pages["prev"] = "#";
                }
                if ($offset + $this->imagesPerPage < $maxcount + 1) {
                    $pages["next"] = $linkprefix . ($offset + $this->imagesPerPage);
                } else {
                    $pages["next"] = "#";
                }
            }
        }
        if (count($pages) < 10 && isset($_GET['pid'])) {
            $offset = $_GET['pid'];
            for ($offset; $offset > 0; $offset = $offset - $this->imagesPerPage) {
                array_unshift($pages["pages"], ["link" => "/?pid=" . ($offset - $this->imagesPerPage), "text" => ($offset - $this->imagesPerPage) / $this->imagesPerPage]);
            }
        }

        return $pages;
    }

    /**
     * Create thumbnail
     * @param string $src name of full size image
     * @param string $dest name of thumbnail
     * @param int $desired_width thumbnail width
     */
    public function makeThumb($src, $dest, $desired_width)
    {
        $source_image = imagecreatefromstring(file_get_contents($src));
        $width = imagesx($source_image);
        $height = imagesy($source_image);

        $desired_height = intval(floor($height * ($desired_width / $width)));

        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        imagejpeg($virtual_image, $dest);
    }

    /**
     * Get thumbnail name
     * @param string $filename name of full size image
     * @return string name of thumbnail
     */
    public function getThumbName($filename)
    {
        $t = explode(".", $filename);
        array_pop($t);
        array_push($t, "_thumb.jpg");
        return implode($t);
    }

    /**
     * Create new image or show error if not authorized/upload failed
     * @param array $data image table row
     * @return Image created from data
     */
    public function create(array $data)
    {
        if (isset($_SESSION['logged_in'])) {
            $tags = explode(",", $data["tags"]);
            unset($data["tags"]);
            foreach ($tags as $k => $v) {
                $tags[$k] = trim($v);
            }

            $model = Image::fromRow($data);
            $model->setFieldValue("uploader_id", $_SESSION['logged_in']->id);

            if (isset($_FILES['imagefile']) && $_FILES['imagefile']['error'] === UPLOAD_ERR_OK) {
                $filename = $_FILES['imagefile']['name'];
                $fileNameCmps = explode(".", $filename);
                $fileExtension = strtolower(end($fileNameCmps));
                $allowedfileExtensions = array('png', 'jpg', 'jpeg', 'gif');
                $newfile = md5(time() . $filename);
                $newFileName =  $newfile . '.' . $fileExtension;
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $uploadFileDir = '../../public/images/';
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($_FILES['imagefile']['tmp_name'], __DIR__ . $dest_path)) {
                        $thumbfile = __DIR__ . '../../public/thumbs/' . $newfile . "_thumb.jpg";
                        $this->makeThumb(__DIR__ . $dest_path, $thumbfile, 180);
                        $model->setFieldValue("filename", $newFileName);
                    }
                }
            } else {
                return $this->error("Image upload failed first", 400);
            }

            if (!$model->save()) {
                return $this->error("Image upload failed secon", 400);
            }

            foreach ($tags as $t) {
                $tag = Tag::getByField("tagname", $t)->one();
                if (!$tag) {
                    $tag = Tag::fromRow(["tagname" => $t, "description" => "New", "creator_id" => $_SESSION['logged_in']->id]);
                    $tag->save();
                }

                $imgtag = ImageTag::fromRow(["image_id" => $model->id, "tag_id" => $tag->id]);
                $imgtag->save();
            }
            Router::redirect("/images/" . $model->id);
        } else {
            return $this->error("Not authorized", 401);
        }
    }


    /**
     * Get tags by image id
     * @param int $id image id
     * @return array of tags
     */
    public function getImageTagsById(int $id)
    {
        $tags = $this->getImageTagsByIdCounted($id);
        $tagsPlain = [];
        foreach ($tags as $k => $v) {
            array_push($tagsPlain, $v[1]);
        }

        return $tagsPlain;
    }

    /**
     * Get tags by image id with tag count
     * @param int $id image id
     * @return array of tags with keys being tag count
     */
    public function getImageTagsByIdCounted(int $id)
    {
        $imgtags = [];
        $itags = ImageTag::getByField("image_id", $id)->data();
        if ($itags) {
            foreach ($itags as $itag) {
                if (!isset($imgtags[$itag->tag_id])) {
                    $t = Tag::getByField("id", $itag->tag_id)->one();
                    if ($t) {
                        $imgtags[$itag->tag_id] = [1, $t->tagname];
                    }
                } else {
                    $imgtags[$itag->tag_id][0]++;
                }
            }
        }

        return $imgtags;
    }

    /**
     * Update existing image or show error if user is not authorized/image doesnt exist
     * @param int $id image id
     * @param array $data image table row
     * @return Image
     */
    public function update(array $data)
    {
        if (isset($_SESSION['logged_in'])) {
            $image = Image::getByField("id", $data["id"])->one();
            if ($image) {
                if ($image->uploader_id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
                    
                    if (isset($data["tags"])) {
                        $tags = explode(",", $data["tags"]);
                        unset($data["tags"]);
                        foreach ($tags as $k => $v) {
                            $tags[$k] = trim($v);
                        }
                    }

                    // Handle old imagetags, if they not present in new tags array
                    $oldImgTags = $this->getImageTagsByIdCounted(intval($data["id"]));

                    if ($oldImgTags) {
                        $oldTags = [];
                        foreach ($oldImgTags as $k => $v) {
                            $oldTags[$v[1]] = $k;
                        }
                    
                        $tagsForRemoval = array_diff(array_keys($oldTags), $tags);
                        $tagsForRemovalIds = [];
                        foreach($tagsForRemoval as $tfr) {
                            array_push($tagsForRemovalIds, $oldTags[$tfr]);
                        }

                        // New tags to create
                        $tags = array_diff($tags, array_keys($oldTags));

                        // Delete old tags..
                        foreach ($tagsForRemoval as $t) {
                            $itags = ImageTag::getByField("image_id", $data["id"])->data();
                            foreach ($itags as $itag) {
                                if (in_array($itag->tag_id, $tagsForRemovalIds)) {
                                    ImageTag::deleteByField("id", $itag->id);
                                }
                            }
                        }
                    }
                    
                    if (!empty($tags)) {
                        // Create new tags..
                        foreach ($tags as $t) {
                            $tag = Tag::getByField("tagname", $t)->one();
                            if (!$tag) {
                                $tag = Tag::fromRow(["tagname" => $t, "description" => "New", "creator_id" => $_SESSION['logged_in']->id]);
                                $tag->save();
                            }
            
                            $imgtag = ImageTag::fromRow(["image_id" => $data["id"], "tag_id" => $tag->id]);
                            $imgtag->save();
                        }
                    }

                    unset($data["tags"]);
                    $newimage = Image::fromRow($data);
                    $newimage->update();
                    Router::redirect("/images/" . $newimage->id);
                } else {
                    return $this->error("Not authorized", 401);
                }
            } else {
                return $this->error("Image does not exist", 400);
            }
        } else {
            return $this->error("Not authorized", 401);
        }
    }

    /**
     * Delete image by id or show error
     * @param int $id image id
     * @return view
     */
    public function delete(int $id)
    {
        if (!isset($_SESSION['logged_in'])) {
            return $this->error("User not logged in", 401);
        }
        $image = Image::getByField('id', $id)->one();
        if ($image->uploader_id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
            Image::deleteByField("id", $id);
            if($image->filename != "testimage.jpg") {
                unlink(__DIR__ . '../../public/images/' . $image->filename);
                unlink(__DIR__ . '../../public/thumbs/' . $this->getThumbName($image->filename));
            }
        } else {
            return $this->error("Not authorized", 401);
        }
        Router::redirect("/");
    }

    /**
     * Show image by id
     * @param int $id image id
     * @return view
     */
    public function getById(int $id)
    {
        $model = Image::getByField('id', $id)->one();
        if (!$model) {
            return $this->error("Image not found", 400);
        }
        $imgtags = [];
        $itags = ImageTag::getByField("image_id", $model->id)->data();
        if ($itags) {
            foreach ($itags as $itag)
                if (!isset($imgtags[$itag->tag_id])) {
                    $t = Tag::getByField("id", $itag->tag_id)->one();
                    if ($t) {
                        $imgtags[$itag->tag_id] = [1, $t->tagname];
                    }
                } else {
                    $imgtags[$itag->tag_id][0]++;
                }
        }

        $comments = [];
        $cm = Comment::getByField("image_id", $id)->data();
        foreach ($cm as $c) {
            $comments[] = ["user" => User::getByField("id", $c->poster_id)->one(), "comment" => $c];
        }

        return $this->view("image-view", [
            'model' => $model,
            'tags' => $imgtags,
            'comments' => $comments,
            'title' => $model->imagename
        ]);
    }

    /**
     * Show upload form
     * @return view
     */
    public function upload()
    {
        return $this->view(
            "image-form",
            [
                'title' => "Upload"
            ]
        );
    }

     /**
     * Show image edit form
     * @return view
     */
    public function edit(int $id)
    {
        $image = Image::getByField('id', $id)->one();
        if (!$image) {
            return $this->error("Image not found", 400);
        }

        $imgtags = $this->getImageTagsById($id);

        if ($image->uploader_id == $_SESSION['logged_in']->id || $_SESSION['logged_in']->id == 1) {
            return $this->view(
                "image-edit",
                [
                    'model' => $image,
                    'tags' => $imgtags,
                    'title' => "Edit " . $image->imagename
                ]
            );
        } else {
            return $this->error("Not authorized", 401);
        }
    }
}
