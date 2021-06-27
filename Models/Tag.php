<?php

declare(strict_types=1);

namespace PHPImageBank\Models;

use PHPImageBank\App\Model;

/**
 * Tag model
 */
class Tag extends Model
{
    public static string $table = "tags"; /**< table name in DB */

    public static array $fields = [];

    /**
     * Init table fields
     */
    public static function init() {     
        static::$fields = [
            'id' => "INTEGER NOT NULL PRIMARY KEY " . static::compat("AUTOINC"),
            'tagname' => "VARCHAR(30) NOT NULL",
            'description' => "VARCHAR(140)",
            'creator_id' => "INTEGER NOT NULL",
            'foreign_keys' => [
                "FOREIGN KEY(creator_id) REFERENCES Users(id)"
            ],
            'validate_fields' => [
                "tagname",
                "description"
            ]
        ];
    }
}

Tag::init();