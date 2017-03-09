<?php

namespace IntegrationTest;

use \Facebook\WebDriver\WebDriverBy;
/**
 * 
 */
class UserManagmentTest extends AbstractSeleniumTest {

    /**
     * @test
     * @group integration
     */
    public function testRegister() {
        $this->getDriver()->get($this->getPath("php/controller/register.php"));
        $this->assertEquals('Portal', $this->getDriver()->getTitle());
        $this->getDriver()->findElement(WebDriverBy::name("username"))->sendKeys("Andre");
        $this->getDriver()->findElement(WebDriverBy::name("password"))->sendKeys("123456");
        $this->getDriver()->findElement(WebDriverBy::name("submit"))->click();
        $this->assertNotNull($this->getDriver()->findElement(WebDriverBy::id('register-success')));
    }
}