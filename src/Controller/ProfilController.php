<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ELEVE');

        $user = $this->getUser();

        $totalPoints = 0;

        foreach ($user->getCouragePoints() as $point) {
            $totalPoints += $point->getPoints();
        }

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'totalPoints' => $totalPoints,
            'points' => $user->getCouragePoints(),
            'badges' => $user->getUserBadges(),
        ]);
    }
}