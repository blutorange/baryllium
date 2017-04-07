<?php

namespace Moose\Test\Integration;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverWait;
use Moose\Util\CmnCnst;

/**
 * Tests database + mail setup.
 */
class SystemSetupTest extends AbstractSeleniumTest {

    /**
     * @test
     * @group integration
     * @group setup
     */
    public function testSetup() {
        $this->doDbAndMail();
        $this->doAdmin();
        $this->doLogin();
        $this->doImport();
    }

    private function doDbAndMail() {
        $d = $this->getDriver();
        $d->get($this->getPath(CmnCnst::PATH_SETUP));
        (new WebDriverSelect($d->findElement(WebDriverBy::name('drive'))))
                ->selectByValue('mysql');
        $d->findElement(WebDriverBy::name('pass'))->sendKeys('baryllium');
        $d->findElement(WebDriverBy::tagName('form'))->submit();
        (new WebDriverWait($d, 40))->until(function() use ($d) {
            return \sizeof($d->findElements(WebDriverBy::id('t_setup_redirect_user'))) === 1;
        });
    }

    public function doAdmin() {
        $d = $this->getDriver();
        $d->find(WebDriverBy::css('#t_setup_redirect_user a'))->click();
        $d->findElement(WebDriverBy::name('firstname'))->sendKeys('Admin');
        $d->findElement(WebDriverBy::name('lastname'))->sendKeys('Admin');
        $d->findElement(WebDriverBy::name('mail'))->sendKeys('moose@mailinator.com');
        $d->findElement(WebDriverBy::name('password'))->sendKeys('sadmin');
        $d->findElement(WebDriverBy::name('password-repeat'))->sendKeys('sadmin');
        $d->findElement(WebDriverBy::tagName('form'))->submit();
        (new WebDriverWait($d, 10))->until(function() use ($d) {
            return \sizeof($d->findElements(WebDriverBy::id('login-form'))) === 1;
        });
    }
    
    private function doLogin() {
        $d = $this->getDriver();
        $d->findElement(WebDriverBy::name('studentid'))->sendKeys('sadmin');
        $d->findElement(WebDriverBy::name('password'))->sendKeys('sadmin');
        $d->findElement(WebDriverBy::tagName('form'))->submit();
        (new WebDriverWait($d, 10))->until(function() use ($d) {
            return \sizeof($d->findElements(WebDriverBy::id('setup-import-form'))) === 1;
        });
    }
    
    private function doImport() {
        $d = $this->getDriver();
        $upload = $d->findElement(WebDriverBy::name('importcss'));
        $upload->sendKeys('./FieldOfStudyAndCourses.csv');
        $d->findElement(WebDriverBy::tagName('form'))->submit();
        (new WebDriverWait($d, 10))->until(function() use ($d) {
            return \sizeof($d->findElements(WebDriverBy::className('field-of-study'))) === 2;
        });
    }

}