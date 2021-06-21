<?php

declare(strict_types=1);

namespace PHPImageBank\tests\Selenium\Helpers;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

/**
 * Generic selenium testcase
 */
abstract class SeleniumTestCase extends TestCase
{
    protected $driver;

    public $website = "http://localhost:8080";

    public function setUp(): void
    {
        $serverUrl = 'http://localhost:4444/wd/hub';
        $this->driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome());
    }

    public function tearDown(): void
    {
        $this->driver->quit();
    }

    public function getString(int $length): string
    {
        return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,$length);
    }

    public function signIn($data): void
    {
        $this->driver->get($this->website);
        $this->driver->findElement(WebDriverBy::id('username'))
            ->sendKeys($data['username']);
        $this->driver->findElement(WebDriverBy::id('password'))
            ->sendKeys($data['username'])
            ->submit();
    }
}
