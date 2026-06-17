<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $badges = [];
        $totalPoints = 0;

        if ($user instanceof User) {

            foreach ($user->getCouragePoints() as $point) {
                $totalPoints += $point->getPoints() ?? 0;
            }

            foreach ($user->getUserBadges() as $userBadge) {
                $badge = $userBadge->getBadge();

                if ($badge) {
                    $badges[] = $badge;
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'badges' => $badges,
            'totalPoints' => $totalPoints,
        ]);
    }
}