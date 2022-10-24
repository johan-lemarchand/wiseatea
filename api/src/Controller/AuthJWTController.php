<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('api', name: 'api_')]
class AuthJWTController extends AbstractController
{
    /**
     * Route appelée lors de l'envoi en POST des identifiants pour le login
     */
    #[Route(path: '/login', name: 'login', methods: 'POST')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): JsonResponse
    {
        return $this->json([
            'message' => 'Ce message ne doit pas apparaitre !',
            'path' => 'src/Controller/AuthJWTController.php',
        ], 400);
    }

    /**
     * Route appelée lors de la déconnexion
     */
    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Route appelée pour regénérer un jwt lorsqu'il y a expiration
     */
    #[Route(path: '/jwt/refresh', name: 'jwt_refresh', methods: 'POST')]
    public function jwtRefresh(): JsonResponse
    {
        return $this->json([
            'message' => 'Ce message ne doit pas apparaitre !',
            'path' => 'src/Controller/AuthController.php',
        ], 400);
    }
}