<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google')]
    public function connect(ClientRegistry $clientRegistry): Response
    {
        // Redirect to Google OAuth
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'email', 'profile'
            ], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheck(Request $request): Response
    {
        // This method is handled by GoogleAuthenticator
        // It will never be executed
        return new Response('This should never be reached');
    }
}
