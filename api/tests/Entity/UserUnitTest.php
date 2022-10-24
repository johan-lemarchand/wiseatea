<?php

namespace App\Tests\Entity;

use App\Entity\Token;
use App\Entity\User;
use App\Entity\UserSession;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserUnitTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        $this->user = (new User())
            ->setEmail('test@test.com')
            ->setRoles([])
            ->setPassword('MyPass8-&')
            ->setOldPassword('MyOldPass8-&')
            ->setNumInternalUser('abcdefghijklmnopur')
            ->setGender(1)
            ->setFirstname('MyName')
            ->setLastname('MyLastName')
            ->setEmail2('test2@test.com')
            ->setBirthdayAt(new \DateTime('24-02-1983 00:00:00'))
            ->setAvatar('https://path.com/avatar.com')
            ->setUsersCreate($this->createAnotherUser())
            ->addCreatedBy($this->createAnotherUser())
            ->setUsersUpdate($this->createAnotherUser())
            ->addUpdatedBy($this->createAnotherUser())
            ->setCreatedAt(new \DateTime('24-05-2022 00:00:00'))
            ->setUpdatedAt(new \DateTime('24-05-2022 00:00:00'))
            ->setToken('azertyuiopqsdfghjklmwxcvbn')
            ->addToken($this->createAToken())
            ->setActived(true)
            ->setOldPassword('oldPassword')
            ->addUserSession($this->createASession())
            ->setCgu(true)
            ->setShareData(true)
            ->setIsFacebook(true)
            ->setIsGoogle(true);
    }

    /**
     * @return User
     */
    private function createAnotherUser(): User
    {
        return (new User())
            ->setEmail('newuser@test.com')
            ->setPassword('MyPass7-&')
            ->setFirstname('MyName');
    }

    /**
     * @return Token
     */
    private function createAToken(): Token
    {
        return (new Token())
            ->setToken('azertyuiopqsdfghjklmwxcvbn123456789')
            ->setCreatedAt(new \DateTime())
            ->setExpiredAt((new \DateTime())->add(\DateInterval::createFromDateString('1 day')))
            ->setUser($this->createAnotherUser());
    }

    /**
     * @return UserSession
     */
    private function createASession(): UserSession
    {
        return (new UserSession())
            ->setToken($this->createAToken())
            ->setUser($this->createAnotherUser())
            ->setCreatedAt(new \Datetime())
            ->setFinishedAt((new \DateTime())->add(\DateInterval::createFromDateString('1 day')))
            ->setJwt('unjwt')
            ->setLastedAt(new \DateTime('15-05-2022 00:00:00'))
            ->setUserAgent('chrome')
            ->setUserIp('192.168.1.1');
    }

    public function testGetEmail()
    {
        $this->assertSame('test@test.com', $this->user->getEmail());
    }

    public function testSetEmail()
    {
        $this->user->setEmail('anotheremail@test.com');
        $this->assertSame('anotheremail@test.com', $this->user->getEmail());
    }

    public function testGetUserIdentifier()
    {
        $this->assertSame('test@test.com', $this->user->getEmail());
    }

    public function testGetRoles()
    {
        $this->assertSame(['ROLE_USER'], $this->user->getRoles());
    }

    public function testSetRoles()
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $this->assertSame(['ROLE_ADMIN', 'ROLE_USER'], $this->user->getRoles());
    }

    public function testGetPassword()
    {
        $this->assertSame('MyPass8-&', $this->user->getPassword());
    }

    public function testSetPassword()
    {
        $this->user->setPassword('MyNewPass8-&');
        $this->assertSame('MyNewPass8-&', $this->user->getPassword());
    }

    /*public function testEraseCredentials()
    {
        $this->assertNull($this->user->eraseCredentials());
    }*/

    public function testGetNumInternalUser()
    {
        $this->assertSame('abcdefghijklmnopur', $this->user->getNumInternalUser());
    }

    public function testSetNumInternalUser()
    {
        $this->user->setNumInternalUser('abcdefg');
        $this->assertSame('abcdefg', $this->user->getNumInternalUser());
    }

    public function testGetGender()
    {
        $this->assertEquals(1, $this->user->getGender());
    }

    public function testSetGender()
    {
        $this->user->setGender(2);
        $this->assertEquals(2, $this->user->getGender());
    }

    public function testGetFirstname()
    {
        $this->assertSame('MyName', $this->user->getFirstname());
    }

    public function testSetFirstname()
    {
        $this->user->setFirstname('MyNewName');
        $this->assertSame('MyNewName', $this->user->getFirstname());
    }

    public function testGetLastname()
    {
        $this->assertSame('MyLastName', $this->user->getLastname());
    }

    public function testSetLastname()
    {
        $this->user->setLastname('MyNewLastName');
        $this->assertSame('MyNewLastName', $this->user->getLastname());
    }

    public function testGetEmail2()
    {
        $this->assertSame('test2@test.com', $this->user->getEmail2());
    }

    public function testSetEmail2()
    {
        $this->user->setEmail2('anotheremail2@test.com');
        $this->assertSame('anotheremail2@test.com', $this->user->getEmail2());
    }

    public function testGetBirthdayAt()
    {
        $this->assertInstanceOf(\Datetime::class, $this->user->getBirthdayAt());
        $this->assertSame('1983-02-24 00:00:00', $this->user->getBirthdayAt()->format('Y-m-d H:i:s'));
    }

    public function testSetBirthdayAt()
    {
        $this->user->setBirthdayAt(new \Datetime('1982-08-16 00:00:00'));
        $this->assertSame('1982-08-16 00:00:00', $this->user->getBirthdayAt()->format('Y-m-d H:i:s'));
    }

    public function testGetAvatar()
    {
        $this->assertSame('https://path.com/avatar.com', $this->user->getAvatar());
    }

    public function testSetAvatar()
    {
        $this->user->setAvatar('https://path2.com/avatar.com');
        $this->assertSame('https://path2.com/avatar.com', $this->user->getAvatar());
    }

    public function testGetUsersCreate()
    {
        $this->assertInstanceOf(User::class, $this->user->getUsersCreate());
        $this->assertEquals($this->createAnotherUser(), $this->user->getUsersCreate());
    }

    public function testSetUsersCreate()
    {
        $newUser = $this->createAnotherUser()->setFirstname('IamANewUser');
        $this->user->setUsersCreate($newUser);
        $this->assertEquals($newUser, $this->user->getUsersCreate());
    }

    public function testGetCreatedBy()
    {
        $this->assertInstanceOf(Collection::class, $this->user->getCreatedBy());
        $this->assertEquals($this->createAnotherUser()->getEmail(), $this->user->getCreatedBy()->first()->getEmail());
    }

    public function testAddCreatedBy()
    {
        $this->user->addCreatedBy($this->createAnotherUser()->setEmail('anotheremail@test.com'));
        $this->assertCount(2, $this->user->getCreatedBy());
        $this->assertSame('anotheremail@test.com', $this->user->getCreatedBy()->last()->getEmail());
    }

    public function testRemoveCreatedBy()
    {
        $userAdd = $this->createAnotherUser()->setEmail('anotheremail@test.com');
        $this->user->addCreatedBy($userAdd);
        $this->assertCount(2, $this->user->getCreatedBy());
        $this->user->removeCreatedBy($userAdd);
        $this->assertCount(1, $this->user->getCreatedBy());
    }

    public function testGetUsersUpdate()
    {
        $this->assertInstanceOf(User::class, $this->user->getUsersUpdate());
        $this->assertEquals($this->createAnotherUser(), $this->user->getUsersUpdate());
    }

    public function testSetUsersUpdate()
    {
        $updateUser = $this->createAnotherUser()->setFirstname('IamAnUpdateUser');
        $this->user->setUsersUpdate($updateUser);
        $this->assertEquals($updateUser, $this->user->getUsersUpdate());
    }

    public function testGetUpdatedBy()
    {
        $this->assertInstanceOf(Collection::class, $this->user->getUpdatedBy());
        $this->assertEquals($this->createAnotherUser()->getEmail(), $this->user->getUpdatedBy()->first()->getEmail());
    }

    public function testAddUpdatedBy()
    {
        $this->user->addUpdatedBy($this->createAnotherUser()->setEmail('anotheremail@test.com'));
        $this->assertCount(2, $this->user->getUpdatedBy());
        $this->assertSame('anotheremail@test.com', $this->user->getUpdatedBy()->last()->getEmail());
    }

    public function testRemoveUpdatedBy()
    {
        $userAdd = $this->createAnotherUser()->setEmail('anotheremail@test.com');
        $this->user->addUpdatedBy($userAdd);
        $this->assertCount(2, $this->user->getUpdatedBy());
        $this->user->removeUpdatedBy($userAdd);
        $this->assertCount(1, $this->user->getUpdatedBy());
    }

    public function testGetCreatedAt()
    {
        $this->assertEquals(new \DateTime('24-05-2022 00:00:00'), $this->user->getCreatedAt());
    }

    public function testSetCreatedAt()
    {
        $this->user->setCreatedAt(new \DateTime('24-05-2022 10:25:35'));
        $this->assertEquals(new \DateTime('24-05-2022 10:25:35'), $this->user->getCreatedAt());
    }

    public function testGetUpdatedAt()
    {
        $this->assertEquals(new \DateTime('24-05-2022 00:00:00'), $this->user->getUpdatedAt());
    }

    public function testSetUpdatedAt()
    {
        $this->user->setUpdatedAt(new \DateTime('24-05-2022 10:25:35'));
        $this->assertEquals(new \DateTime('24-05-2022 10:25:35'), $this->user->getUpdatedAt());
    }

    public function testGetToken()
    {
        $this->assertSame('azertyuiopqsdfghjklmwxcvbn', $this->user->getToken());
    }

    public function testSetToken()
    {
        $this->user->setToken('nbvcxwmjhgfsqiuytreza');
        $this->assertSame('nbvcxwmjhgfsqiuytreza', $this->user->getToken());
    }

    public function testGetTokens()
    {
        $this->assertCount(1, $this->user->getTokens());
        $this->assertSame('azertyuiopqsdfghjklmwxcvbn123456789', $this->user->getTokens()->first()->getToken());
    }

    public function testAddToken()
    {
        $this->user->addToken($this->createAToken()->setToken('tokentest'));
        $this->assertCount(2, $this->user->getTokens());
        $this->assertSame('tokentest', $this->user->getTokens()->last()->getToken());
    }

    public function testRemoveToken()
    {
        $token = $this->createAToken()->setToken('tokentest');
        $this->user->addToken($token);
        $this->assertCount(2, $this->user->getTokens());
        $this->user->removeToken($token);
        $this->assertCount(1, $this->user->getTokens());
    }

    public function testGetActived()
    {
        $this->assertTrue($this->user->getActived());
    }

    public function testSetActived()
    {
        $this->user->setActived(false);
        $this->assertFalse($this->user->getActived());

    }

    public function testGetOldPassword()
    {
        $this->assertSame('oldPassword', $this->user->getOldPassword());
    }

    public function testSetOldPassword()
    {
        $this->user->setOldPassword('anotheroldpassword');
        $this->assertSame('anotheroldpassword', $this->user->getOldPassword());
    }

    public function testGetUserSessions()
    {
        $this->assertCount(1, $this->user->getUserSessions());
        $this->assertSame('unjwt', $this->user->getUserSessions()->first()->getJwt());
    }

    public function testAddUserSession()
    {
        $this->user->addUserSession($this->createASession()->setJwt('testjwt'));
        $this->assertCount(2, $this->user->getUserSessions());
        $this->assertSame('testjwt', $this->user->getUserSessions()->last()->getJwt());
    }

    public function testRemoveUserSession()
    {
        $session = $this->createASession()->setJwt('testjwt');
        $this->user->addUserSession($session);
        $this->assertCount(2, $this->user->getUserSessions());
        $this->user->removeUserSession($session);
        $this->assertCount(1, $this->user->getUserSessions());
    }

    public function testGetCgu()
    {
        $this->assertTrue($this->user->getCgu());
    }

    public function testSetCgu()
    {
        $this->user->setCgu(false);
        $this->assertFalse($this->user->getCgu());
    }

    public function testGetShareData()
    {
        $this->assertTrue($this->user->getShareData());
    }

    public function testSetShareData()
    {
        $this->user->setShareData(false);
        $this->assertFalse($this->user->getShareData());
    }

    public function testGetIsFacebook()
    {
        $this->assertTrue($this->user->getIsFacebook());
    }

    public function testSetIsFacebook()
    {
        $this->user->setIsFacebook(false);
        $this->assertFalse($this->user->getIsFacebook());
    }

    public function testSetIsGoogle()
    {
        $this->assertTrue($this->user->getIsGoogle());
    }

    public function testGetIsGoogle()
    {
        $this->user->setIsGoogle(false);
        $this->assertFalse($this->user->getIsGoogle());
    }



}
