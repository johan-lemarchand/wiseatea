<?php

namespace App\Tests\Entity;

use App\Entity\Token;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TokenUnitTest extends TestCase
{
    /**
     * @var Token
     */
    private Token $token;

    protected function setUp(): void
    {
        $this->token = (new Token())
            ->setToken('azertyuiopqsdfghjklmwxcvbn123456789')
            ->setCreatedAt(new \DateTime('22-05-2022 00:00:00'))
            ->setExpiredAt((new \DateTime('22-05-2022 00:00:00'))->add(\DateInterval::createFromDateString('1 day')))
            ->setUser($this->createAUser());
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

    public function testGetToken()
    {
        $this->assertSame('azertyuiopqsdfghjklmwxcvbn123456789', $this->token->getToken());
    }

    public function testSetToken()
    {
        $this->token->setToken('azertyuiopqsdfghjklmwxcvbn');
        $this->assertSame('azertyuiopqsdfghjklmwxcvbn', $this->token->getToken());
    }

    public function testGetCreatedAt()
    {
        $this->assertEquals(new \DateTime('22-05-2022 00:00:00'), $this->token->getCreatedAt());
    }

    public function testSetCreatedAt()
    {
        $this->token->setCreatedAt(new \DateTime('22-05-2022 10:10:10'));
        $this->assertEquals(new \DateTime('22-05-2022 10:10:10'), $this->token->getCreatedAt());
    }

    public function testGetExpiredAt()
    {
        $this->assertEquals(new \DateTime('23-05-2022 00:00:00'), $this->token->getExpiredAt());
    }

    public function testSetExpiredAt()
    {
        $this->token->setExpiredAt(new \DateTime('23-05-2022 10:10:10'));
        $this->assertEquals(new \DateTime('23-05-2022 10:10:10'), $this->token->getExpiredAt());
    }

    public function testGetUser()
    {
        $this->assertInstanceOf(User::class, $this->token->getUser());
        $this->assertEquals($this->createAUser(), $this->token->getUser());
    }

    public function testSetUser()
    {
        $this->token->setUser($this->createAUser()->setEmail('anotheremail@test.com'));
        $this->assertSame('anotheremail@test.com', $this->token->getUser()->getEmail());
    }
}
