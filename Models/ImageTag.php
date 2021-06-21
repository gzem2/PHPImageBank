<?php

declare(strict_types=1);

namespace PHPImageBank\Models;

use PHPImageBank\App\Model;
use PHPImageBank\App\ModelCollection;
use PHPImageBank\Models\Image;

/**
 * ImageTag model.
 * This model represents relations between images and tags.
 * One image can have multiple tags, and multiple images can share same tags.
 */
class ImageTag extends Model
{
    public static string $table = "imagetags";
    /**< table name in DB */

    /**
     * Init table fields
     */
    public static function init()
    {
        static::$fields = [
            'id' => "INTEGER NOT NULL PRIMARY KEY " . static::compat("AUTOINC"),
            'image_id' => "INTEGER NOT NULL",
            'tag_id' => "INTEGER NOT NULL",
            'foreign_keys' => [
                "FOREIGN KEY(image_id) REFERENCES Images(id)",
                "FOREIGN KEY(tag_id) REFERENCES tags(id)"
            ],
            'validate_fields' => []
        ];
    }

    /**
     * Get count of images which share same tags as specified in the list
     * @param array $tags array of tags
     * @return int number of images which share the tags
     */
    public static function getCountForTags(array $tags): int
    {
        $query = "SELECT COUNT(*), tag_id FROM imagetags WHERE tag_id IN (";

        $params = [];
        foreach ($tags as $t) {
            $tm = Tag::getByField("tagname", $t)->one();
            $params[":" . $t] = $tm->id;
            $query = $query . ":$t, ";
        }
        $query = rtrim($query, ", ") . ")";

        $sth = static::connect()->prepare($query);

        $sth->execute($params);
        return intval($sth->fetchColumn());
    }

    /**
     * Get images which share same tags as specified in the list
     * @param array $tags array of tags
     * @param string $limit SQL limit
     * @param string $offset SQL offset
     * @param array $order assoc array to specify ORDER of SQL select query
     * @return ModelCollection collection of models returned from DB or empty collection
     */
    public static function getImgsForTags(array $tags, $limit = null, $offset = null, $order = null): ModelCollection
    {
        $query = "SELECT image_id, tag_id FROM imagetags WHERE tag_id IN (";
        $params = [];
        foreach ($tags as $t) {
            $tm = Tag::getByField("tagname", $t)->one();
            $params[":" . $t] = $tm->id;
            $query = $query . ":$t, ";
        }
        $query = rtrim($query, ", ") . ")" . "GROUP BY image_id HAVING COUNT(*) > " . strval(count($tags) - 1);

        if ($order) {
            if ($order["last"])
                $query = $query . " ORDER BY " . $order["column"] . " " . $order["sort"];
        }

        $sql = $query . ($limit ? " LIMIT :row_count" : "") . ($offset ? " OFFSET :offset" : "");

        if ($order) {
            if (!$order["last"]) {
                $sql = "SELECT * FROM (" .  $sql . ") AS T1 " . " ORDER BY " . $order["column"] . " " . $order["sort"];
            } else {
                $sql = "SELECT * FROM (" .  $sql . ") AS T1 " . " ORDER BY " . $order["column"] . " DESC";
            }
        }

        $sth = static::connect()->prepare($sql);
        if ($limit) {
            $params[':row_count'] = $limit;
            if ($offset) $params[':offset'] = $offset;
        }

        $sth->execute($params);
        $image_ids = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $models = new ModelCollection();
        foreach ($image_ids as $d) {
            $c = Image::getByField("id", $d["image_id"])->one();
            if ($c) {
                $models->add($c);
            }
        }
        return $models;
    }
}

ImageTag::init();
