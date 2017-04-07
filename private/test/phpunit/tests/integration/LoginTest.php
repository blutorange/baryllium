<?php

namespace Moose\Test\Integration;

use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\WebDriverWait;

/**
 * Tests registration, login and user management.
 */
class LoginTest extends AbstractSeleniumTest {

    /**
     * @test
     * @dataProvider loginProvider
     * @group integration
     * @group login
     * @group userManagement
     */
    public function testLogin(string $sid, string $pass, bool $success) {
        $this->getDriver()->get($this->getPath(\Moose\Util\CmnCnst::PATH_LOGIN_PAGE));
        (new WebDriverWait($this->getDriver()))->until(function(RemoteWebDriver $driver) : bool {
            return \sizeof($driver->findElements(WebDriverBy::id('register-form'))) === 1;
        });
        $this->getDriver()->findElement(WebDriverBy::name("studentid"))->sendKeys($sid);
        $this->getDriver()->findElement(WebDriverBy::name("password"))->sendKeys($pass);
        $this->getDriver()->findElement(WebDriverBy::id("register-form"))->submit();
        if ($success) {
            $wait->until(function() : bool {
                return sizeof($this->getDriver()->findElements(WebDriverBy::id('dashboard'))) === 1;
            });
        }
        else {
            $wait->until(function() : bool {
                return sizeof($this->getDriver()->findElements(WebDriverBy::className('moose-messages'))) === 1;
            });
        }
    }
    
    public function loginProvider() {
        return [
            ['s3002591', '12345', true],
            ['sadmin', 'sadmin', true],
            ['sadmin', 'admin', false],
            ['s3002591', '1234', false],
            ['1234567@ba-dresden.de', 'äöüß&', 'äöüß&', true],
            ['', '', false],
            ['student.ba', '12345', false],
            ['s9876543', 'abcd', false],
        ];
    }    
}