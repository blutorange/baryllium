<?php

namespace Moose\Test\Unit;

use Doctrine\ORM\ORMException;
use Moose\Context\Context;
use Moose\Context\MooseConfig;
use PHPUnit_Framework_TestCase;
use PHPUnit_Runner_Exception;

/**
 * 
 */
abstract class AbstractContextTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        if (Context::getInstance()->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_TESTING)) {
            throw new PHPUnit_Runner_Exception("Mode not set to testing - please edit private/config/phinx.yml");
        }
    }

    public static function tearDownAfterClass() {
        try {
            Context::getInstance()->closeEm();
        }
        catch (ORMException $e) {
            \error_log($e);
        }
    }
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        \error_log("Session is " . \session_status());
        Context::configureInstance();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }
    
    protected function getContext() : Context {
        return Context::getInstance();
    }
    
    protected function assertValidate($entity, int $numberOfErrs) {
        $errMsg = array();
        $res = $entity->validate($errMsg, $this->getTranslator());
        self::assertEquals(\sizeof($errMsg), $numberOfErrs);
        self::assertTrue($res && \sizeof($errMsg) === 0 || !$res && \sizeof($errMsg) > 0);
    }
}