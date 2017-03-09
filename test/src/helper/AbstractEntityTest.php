<?php

namespace UnitTest;

/**
 * Base class for all tests testing an entity class for the database.
 */
abstract class AbstractEntityTest extends \AbstractDbTest {  
    protected static function assertValidate($entity, int $numberOfErrs) {
        $errMsg = array();
        $res = $entity->validate($errMsg, "en");
        self::assertEquals(sizeof($errMsg), $numberOfErrs);
        self::assertTrue($res && sizeof($errMsg) === 0 || !$res && sizeof($errMsg) > 0 );
    }
}
