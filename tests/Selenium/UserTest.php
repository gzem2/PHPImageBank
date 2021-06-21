<?php

declare(strict_types=1);

namespace PHPImageBank\tests\App;

use PHPImageBank\tests\Selenium\Helpers\SeleniumTestCase;

use Facebook\WebDriver\WebDriverBy;

/**
 * @covers UserController
 */
final class UserTest extends SeleniumTestCase
{
    public function testCreate()
    {
        $data = [];
        $data['username'] = "user_" . $this->getString(7);

        $this->driver->get($this->website . '/register');
        $this->driver->findElement(WebDriverBy::id('username'))
            ->sendKeys($data['username']);
        $this->driver->findElement(WebDriverBy::id('email'))
            ->sendKeys($data['username'] . "@email.com");
        $this->driver->findElement(WebDriverBy::id('password'))
            ->sendKeys($data['username'])
            ->submit();
        $this->assertEquals($this->website . "/", $this->driver->getCurrentUrl());

        $link = $this->driver->findElement(
            WebDriverBy::cssSelector('.header span a')
        );
        $this->assertStringContainsString("Profile(" . $data['username'] . ')', $link->getText());
        $data['href'] = $link->getattribute('href');
        return $data;
    }
    
    /**
     * @depends testCreate
     */
    public function testLogin($data)
    {
        $this->signIn($data);
        
        $profile = $this->driver->findElement(
            WebDriverBy::cssSelector('.header span a')
        );
        $this->assertStringContainsString($data['username'], $profile->getText());

        return $data;
    }

    /**
     * @depends testLogin
     */
    public function testLogout($data)
    {
        $this->signIn($data);

        $this->driver->get($this->website . '/logout');
        $link = $this->driver->findElement(
            WebDriverBy::cssSelector('.header span a')
        );
        $this->assertStringContainsString("Register", $link->getText());
        return $data;
    }

    /**
     * @depends testLogout
     */
    public function testUserDetails($data)
    {
        $this->driver->get($this->website . $data['href']);
        $username = $this->driver->findElement(
            WebDriverBy::cssSelector('.user-details table tbody tr:nth-child(2) td:nth-child(2)')
        );
        $this->assertStringContainsString($data['username'], $username->getText());
        return $data;
    }

    /**
     * @depends testUserDetails
     */
    public function testDeleteUser($data)
    {
        $this->signIn($data);

        $e = explode("/", $data['href']);
        array_splice($e, 2, 0, ["delete"]);
        $this->driver->get($this->website . implode("/", $e));

        $this->driver->get($this->website . $data['href']);
        $info = $this->driver->findElement(
            WebDriverBy::cssSelector('.content > h3')
        );
        $this->assertStringContainsString("User not found", $info->getText());
    }
}