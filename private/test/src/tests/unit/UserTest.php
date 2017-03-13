<?php

namespace UnitTest;

use \Entity\User;
use \Dao\UserDao;

class UserTest extends AbstractEntityTest {

    /**
     * @test
     * @dataProvider usernameProvider
     * @covers \Entity\User::setUsername
     * @group entity
     * @group unit
     */
    public function testUsername(string $username, int $errors) {
        $user = new User();
        $user->setUserName($username);
        $user->setPassword("123abcABC$%&");
        $this->assertValidate($user, $errors);
    }
    
    public function usernameProvider()
    {
        return [
            ["", 1],
            ["Andre", 0],
            ["あ の さ", 0],
        ];
    }    
    
    /**
     * @test
     * @dataProvider passwordProvider
     * @covers \Entity\User::setPassword
     * @covers \Entity\User::verifyPassword
     * @group entity
     * @group unit
     */
    public function testPassword(string $password, int $errors) {
        $user = new User();
        $user->setUserName("Andre");
        $user->setPassword($password);
        $this->assertValidate($user, $errors);
        if ($errors === 0) {
            $this->assertTrue($user->verifyPassword($password));
            $this->assertFalse($user->verifyPassword($password . "a"));
        }
    }
    
    public function passwordProvider()
    {
        return [
            ["", 1],
            ["1", 1],
            ["1234", 1],
            ["12345", 0],
        ];
    }

    /**
     * @test
     * @covers \Entity\User::getUsername
     * @covers \Entity\User::getId
     * @covers \Dao\UserDao::persist
     * @covers \Entity\User::verifyPassword
     * @group entity
     * @group unit
     */    
    public function testPersist() {
        $user = new User();
        $dao = $user->getDao($this->getEm());
        $user->setUserName("Andre");
        $user->setPassword("12345");
        $this->assertEquals($user->getId(), \Entity\AbstractEntity::$INITIAL_ID);
        $this->assertCount(0, $dao->persist($user, $this->getTranslator(), true));
        $this->assertNotEquals($user->getId(), \Entity\AbstractEntity::$INITIAL_ID);
        $loadedUser = $dao->findOneById($user->getId());
        $this->assertEquals($loadedUser->getId(), $user->getId());
        $this->assertEquals($loadedUser->getUsername(), "Andre");
        $this->assertTrue($loadedUser->verifyPassword("12345"));
        $this->assertFalse($loadedUser->verifyPassword("123456"));
    }
}