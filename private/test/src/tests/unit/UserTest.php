<?php

/* Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace UnitTest;

use Doctrine\DBAL\Types\ProtectedString;
use Entity\AbstractEntity;
use Entity\User;

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
        $user->setPassword(new ProtectedString("123abcABC$%&"));
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
        $user->setPassword(new ProtectedString($password));
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
        $user->setUsername("Andre");
        $user->setFirstName("Andre");
        $user->setLastName("Wachsmuth");
        $user->setRole("Student");
        $user->setMail("sensenmann5@gmail.com");
        $user->setPassword(new ProtectedString("12345"));
        $this->assertEquals($user->getId(), AbstractEntity::$INITIAL_ID);
        $errors = $dao->persist($user, $this->getTranslator(), true);
        $this->assertCount(0, $errors, print_r($errors, true));
        $this->assertNotEquals($user->getId(), AbstractEntity::$INITIAL_ID);
        $loadedUser = $dao->findOneById($user->getId());
        $this->assertEquals($loadedUser->getId(), $user->getId());
        $this->assertEquals($loadedUser->getUsername(), "Andre");
        $this->assertTrue($loadedUser->verifyPassword("12345"));
        $this->assertFalse($loadedUser->verifyPassword("123456"));
    }
}
