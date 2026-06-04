<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class AnonymousController extends AbstractController
{
    #[Route('/chat/anonymous', name: 'app_chat_anonymous')]
    public function startAnonymous(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        // Marque la session comme anonyme
        $session->set('is_anonymous', true);
        $session->set('anonymous_prenom', 'Anonyme');
        // Supprime une éventuelle conversation précédente
        $session->remove('conversation_id');

        return $this->redirectToRoute('app_chat');
    }
}