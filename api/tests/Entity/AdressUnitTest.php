<?php

namespace App\Tests\Entity;

use App\Entity\Adress;
use PHPUnit\Framework\TestCase;

class AdressUnitTest extends TestCase
{
    /**
     * @var Adress
     */
    private Adress $adress;

    protected function setUp(): void
    {
        $this->adress = (new Adress())
            ->setAdress('my street')
            ->setCity('London')
            ->setZipCode('40335');
    }

    public function testGetAdress()
    {
        $this->assertSame('my street', $this->adress->getAdress());
    }

    public function testSetAdress()
    {
        $this->adress->setAdress('my another street');
        $this->assertSame('my another street', $this->adress->getAdress());
    }

    public function testGetZipCode()
    {
        $this->assertSame('40335', $this->adress->getZipCode());
    }

    public function testSetZipCode()
    {
        $this->adress->setZipCode('85260');
        $this->assertSame('85260', $this->adress->getZipCode());
    }

    public function testGetCity()
    {
        $this->assertSame('London', $this->adress->getCity());
    }

    public function testSetCity()
    {
        $this->adress->setCity('Paris');
        $this->assertSame('Paris', $this->adress->getCity());
    }

}
