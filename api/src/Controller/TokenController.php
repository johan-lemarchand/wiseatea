<?php

namespace App\Controller;

use App\Entity\Token;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/token', name: 'token_')]
class TokenController extends AbstractController
{
    #[Route('/findOne/{token}', name: 'findOne', methods: "GET")]
    public function findOne(Token $token): JsonResponse {
        if(!$token->getId()) {
            return $this->json([
                "error" => "Se jeton n'existe pas"
            ], 400);
        }

        if(new DateTime() > $token->getExpiredAt()) {
            return $this->json([
                "error" => "Se jeton à expiré"
            ], 400);
        }

        return $this->json([
            "token" => $token
        ], 200);
    }
}
