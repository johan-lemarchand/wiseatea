<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\UserSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class DecodeJWT
{
    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(DecoderInterface $decoder, RequestStack $requestStack, EntityManagerInterface $manager)
    {
        $this->decoder = $decoder;
        $this->requestStack = $requestStack;
    }

    /**
     * Permet de décoder un token JWT pour créer un array avec les infos du token
     */
    public function decodeJWT(String $jwt): Array
    {
        $request = $this->requestStack->getCurrentRequest();
        $jwtParts = explode('.', $jwt);

        $userDecode = $this->decoder->decode(base64_decode($jwtParts[1]),'json');
        
        return $userDecode;
    }
}