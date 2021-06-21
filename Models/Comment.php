<?php

declare(strict_types=1);

namespace PHPImageBank\Models;

use PHPImageBank\App\Model;

/**
 * Comment model
 */
class Comment extends Model
{
    public static string $table = "comments"; /**< table name in DB */

    /**
     * Init table fields
     */
    public static function init() {     
        static::$fields = [
            'id' => "INTEGER NOT NULL PRIMARY KEY " . static::compat("AUTOINC"),
            'image_id' => "INTEGER NOT NULL",
            'poster_id' => "INTEGER NOT NULL",
            'post_date' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            'comment' => "TEXT",
            'foreign_keys' => [
                "FOREIGN KEY(image_id) REFERENCES Images(id)",
                "FOREIGN KEY(poster_id) REFERENCES Users(id)"
            ],
            'validate_fields' => []
        ];
    }
}

Comment::init();