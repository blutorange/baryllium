<?php

namespace Moose\Test\Unit;

use Moose\Util\PlaceholderTranslator;
use \Doctrine\ORM\EntityManager;

/**
 * Base class for all tests testing an entity class for the database.
 */
abstract class AbstractEntityTest extends \AbstractDbTest {

    private $sessionHandler;

    public function setUp() {
        error_log("Session is " . session_status());
        Context::configureInstance(dirname(__FILE__, 5));
        $this->sessionHandler = new \Moose\Context\PortalSessionHandler();
    }
    
    protected function getContext() : \Moose\Context\Context {
        return Context::getInstance();
    }
    
    protected function getSessionHandler() : \Moose\Context\PortalSessionHandler {
        return $this->sessionHandler;
    }
    
    protected function getTranslator() : PlaceholderTranslator {
        return $this->getSessionHandler()->getTranslator();
    }

    protected function getEm(): EntityManager {
        return Context::getInstance()->getEm();
    }

    protected function assertValidate($entity, int $numberOfErrs) {
        $errMsg = array();
        $res = $entity->validate($errMsg, $this->getTranslator());
        self::assertEquals(sizeof($errMsg), $numberOfErrs);
        self::assertTrue($res && sizeof($errMsg) === 0 || !$res && sizeof($errMsg) > 0);
    }
}
