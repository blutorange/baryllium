<?php

use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Moose\Context\Context;
use Moose\Context\MooseConfig;

/**
 * 
 */
abstract class AbstractDbTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        if (Context::getInstance()->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_TESTING)) {
            throw new PHPUnit_Runner_Exception("Mode not set to testing - please edit private/config/phinx.yml");
        }
        $tool = new SchemaTool(Context::getInstance()->getEm());
        $tool->dropDatabase();
        $metas = Context::getInstance()->getEm()->getMetadataFactory()->getAllMetadata();
        $tool->updateSchema($metas);
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
}