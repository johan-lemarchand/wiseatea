<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\UserSession;
use Symfony\Component\HttpFoundation\RequestStack;

class CreateSession
{
    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param String $jwt
     * @param array $userDecode
     * @param User $user
     * @return UserSession
     */
    public function createUserSession(String $jwt, Array $userDecode, User $user): UserSession
    {
        return (new UserSession())
            ->setJwt($jwt)
            ->setCreatedAt((new \Datetime)->setTimestamp($userDecode['iat']))
            // A changer - la valeur n'est pas la bonne
            ->setLastedAt((new \Datetime)->setTimestamp($userDecode['iat']))
            ->setFinishedAt((new \Datetime)->setTimestamp($userDecode['exp']))
            ->setUser($user)
            ->setUserIp($this->requestStack->getCurrentRequest()->getClientIp())
            // @todo Pb avec le user agent - renvoie null
            ->setUserAgent($this->requestStack->getCurrentRequest()->headers->get('User-Agent'));
    }
}