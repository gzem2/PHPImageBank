<?php

declare(strict_types=1);

namespace PHPImageBank\Models;

use PHPImageBank\App\Model;

/**
 * User model
 */
class User extends Model
{
    public static string $table = "users"; /**< table name in DB */

    /**
     * Init table fields
     */
    public static function init() {     
        static::$fields = [
            'id' => "INTEGER NOT NULL PRIMARY KEY " . static::compat("AUTOINC"),
            'username' => "VARCHAR(30) NOT NULL",
            'email' => "VARCHAR(30)",
            'password' => "VARCHAR(255) NOT NULL",
            'register_date' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            'validate_fields' => [
                "username",
                "email",
                "password"
            ]
        ];
    }
}

User::init();