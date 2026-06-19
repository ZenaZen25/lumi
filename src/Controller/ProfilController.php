<?php

namespace App\Controller;

use App\Entity\Badge;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ELEVE');

        $user = $this->getUser();

        $totalPoints = 0;

        foreach ($user->getCouragePoints() as $point) {
            $totalPoints += $point->getPoints() ?? 0;
        }

        $nextBadge = null;
        $pointsNeeded = 0;

        $allBadges = $em->getRepository(Badge::class)->findBy([], [
            'pointsRequis' => 'ASC',
        ]);

        foreach ($allBadges as $badge) {
            if ($badge->getPointsRequis() !== null && $badge->getPointsRequis() > $totalPoints) {
                $nextBadge = $badge;
                $pointsNeeded = $badge->getPointsRequis() - $totalPoints;
                break;
            }
        }

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'totalPoints' => $totalPoints,
            'points' => $user->getCouragePoints(),
            'badges' => $user->getUserBadges(),
            'nextBadge' => $nextBadge,
            'pointsNeeded' => $pointsNeeded,
        ]);
    }
}