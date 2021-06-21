<?php

declare(strict_types=1);

namespace PHPImageBank\App;

require_once __DIR__ . '/../vendor/autoload.php';

use PDOException;
use PHPImageBank\Models\User;
use PHPImageBank\Models\Image;
use PHPImageBank\Models\Tag;
use PHPImageBank\Models\ImageTag;
use PHPImageBank\Models\Comment;

/**
 *  Class to create tables and/or seed DB with data
 */
class Seeder
{
    /**
     * Create table of specified model
     * @param string $model fully qualified class name of a model
     */
    public static function createTable($model)
    {
        if (isset($model::$fields["foreign_keys"])) {
            $fk = $model::$fields["foreign_keys"];
        }

        $m = [];
        foreach ($model::$fields as $k => $v) {
            if (!is_array($v)) {
                $m[$k] = $v;
            }
        }

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $model::$table . ' (';
        foreach ($m as $k => $v) {
            $sql = $sql . $k . " " . $v . ", ";
        }
        if (isset($fk)) {
            foreach ($fk as $f) {
                $sql = $sql . $f . ", ";
            }
        }
        $sql = rtrim($sql, ", ") . ')';

        $model::connect()->exec($sql);
    }

    /**
     * Seed DB with data or pass if its already present in DB
     * @param $model model instance to be saved
     */
    public static function seedTable($model)
    {
        $model::init();
        try {
            $model->save();
        } catch (PDOException $e) {
            return false;
        }
    }
}

if (php_sapi_name() == 'cli') {
    $tables = [User::class, Image::class, Tag::class, ImageTag::class, Comment::class];
    foreach ($tables as $t) {
        echo "Creating table if not exist.. $t" . PHP_EOL;
        Seeder::createTable($t);
    }

    echo "Creating admin user.." . PHP_EOL;
    $admin = User::fromRow(["id" => 1, "username" => "admin", "email" => "", "password" => password_hash("admin", PASSWORD_DEFAULT)]);
    Seeder::seedTable($admin);
    
    Seeder::seedTable(Tag::fromRow(["tagname" => "tag_a", "description" => "Example tag description", "creator_id" => 1]));
    Seeder::seedTable(Tag::fromRow(["tagname" => "tag_b", "description" => "Example tag description", "creator_id" => 1]));
    Seeder::seedTable(Tag::fromRow(["tagname" => "tag_c", "description" => "Example tag description", "creator_id" => 1]));

    $opt = getopt("n", ["noseed"]);
    if (!isset($opt["n"]) and !isset($opt["noseed"])) {
        echo "Creating tags.." . PHP_EOL;
        foreach (range('a', 'z') as $t) {
            Seeder::seedTable(Tag::fromRow(["tagname" => "tag_" . $t, "description" => "Example tag description", "creator_id" => 1]));
        }

        echo "Creating images.." . PHP_EOL;
        foreach (range(1, 90) as $t) {
            Seeder::seedTable(Image::fromRow(["imagename" => "testimage", "filename" => "testimage.jpg",  "description" => "Example image description", "uploader_id" => 1]));
        }

        echo "Creating imagetags.." . PHP_EOL;
        foreach (range(1, 90) as $t) {
            Seeder::seedTable(ImageTag::fromRow(["image_id" => $t, "tag_id" => rand(1, 26)]));
            Seeder::seedTable(ImageTag::fromRow(["image_id" => $t, "tag_id" => rand(1, 26)]));
        }
    }
}
