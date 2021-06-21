<?php

namespace PHPImageBank\tests\Selenium\Helpers;

class SeleniumBootstrapper
{
    public static $chrLatestRelease;

    public static function get_chrome_latest_release()
    {
        static::$chrLatestRelease = file_get_contents("https://chromedriver.storage.googleapis.com/LATEST_RELEASE");
    }

    static function get_chrome_webdriver()
    {
        $webdriverDir = __DIR__ . '/../webdriver';

        if (!file_exists($webdriverDir)) {
            mkdir($webdriverDir, 0777, true);
        }

        switch (strtolower(PHP_OS)) {
            case 'darwin':
                $chrZip = "chromedriver_mac64.zip";
                $chrDriver = $webdriverDir . "/chromedriver";
                break;
            case 'winnt':
                $chrZip = "chromedriver_win32.zip";
                $chrDriver = $webdriverDir . "/chromedriver.exe";
                break;
            case 'linux':
                $chrZip = "chromedriver_linux64.zip";
                $chrDriver = $webdriverDir . "/chromedriver";
                break;
        }
        $chrDriverZip = $webdriverDir . "/" . $chrZip;

        $chrLatestReleaseFile = $webdriverDir . '/chrLatestRelease';

        if (!file_exists($chrLatestReleaseFile) || file_get_contents($chrLatestReleaseFile) != static::$chrLatestRelease) {
            file_put_contents($chrLatestReleaseFile, static::$chrLatestRelease);
            $f = file_get_contents("https://chromedriver.storage.googleapis.com/" . static::$chrLatestRelease .  "/" . $chrZip);
            file_put_contents($chrDriverZip, $f);
            $zip = new \ZipArchive;
            $res = $zip->open($chrDriverZip);
            if ($res === TRUE) {
                $zip->extractTo($webdriverDir);
                $zip->close();
            }
        }

        putenv("WEBDRIVER_CHROME_DRIVER=" . $chrDriver);
    }

    protected static $process;
    protected static $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    protected static $pipes;

    public static function startServer(): void
    {
        SeleniumBootstrapper::get_chrome_webdriver();

        static::$process = proc_open("php -S localhost:8080 " . __DIR__ . "/../../../public/index.php", static::$descriptorspec, static::$pipes);

        //usleep(100000);
    }

    public static function stopServer(): void
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $pstatus = proc_get_status(static::$process);
            exec('taskkill /F /T /PID ' . $pstatus['pid']);
        } else {
            fclose(static::$pipes[0]);
            fclose(static::$pipes[1]);
            fclose(static::$pipes[2]);
            proc_terminate(static::$process);
        }
    }
}

SeleniumBootstrapper::get_chrome_latest_release();
