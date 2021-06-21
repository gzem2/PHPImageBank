<?php

declare(strict_types=1);

namespace PHPImageBank\tests\App;

use PHPUnit\Framework\TestCase;

use PHPImageBank\App\Router;

/**
 * @covers Router
 */
final class RouterTest extends TestCase
{
    public function setUp(): void
    {
        $_SERVER["REQUEST_METHOD"] = "GET";
    }

    public function testCheckRoute(): void
    {
        $route = array("expression" => "/users/([0-9]*)", "function" => function () {
            return;
        }, "method" => "get");

        $path = "/users/300";
        $this->assertEquals(array(true, true), Router::checkRoute($route, $path));

        $path = "/undefined";
        $this->assertEquals(array(false, false), Router::checkRoute($route, $path));

        $_SERVER["REQUEST_METHOD"] = "POST";
        $path = "/users/300";
        $this->assertEquals(array(true, false), Router::checkRoute($route, $path));
    }

    public function testRun(): void
    {
        $found = "";
        Router::get('/users', function () use (&$found) {
            $found = "users";
        });
        Router::get('/customers', function () use (&$found) {
            $found = "customers";
        });
        Router::get('/admins', function () use (&$found) {
            $found = "admins";
        });

        $_SERVER['REQUEST_URI'] = "/users";
        Router::run();
        $this->assertEquals("users", $found);

        $_SERVER['REQUEST_URI'] = "/admins";
        Router::run();
        $this->assertEquals("admins", $found);
    }
}
