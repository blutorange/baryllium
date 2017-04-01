<?php

namespace Moose\Test\Integration;

use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\WebDriverWait;

/**
 * Tests registration, login and user management.
 */
class UserManagmentTest extends AbstractSeleniumTest {

    /**
     * @test
     * @group integration
     * @group userManagement
     */
    public function testRegister() {
        $this->getDriver()->get($this->getPath("public/controller/register.php"));
        (new WebDriverWait($this->getDriver()))->until(function(RemoteWebDriver $driver) : bool {
            return 'Register' === $driver->getTitle();
        });
        $this->getDriver()->findElement(WebDriverBy::name("username"))->sendKeys("Andre");
        $this->getDriver()->findElement(WebDriverBy::name("password"))->sendKeys("123456");
        $this->getDriver()->findElement(WebDriverBy::name("btnSubmit"))->click();
        (new WebDriverWait($this->getDriver(), 10))->until(function() : bool {
            return sizeof($this->getDriver()->findElements(WebDriverBy::id('register-success'))) === 1;
        });
    }
}