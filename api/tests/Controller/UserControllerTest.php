<?php

namespace App\Tests;

use App\Entity\Token;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private KernelBrowser $client;

    private array $user = [
        'email' => 'test@test.com',
        'password' => 'testpasS8&',
        'firstname' => 'myname',
        'cgu' => true,
        'share_data' => true
    ];

    /**
     * Appelée avant chaque test
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        // On vide toutes les tables de la bdd
        $conn = self::getContainer()->get('doctrine.orm.entity_manager')->getConnection();
        $conn->executeQuery('DELETE FROM token');
        $conn->executeQuery('DELETE FROM user_session');
        $conn->executeQuery('DELETE FROM "user"');
        $conn->executeQuery('DELETE FROM adress');
    }

    /**
     * Test la fonction registration()
     * @return void
     */
    public function testValidRegistration(): void
    {
        // Active le profiler (pour recupérer les emails)
        $this->client->enableProfiler();
        self::bootKernel();

        // Création d'un nouvel utilisateur
        $this->client->request('POST', '/api/user/registration',[], [], [], json_encode($this->user));

        // Check si Success et status code
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        // Check le contenu de la reponse
        $message = json_decode($response->getContent(), true);
        $this->assertEquals("Votre compte à bien été créé", $message["message"]);

        // Check si le nouveau user est enregistré dans la bdd
        $newUser = self::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);
        $this->assertSame($this->user['email'], $newUser->getEmail());

        // Check si un token a été enregistré pour le nouveau utilisateur
        $token = self::getContainer()->get('doctrine')->getRepository(Token::class)->findOneBy(['user' => $newUser]);
        $this->assertEquals($newUser->getId(), $token->getUser()->getId());

        // Test envoie d'email avec token
        $message = $this->client->getProfile()->getCollector('mailer')->getEvents()->getMessages();
        $this->assertCount(1, $message);
        // Récupération du token envoyé par mail et check si c'est le même qu'en Bdd
        $tokenMail = $message[0]->getContext()["token"];
        $tokenBdd = self::getContainer()->get('doctrine')->getRepository(Token::class)->findOneBy(['token' => $tokenMail])->getToken();
        $this->assertSame($tokenMail, $tokenBdd);

        $emailMail = $message[0]->getHeaders()->get('to')->getAddressStrings()[0];
        $emailBdd = self::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => $emailMail])->getEmail();
        $this->assertSame($emailMail, $emailBdd);
    }

    /**
     * Test le fontion registration()
     * @return void
     */
    public function testRegistrationWithBadData(): void
    {
        // Active le profiler (pour recupérer les emails)
        $this->client->enableProfiler();
        self::bootKernel();

        // Modification de l'email de l'utilisateur pour avoir une erreur 400
        $this->user['email'] = 'bademail.com';

        // Création d'un nouvel utilisateur
        $this->client->request('POST', '/api/user/registration',[], [], [], json_encode($this->user));

        // Check si le status code est 400
        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        // Check le contenu de la reponse / message d'erreur
        $violations = json_decode($response->getContent(), true)['violations'];
        $this->assertCount(1, $violations);
        $this->assertSame("Votre email n'est pas valide", $violations[0]['title']);

        // Vérifie que le user n'est pas enregistré en BDD
        $user = self::getContainer()->get('doctrine')->getRepository(User::class)->findAll();
        $this->assertCount(0, $user);
    }

    /**
     * Test le fontion registration()
     * @return void
     */
    public function testRegistrationWithBadJsonFormat(): void
    {
        // Active le profiler (pour recupérer les emails)
        $this->client->enableProfiler();
        self::bootKernel();

        // Modification du json, suppression de la 1ère virgule et envoi de la requete
        $jsonUser = preg_replace('/,/', '', json_encode($this->user),1);
        $this->client->request('POST', '/api/user/registration',[], [], [], json_encode($jsonUser));

        // Check si le status code est 400 et si message est bien invalid data
        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $message = mb_substr(json_decode($response->getContent())->message, 0, mb_strpos(json_decode($response->getContent())->message,'"'));
        $this->assertSame('Invalid data', trim($message));
    }

    /**
     * Test le fontion activation()
     * @return void
     */
    public function testValidAccountActivation(): void
    {
        // Active le profiler (pour recupérer les emails)
        self::bootKernel();

        // Création d'un nouvel utilisateur
        $this->client->request('POST', '/api/user/registration',[], [], [], json_encode($this->user));

        $db = self::getContainer()->get('doctrine');
        $user = $db->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);
        $token = $db->getRepository(Token::class)->findOneBy(['user' => $user]);

        // Check si le compte est crée mais pas activé
        $this->assertFalse($user->getActived());

        $this->client->request('GET', "/api/user/activation/" . $token->getToken());

        self::getContainer()->get('doctrine.orm.entity_manager')->refresh($user);

        // Check si Success et status code
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // Check si le compte est crée et activé
        $this->assertTrue($user->getActived());

        // Check si le token a été supprimé une fois le compte activé
        $this->assertCount(0, $db->getRepository(Token::class)->findAll());
    }

    /**
     * Test le fontion activation()
     * @return void
     */
    public function testInvalidAccountActivationWithExpiredToken(): void
    {
        // Active le profiler (pour recupérer les emails)
        self::bootKernel();

        // Création d'un nouvel utilisateur
        $this->client->request('POST', '/api/user/registration',[], [], [], json_encode($this->user));

        $db = self::getContainer()->get('doctrine');
        $user = $db->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);
        $token = $db->getRepository(Token::class)->findOneBy(['user' => $user]);

        // Modification des dates d'expiration et de création du token
        $token->setCreatedAt(new \DateTime('2022-05-15 00:00:00'));
        $token->setExpiredAt(new \DateTime('2022-05-16 00:00:00'));
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        $manager->persist($token);
        $manager->flush();

        // Requête pour activer le compte
        $this->client->request('GET', "/api/user/activation/" . $token->getToken());
        $response = $this->client->getResponse();

        // Check status code et message d'erreur en cas de token expiré
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame("Le jeton d'activation à expiré", json_decode($response->getContent())->error);
    }

    /**
     * Test le fontion activation()
     * @return void
     */
    public function testInvalidAccountActivationWithInexistantToken(): void
    {
        // Active le profiler (pour recupérer les emails)
        self::bootKernel();

        // Création d'un nouvel utilisateur
        $this->client->request('POST', '/api/user/registration',[], [], [], json_encode($this->user));

        $db = self::getContainer()->get('doctrine');
        $user = $db->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);
        $token = $db->getRepository(Token::class)->findOneBy(['user' => $user]);

        // Suppression du token en bdd
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        $manager->remove($token);
        $manager->flush();

        // Requête pour activer le compte
        $this->client->request('GET', "/api/user/activation/" . $token->getToken());
        $response = $this->client->getResponse();

        // Check status code et message d'erreur en cas de token expiré
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame("Le jeton d'activation n'existe pas", json_decode($response->getContent())->error);
    }

    public function testValidForgotPasswordRequest(): void
    {
        // Active le profiler (pour recupérer les emails)
        $this->client->enableProfiler();
        self::bootKernel();
        // Création d'un nouvel utilisateur et activation du compte
        $this->client->request('POST', '/api/user/registration',[], [], [], json_encode($this->user));
        $db = self::getContainer()->get('doctrine');
        $user = $db->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);
        $token = $db->getRepository(Token::class)->findOneBy(['user' => $user]);
        $this->client->request('GET', "/api/user/activation/" . $token->getToken());

        // Envoie de la demande de mot de passe oublié avec l'adresse du user en POST
        $this->client->request('POST', '/api/user/forgotPassword', [], [], [], json_encode([
            'email' => 'test@test.com'
        ]));

        // Check si le token a été supprimé une fois le compte activé
        $this->assertCount(1, $db->getRepository(Token::class)->findAll());

        // Check si un token a été enregistré pour l'utilisateur
        $token = $db->getRepository(Token::class)->findOneBy(['user' => $user]);
        $this->assertEquals($user->getId(), $token->getUser()->getId());
        
    }


}
