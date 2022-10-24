<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestResponseController extends AbstractController
{
    #[Route('/api/test', name: 'app_test_response')]
    public function index(Request $request): Response
    {
        return $this->json("OK !!!", 200);
    }
}
