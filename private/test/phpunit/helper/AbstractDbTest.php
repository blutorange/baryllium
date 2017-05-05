<?php

namespace Moose\Test\Unit;

use Doctrine\ORM\Tools\SchemaTool;
use Moose\Context\Context;
use PHPUnit_Framework_TestCase;

/**
 * 
 */
abstract class AbstractDbTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        $tool = new SchemaTool(Context::getInstance()->getEm());
        $tool->dropDatabase();
        $metas = Context::getInstance()->getEm()->getMetadataFactory()->getAllMetadata();
        $tool->updateSchema($metas);
    }
}