<?php

namespace App\Services;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Entity\Token;
use App\Entity\UserSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class TokenService
{
    /**
     * Créer un nouveau token pur un user ou un session
     */
    public function create(User|UserSession $user, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $manager, $timeLimit):string {
        $token = new Token;
        
        $date = new DateTime();

        $tokenGenerate = $tokenGenerator->generateToken();

        $token 
            ->setToken($tokenGenerate)
            ->setCreatedAt(new DateTime())
            ->setExpiredAt($date->add(new DateInterval($timeLimit)))
        ;

        if($user instanceof User) {
            $token->setUser($user);
        } elseif($user instanceof UserSession) {
            $token->setUserSession($user);
        }

        $manager->persist($token);
        $manager->flush();

        return $tokenGenerate;
    }

    /**
     * Supprime un token
     */
    public function delete(Token $token, EntityManagerInterface $manager): bool {

        if(new DateTime() > $token->getExpiredAt()) {
            return false;
        }
        
        $manager->remove($token);
        $manager->flush();

        return true;
    }

    /**
     * Verifie si un token existe
     */
    public function findOne(Token $token): bool {
        if(!$token->getId()) {
            return false;
        }

        if(new DateTime() > $token->getExpiredAt()) {
            return false;
        }

        return true;
    }
}