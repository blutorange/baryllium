<?php

namespace UnitTest;

use Doctrine\ORM\Tools\SchemaTool;

/**
 * 
 */
abstract class AbstractEntityTest extends \AbstractDbTest {  
    protected static function assertValidate($entity, int $numberOfErrs) {
        $errMsg = array();
        $res = $entity->validate($errMsg, "en");
        self::assertEquals(sizeof($errMsg), $numberOfErrs);
        self::assertTrue($res && sizeof($errMsg) === 0 || !$res && sizeof($errMsg) > 0 );
    }
}
