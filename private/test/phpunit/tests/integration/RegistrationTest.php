<?php

namespace Moose\Test\Integration;

use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\WebDriverWait;

/**
 * Tests registration, login and user management.
 */
class RegistrationTest extends AbstractSeleniumTest {

    /**
     * @test
     * @dataProvider registerProvider
     * @group integration
     * @group registration
     * @group userManagement
     */
    public function testRegister(string $sid, string $cdual, string $pass, string $passRepeat, bool $success) {
        $this->getDriver()->get($this->getPath(\Moose\Util\CmnCnst::PATH_REGISTER . '?skp-reg-ck=true'));
        (new WebDriverWait($this->getDriver()))->until(function(RemoteWebDriver $driver) : bool {
            return \sizeof($driver->findElements(WebDriverBy::id('register-form'))) === 1;
        });
        $this->getDriver()->findElement(WebDriverBy::name("studentid"))->sendKeys($sid);
        $this->getDriver()->findElement(WebDriverBy::name("passwordcdual"))->sendKeys($cdual);
        $this->getDriver()->findElement(WebDriverBy::name("password"))->sendKeys($pass);
        $this->getDriver()->findElement(WebDriverBy::name("password-repeat"))->sendKeys($passRepeat);
        $this->getDriver()->findElement(WebDriverBy::name("agb"))->click();
        $this->getDriver()->findElement(WebDriverBy::id("register-form"))->submit();
        if ($success) {
            $wait->until(function() : bool {
                return sizeof($this->getDriver()->findElements(WebDriverBy::id('register-success'))) === 1;
            });
        }
        else {
            $wait->until(function() : bool {
                return sizeof($this->getDriver()->findElements(WebDriverBy::className('moose-messages'))) === 1;
            });
        }
    }
    
    public function registerProvider() {
        return [
            ['s3002591', 'dummy', '12345', '12345', true],
            ['1234567@ba-dresden.de', 'dummy', 'äöüß&', 'äöüß&', true],
            ['', '', '', '', false],
            ['student.ba', 'dummy', 'abcde', 'abcde', false],
            ['s9876543', 'dummy', 'abcd', 'abcd', false],
        ];
    }    
}