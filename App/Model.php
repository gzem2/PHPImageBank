<?php

declare(strict_types=1);

namespace PHPImageBank\App;

require_once __DIR__ . '/../config/DB.php';

use PHPImageBank\App\ModelCollection;

/**
 * Generic model class
 */
class Model
{
    public static string $table; /**< table name in DB */
    public static array $fields; /**< model fields in DB */
    public array $values; /**< model instance field values */

    protected static $conn; /**< PDO connection instance */
    protected static $driver; /**< name of PDO driver */

    /**
     * SQL compat values between MySQL and SQLite
     */
    public static array $compat_values = [
        "AUTOINC" => [
            "mysql" => "AUTO_INCREMENT",
            "sqlite" => "AUTOINCREMENT"
        ]
    ];

    /**
     * Return compat value depending on used PDO driver
     * @param string $prop compat prop name
     * @return string compat prop value
     */
    public static function compat($prop)
    {
        if (!static::$driver) {
            static::$driver =  static::connect()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        }
        return static::$compat_values[$prop][static::$driver];
    }

    /**
     * Create model instance from table row 
     * @param array $row table row
     * @return Model instance
     */
    public static function fromRow($row)
    {
        $instance = new static();
        $instance->values = $row;
        foreach ($row as $k => $v) {
            $instance->$k = $v;
        }
        return $instance;
    }

    /**
     * Set model instance field value
     * @param $field name of field
     * @param $value value to be set
     * @return Model instance
     */
    public function setFieldValue($field, $value)
    {
        $this->values[$field] = $value;
        if (!isset($this->$field)) {
            $this->$field = $value;
        }
        return $this;
    }

    /**
     * Create new PDO instance or return PDO instance if already exists
     * @return PDO instance
     */
    public static function connect()
    {
        if (!static::$conn) {
            static::$conn = new \PDO(DB_CONNECTION_STRING, DB_USER, DB_PASS);
            static::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return static::$conn;
    }

    /**
     * Validate specific model field
     * @param string $field name of model field to be validated
     * @return true if validation passed or false if its failed
     */
    public function validate_field(string $field): bool
    {
        $good = false;
        $input = static::$fields[$field];
        $matches = [];

        $check_length = function ($matches) use ($field, &$good) {
            array_shift($matches);

            if (!empty($matches[0])) {
                $f = $this->values[$field];
                if (is_int($f)) {
                    if (intval($f) <= intval($matches[0])) {
                        $good = true;
                    }
                } elseif (is_string($f)) {
                    if (strlen($f) <= intval($matches[0])) {
                        $good = true;
                    }
                } else {
                    $good = true;
                }
            }
        };

        $check_not_null = function ($matches) use ($field, &$good) {
            array_shift($matches);

            if (!empty($matches[0])) {
                $f = $this->values[$field];
                if (is_numeric($f)) {
                    if (intval($f) >= 0) {
                        $good = true;
                    } else {
                        $good = false;
                    }
                } elseif (is_string($f)) {
                    if (strlen($f) > 0) {
                        $good = true;
                    } else {
                        $good = false;
                    }
                } else {
                    $good = false;
                }
            }
        };

        if (preg_match('/^.*VARCHAR\s?\((\d*)\)\s?.*$/i', $input, $matches)) {
            $check_length($matches);
            if (!$good) {
                return false;
            }
        }
        if (preg_match('/^.*INT\s?\((\d*)\)\s?.*$/i', $input, $matches)) {
            $check_length($matches);
            if (!$good) {
                return false;
            }
        }
        if (preg_match('/\s*(NOT\s+NULL)\.*/i', $input, $matches)) {
            $check_not_null($matches);
            if (!$good) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate all model fields
     * @return true if all model fields pass the check or false if it fails
     */
    public function validate()
    {
        foreach (static::$fields['validate_fields'] as $f) {
            if (!$this->validate_field($f)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Save model in DB
     * @return Model instance with ID field set or false if it fails to validate
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->values as $k => $v) {
            if ($v) {
                if(is_string($v)) {
                    $this->values[$k] = htmlspecialchars($v, ENT_NOQUOTES);
                } else {
                    $this->values[$k] = $v;
                }
            }
        }

        $sql = "INSERT INTO " . static::$table . " (";
        foreach ($this->values as $k => $v) {
            $sql = $sql . $k . ", ";
        }
        $sql = rtrim($sql, ", ") . ") VALUES (";
        foreach ($this->values as $k => $v) {
            $sql = $sql . ":" . $k . ", ";
        }
        $sql = rtrim($sql, ", ") . ")";

        $sth = $this->connect()->prepare($sql);
        $sth->execute($this->values);

        $this->setFieldValue("id", $this->connect()->lastInsertId());
        return $this;
    }

    /**
     * Update model
     * @return Model instance or false if it fails to validate
     */
    public function update()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->values as $k => $v) {
            if ($v) {
                if(is_string($v)) {
                    $this->values[$k] = htmlspecialchars($v, ENT_NOQUOTES);
                } else {
                    $this->values[$k] = $v;
                }
            }
        }

        $sql = "UPDATE " . static::$table . " SET ";

        foreach ($this->values as $k => $v) {
            $sql = $sql . $k . "=:" . $k . ", ";
        }
        $sql = rtrim($sql, ", ");

        $sql = $sql . " WHERE id=:id";

        $sth = $this->connect()->prepare($sql);
        $sth->execute($this->values);

        return $this;
    }

    /**
     * Generic select query
     * @param string $query SQL select query
     * @param string $limit SQL limit
     * @param string $offset SQL offset
     * @param array $field_params assoc array of pairs field => value to be used with sql WHERE query
     * @param array $order assoc array to specify ORDER of SQL select query
     * @return ModelCollection collection of models returned from DB or empty collection
     */
    public static function getSelect($query, $limit = null, $offset = null, $field_params = null, $order = null)
    {
        $params = [];
        if ($field_params) {
            $field = array_keys($field_params)[0];
            $value = reset($field_params);
            $params = [':' . $field => $value];
            $query = $query . " WHERE $field=:$field";
        }

        if($order) {
            if($order["last"])
                $query = $query . " ORDER BY " . $order["column"] . " " . $order["sort"];
        }

        $sql = $query . ($limit ? " LIMIT :row_count" : "") . ($offset ? " OFFSET :offset" : "");

        if($order) {
            if(!$order["last"]) {
                $sql = "SELECT * FROM (" .  $sql . ") AS T1 " . " ORDER BY " . $order["column"] . " " . $order["sort"];
            } else {
                $sql = "SELECT * FROM (" .  $sql . ") AS T1 " . " ORDER BY " . $order["column"] . " DESC";
            }
        }

        $sth = static::connect()->prepare($sql);
        if ($limit) {
            $params[':row_count'] = $limit;
            if ($offset) $params[':offset'] = $offset;
            $sth->execute($params);
        } else {
            if (!empty($params)) {
                $sth->execute($params);
            } else {
                $sth->execute();
            }
        }

        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $models = new ModelCollection();
        foreach ($data as $row) {
            $models->add(static::fromRow($row));
        }

        return $models;
    }

    /**
     * Select all rows from model table
     * @param string $limit SQL limit
     * @param string $offset SQL offset
     * @param array $order assoc array to specify ORDER of SQL select query
     * @return ModelCollection collection of models returned from DB or empty collection
     */
    public static function getAll($limit = null, $offset = null, $order = null)
    {
        $query = "SELECT * FROM " . static::$table;

        return static::getSelect($query, $limit, $offset, null, $order);
    }

    /**
     * Select rows by field model
     * @param string $field name of field
     * @param string $value value of field
     * @param string $limit SQL limit
     * @param string $offset SQL offset
     * @return ModelCollection collection of models returned from DB or empty collection
     */
    public static function getByField($field, $value, $limit = null, $offset = null)
    {
        $query = "SELECT * FROM " . static::$table;

        return static::getSelect($query, $limit, $offset, [$field => $value]);
    }

    /**
     * Get amount of rows in model table
     * @return int number of rows
     */
    public static function getCount() : int
    {
        $sql = "SELECT COUNT(*) FROM " . static::$table;

        $sth = static::connect()->prepare($sql);
        $sth->execute();
        return intval($sth->fetchColumn());
    }

    /**
     * Delete rows from table by field value
     * @param string $field name of field
     * @param string $value value of field
     */
    public static function deleteByField($field, $value)
    {
        $sql = "DELETE FROM " . static::$table . " WHERE $field=:$field";

        $sth = static::connect()->prepare($sql);
        $sth->execute([":$field" => $value]);
    }
}
