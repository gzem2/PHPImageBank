<?php

declare(strict_types=1);

namespace PHPImageBank\tests\App;

use PHPUnit\Framework\TestCase;

use PHPImageBank\App\Model;

class TestModel extends Model
{
    public static string $table = "testmodels";

    public static function init() {     
        static::$fields = [
            'id' => "INTEGER NOT NULL PRIMARY KEY " . static::compat("AUTOINC"),
            'varcharfield' => "VARCHAR(30) NOT NULL",
            'intfield' => "int(6) NOT NULL",
            'validate_fields' => [
                "varcharfield",
                "intfield",
            ]
        ];
    }
}

/**
 * @covers Model
 */
final class ModelTest extends TestCase
{
    public $model;
    
    public function setUp(): void
    {
        TestModel::init();
        $this->model = TestModel::fromRow([
            "id" => 0,
            "varcharfield" => "data",
            "intfield" => 1
        ]);
    }

    public function testValidateField(): void
    {
        $this->assertTrue($this->model->validate_field("id"));
        $this->model->values["id"] = null;
        $this->assertFalse($this->model->validate_field("id"));

        $this->assertTrue($this->model->validate_field("varcharfield"));
        $this->model->values["varcharfield"] = "01234567890123456789012345678911";
        $this->assertFalse($this->model->validate_field("varcharfield"));

        $this->assertTrue($this->model->validate_field("intfield"));
        $this->model->values["intfield"] = null;
        $this->assertFalse($this->model->validate_field("intfield"));


        $this->model::$fields["intfield"] = "int(6)";
        $this->assertTrue($this->model->validate_field("intfield"));
    }

    public function testValidate() : void
    {
        $this->assertTrue($this->model->validate());
    }
}
