<?php

namespace App\EventListener;

use App\Entity\User;
use App\Services\CreateSession;
use App\Services\DecodeJWT;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class JWTListener
{
    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * @var DecodeJWT
     */
    private DecodeJWT $decoder;

    /**
     * @var CreateSession
     */
    private CreateSession $createSession;

    /**
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $manager
     * @param DecodeJWT $decoder
     * @param CreateSession $createSession
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $manager, DecodeJWT $decoder, CreateSession $createSession)
    {
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->decoder = $decoder;
        $this->createSession = $createSession;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        
        $userId = $this->manager->getRepository(User::class)->findOneBy(['email' => $payload['email']])->getId();

        $payload['id'] = $userId;
        $event->setData($payload);
    }

    /**
     * @param AuthenticationSuccessEvent $event
     * 
     * @return void
     */
    public function onJWTAuthentificationSuccess(AuthenticationSuccessEvent $event): void
    {
        // Récupère le jwt et divise en 3 parties : 0 -> headers, 1 -> charge utile : user
        $jwt = $event->getData()['token'];        
        $userDecode = $this->decoder->decodeJWT($jwt);

        $user = $this->manager->getRepository(User::class)->findOneBy(['id' => $userDecode['id']]);

        $userSession = $this->createSession->createUserSession(
            $jwt,
            $userDecode,
            $user
        );

        $this->manager->persist($userSession);
        $this->manager->flush();
    }
}