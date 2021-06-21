<?php

declare(strict_types=1);

namespace PHPImageBank\Models;

use PHPImageBank\App\Model;

/**
 * Image model
 */
class Image extends Model
{
    public static string $table = "images"; /**< table name in DB */

    /**
     * Init table fields
     */
    public static function init()
    {
        static::$fields = [
            'id' => "INTEGER NOT NULL PRIMARY KEY " . static::compat("AUTOINC"),
            'imagename' => "VARCHAR(30) NOT NULL",
            'filename' => "VARCHAR(30) NOT NULL",
            'description' => "VARCHAR(140)",
            'upload_date' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            'uploader_id' => "INTEGER NOT NULL",
            'foreign_keys' => [
                "FOREIGN KEY(uploader_id) REFERENCES Users(id)"
            ],
            'validate_fields' => [
                "imagename",
                "description"
            ]
        ];
    }
}

Image::init();