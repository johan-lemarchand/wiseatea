<?php

namespace App\Services;

use App\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;

class GenerateJWTs {

    /**
     * @var RefreshTokenGeneratorInterface
     */
    private RefreshTokenGeneratorInterface $refreshTokenGenerator;

    /**
     * @var RefreshTokenManagerInterface
     */
    private RefreshTokenManagerInterface $refreshTokenManager;

    /**
     * @var JWTTokenManagerInterface
     */
    private JWTTokenManagerInterface $JWTManager;

    public function __construct(RefreshTokenGeneratorInterface $refreshTokenGenerator, RefreshTokenManagerInterface $refreshTokenManager, JWTTokenManagerInterface $JWTManager)
    {
        $this->refreshTokenGenerator = $refreshTokenGenerator;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->JWTManager = $JWTManager; 
    }

    public function generateJWTs(User $user): Array
    {
        // Crée un JWT pour l'utilisateur
        $jwtToken = $this->JWTManager->create($user);
        // Génère un refresh token 
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 2592000);
        // Enregistre le refresh token dans la bdd
        $this->refreshTokenManager->save($refreshToken, true);

        return [
            'jwt_token' => $jwtToken, 
            'refresh_token' => $refreshToken->getRefreshToken()
        ];
    }

}