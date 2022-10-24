<?php

namespace App\Tests\Entity;

use App\Entity\Token;
use App\Entity\User;
use App\Entity\UserSession;
use PHPUnit\Framework\TestCase;

class UserSessionUnitTest extends TestCase
{
    /**
     * @var UserSession
     */
    private UserSession $userSession;

    protected function setUp(): void
    {
        $this->userSession = (new UserSession())
            ->setToken($this->createAToken())
            ->setUser($this->createAUser())
            ->setCreatedAt(new \Datetime('24-05-2022 00:00:00'))
            ->setFinishedAt((new \DateTime('24-05-2022 00:00:00'))->add(\DateInterval::createFromDateString('1 day')))
            ->setJwt('unjwt')
            ->setLastedAt(new \DateTime('15-05-2022 00:00:00'))
            ->setUserAgent('chrome')
            ->setUserIp('192.168.1.1');
    }

    /**
     * @return User
     */
    private function createAUser(): User
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
            ->setCreatedAt(new \DateTime('22-05-2022 00:00:00'))
            ->setExpiredAt((new \DateTime('22-05-2022 00:00:00'))->add(\DateInterval::createFromDateString('1 day')))
            ->setUser($this->createAUser());
    }

    public function testGetToken()
    {
        $this->assertEquals($this->createAToken(), $this->userSession->getToken());
    }

    public function testSetToken()
    {
        $this->userSession->setToken($this->createAToken()->setToken('testtoken'));
        $this->assertSame('testtoken', $this->userSession->getToken()->getToken());
    }

    public function testGetLastedAt()
    {
        $this->assertEquals(new \DateTime('15-05-2022 00:00:00'), $this->userSession->getLastedAt());
    }

    public function testSetLastedAt()
    {
        $this->userSession->setLastedAt(new \DateTime('16-05-2022 10:10:10'));
        $this->assertEquals(new \DateTime('16-05-2022 10:10:10'), $this->userSession->getLastedAt());
    }

    public function testGetFinishedAt()
    {
        $this->assertEquals(new \DateTime('25-05-2022 00:00:00'), $this->userSession->getFinishedAt());
    }

    public function testSetFinishedAt()
    {
        $this->userSession->setFinishedAt(new \DateTime('26-05-2022 10:10:10'));
        $this->assertEquals(new \DateTime('26-05-2022 10:10:10'), $this->userSession->getFinishedAt());
    }

    public function testGetCreatedAt()
    {
        $this->assertEquals(new \DateTime('24-05-2022 00:00:00'), $this->userSession->getCreatedAt());
    }

    public function testSetCreatedAt()
    {
        $this->userSession->setCreatedAt(new \DateTime('26-05-2022 10:10:10'));
        $this->assertEquals(new \DateTime('26-05-2022 10:10:10'), $this->userSession->getCreatedAt());
    }

    public function testGetUserIp()
    {
        $this->assertSame('192.168.1.1', $this->userSession->getUserIp());
    }

    public function testSetUserIp()
    {
        $this->userSession->setUserIp('192.168.1.2');
        $this->assertSame('192.168.1.2', $this->userSession->getUserIp());

    }

    public function testGetUserAgent()
    {
        $this->assertSame('chrome', $this->userSession->getUserAgent());

    }

    public function testSetUserAgent()
    {
        $this->userSession->setUserAgent('mozilla');
        $this->assertSame('mozilla', $this->userSession->getUserAgent());
    }

    public function testGetJwt()
    {
        $this->assertSame('unjwt', $this->userSession->getJwt());
    }

    public function testSetJwt()
    {
        $this->userSession->setJwt('anotherjwt');
        $this->assertSame('anotherjwt', $this->userSession->getJwt());
    }

    public function testGetUser()
    {
        $this->assertEquals($this->createAUser(), $this->userSession->getUser());
    }

    public function testSetUser()
    {
        $this->userSession->setUser($this->createAUser()->setEmail('user@test.com'));
        $this->assertSame('user@test.com', $this->userSession->getUser()->getEmail());
    }
}
