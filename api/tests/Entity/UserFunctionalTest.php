<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;

class UserFunctionalTest extends KernelTestCase
{
    private function getUser()
    {
        return (new User)
            ->setEmail("test@test.com")
            ->setpassword('Testpassword1&')
            ->setFirstname('John')
            ->setActived(true)
            ->setCgu(true)
            ->setShareData(true);
    }

    private function validate(User $user): ConstraintViolationList
    {
        return static::getContainer()->get('validator')->validate($user);
    }

    public function testValidUserWithMinimalDatas(): void
    {
        $user = $this->getUser();

        $kernel = self::bootKernel();
        $errors = static::getContainer()->get('validator')->validate($user);

        $this->assertCount(0, $errors);
    }

    public function testInvalidEmail(): void
    {
        $user = $this->getUser();

        // Email null
        $user->setEmail('');
        $errors = $this->validate($user);
        $this->assertSame("Vous devez indiquer votre email", $errors[0]->getMessage());

        // Email trop petit
        $user->setEmail('@a.t');
        $errors = $this->validate($user);
        $this->assertSame('Votre email doit contenir au moins 5 caractères', $errors[0]->getMessage());

        // Email trop grand
        $user->setEmail(str_repeat('emailtroplong', 20) . '@troplong.com');
        $errors = $this->validate($user);
        $this->assertSame('Votre email ne peut pas dépasser 250 caractère', $errors[0]->getMessage());

        // Email non valide
        $user->setEmail('emailnonvalide.com');
        $errors = $this->validate($user);
        $this->assertSame("Votre email n'est pas valide", $errors[0]->getMessage());
    }

    public function testNonUniqueEmail(): void
    {
        // @todo test duplicate email
    }

    public function testInvalidPassword(): void
    {
        $user = $this->getUser();

        // Password null
        $user->setPassword('');
        $errors = $this->validate($user);
        $this->assertSame("Vous devez indiquer votre mot de passe", $errors[0]->getMessage());

        // Password invalid
        $user->setPassword('PASDEMINUSCULE8&');
        $errors = $this->validate($user);
        $this->assertSame("Votre mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spéciale.", $errors[0]->getMessage());

        $user->setPassword('pasdemajuscule8&');
        $errors = $this->validate($user);
        $this->assertSame("Votre mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spéciale.", $errors[0]->getMessage());

        $user->setPassword('PasDeCaractesSpeciaux5');
        $errors = $this->validate($user);
        $this->assertSame("Votre mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spéciale.", $errors[0]->getMessage());

        $user->setPassword('PasDeChiffre-&');
        $errors = $this->validate($user);
        $this->assertSame("Votre mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spéciale.", $errors[0]->getMessage());

        // Password trop petit
        $user->setPassword('Pet5&');
        $errors = $this->validate($user);
        $this->assertSame("Votre mot de passe doit contenir au moins 8 caractères.", $errors[0]->getMessage());
    }

    public function testInvalidFirstname(): void 
    {
        $user = $this->getUser();

        // Firstname null
        $user->setFirstname('');
        $errors = $this->validate($user);
        $this->assertSame("Vous devez indiquer votre prénom", $errors[0]->getMessage());
    }
}
