<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Entity\Alerte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SignalementController extends AbstractController
{
    #[Route('/signalement', name: 'app_signalement')]
    public function index(): Response
    {
        return $this->render('signalement/index.html.twig');
    }

    #[Route('/signalement/submit', name: 'app_signalement_submit', methods: ['POST'])]
    public function submit(Request $request, EntityManagerInterface $em): Response
    {
        $type        = $request->request->get('type');
        $zone        = $request->request->get('zone', '');
        $frequence   = $request->request->get('frequence');
        $description = $request->request->get('description', '');

        // Détermine la sévérité selon la fréquence
        $severite = match($frequence) {
            'tous_les_jours' => 'high',
            'plusieurs_fois' => 'medium',
            default          => 'low',
        };

        $signalement = new Signalement();
        $signalement->setType($type);
        $signalement->setZone($zone);
        $signalement->setSeverite($severite);
        $signalement->setDescription($description);
        $signalement->setStatut('nouveau');
        $signalement->setUser($this->getUser());
        $signalement->setAnonymousToken($request->getSession()->getId());
        $signalement->setCreatedAt(new \DateTimeImmutable());
        $signalement->setUpdatedAt(new \DateTime());

        // Établissement si user connecté
        if ($this->getUser()?->getEtablissement()) {
            $signalement->setEtablissement($this->getUser()->getEtablissement());
        }

        $em->persist($signalement);
        $em->flush();

        // Crée une alerte admin
        $alerte = new Alerte();
        $alerte->setSeverite($severite);
        $alerte->setStatut('nouveau');
        $alerte->setNoteInterne('Signalement soumis via le wizard.');
        $alerte->setCreatedAt(new \DateTimeImmutable());
        $alerte->setSignalement($signalement);
        $em->persist($alerte);
        $em->flush();

        // Points Courage si user connecté
        if ($this->getUser()) {
            $user = $this->getUser();
            $user->setCouragePoints(($user->getCouragePoints() ?? 0) + 50);
            $em->flush();
        }

        return $this->redirectToRoute('app_signalement_confirmation');
    }

    #[Route('/signalement/confirmation', name: 'app_signalement_confirmation')]
    public function confirmation(): Response
    {
        return $this->render('signalement/confirmation.html.twig');
    }
}