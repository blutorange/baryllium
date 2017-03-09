<?php

namespace IntegrationTest;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;


/**
 * Base class for all selenium tests setting up the browser.
 * You will need to run java -jar selenium/selenium-server-standalone-3.3.0.jar
 * separately.
 */
class AbstractSeleniumTest extends \AbstractDbTest {

    protected static $BASE_URL = "localhost:8082/";
    protected static $SELENIUM_HOST = 'http://localhost:4444/wd/hub';
            
    /**
     * @var \RemoteWebDriver
     */
    protected static $WEB_DRIVER;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$WEB_DRIVER = RemoteWebDriver::create(self::$SELENIUM_HOST,
                DesiredCapabilities::chrome());
    }
    
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        self::$WEB_DRIVER->quit();
    }
    
    protected function getDriver() : RemoteWebDriver {
        return self::$WEB_DRIVER;
    }
    
    protected function getPath(string $subPath) : string {
        return self::$BASE_URL . '/' . $subPath;
    }
}