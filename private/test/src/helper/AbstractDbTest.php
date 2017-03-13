<?php

use \Doctrine\ORM\Tools\SchemaTool;

/**
 * 
 */
abstract class AbstractDbTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Entity to test.
     */
    private static $context;

    public static function setUpBeforeClass() {
        self::$context = $GLOBALS['context'];
        self::assertNotNull(self::$context);
        if (self::$context->getMode() !== Context::$MODE_TESTING) {
            throw new PHPUnit_Runner_Exception("Mode not set to testing - please edit private/config/phinx.yml");
        }
        $tool = new SchemaTool(self::$context->getEm());
        $tool->dropDatabase();
        $metas = self::$context->getEm()->getMetadataFactory()->getAllMetadata();
        $tool->updateSchema($metas);
    }

    public static function tearDownAfterClass() {
        try {
            self::$context->closeEm();
        }
        catch (\Doctrine\ORM\ORMException $e) {
            error_log($e);
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
        return self::$context;
    }
}