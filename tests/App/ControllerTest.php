<?php

declare(strict_types=1);

namespace PHPImageBank\tests\App;

use PHPUnit\Framework\TestCase;

use PHPImageBank\App\Controller;


function extract($a)
{
}

/**
 * @covers Controller
 */
final class ControllerTest extends TestCase
{
    public $controller;
    public function setUp(): void
    {
        $this->controller = new Controller();
        $_SERVER['REQUEST_URI'] = "http://localhost:8080";
        $_SERVER['REQUEST_METHOD'] = "GET";
    }

    public function testView(): void
    {
        $data = 'data';
        $this->controller->view("../tests/Assets/test-view", [$$data => 1]);
        
        $this->expectNotToPerformAssertions();
    }
}
