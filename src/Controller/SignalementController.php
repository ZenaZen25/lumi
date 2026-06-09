<?php

namespace App\Controller;

use App\Entity\Signalement;
use App\Entity\Alerte;
use App\Entity\CouragePoint;
use App\Entity\Badge;
use App\Entity\UserBadge;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class SignalementController extends AbstractController
{
    /**
     * Affiche le formulaire de signalement.
     */
    #[Route('/signalement', name: 'app_signalement')]
    public function index(): Response
    {
        return $this->render('signalement/index.html.twig');
    }

    /**
     * Traite la soumission du formulaire de signalement.
     *
     * Cette méthode :
     * 1. récupère les données envoyées par l'utilisateur ;
     * 2. crée un nouveau signalement ;
     * 3. génère automatiquement une alerte ;
     * 4. attribue des points Courage ;
     * 5. redirige vers la page de confirmation.
     */
    #[Route('/signalement/submit', name: 'app_signalement_submit', methods: ['POST'])]
    public function submit(Request $request, EntityManagerInterface $em): Response
    {
        // ==========================
        // RÉCUPÉRATION DES DONNÉES
        // ==========================

        $type        = $request->request->get('type');
        $zone        = $request->request->get('zone', '');
        $frequence   = $request->request->get('frequence');
        $description = $request->request->get('description', '');

        // ==========================
        // CALCUL DE LA SÉVÉRITÉ
        // ==========================
        //
        // Plus les faits sont fréquents,
        // plus la gravité est élevée.
        //
        $severite = match ($frequence) {
            'tous_les_jours' => 'high',
            'plusieurs_fois' => 'medium',
            default          => 'low',
        };

        // ==========================
        // CRÉATION DU SIGNALEMENT
        // ==========================

        $signalement = new Signalement();

        $signalement->setType($type);
        $signalement->setZone($zone);
        $signalement->setSeverite($severite);
        $signalement->setDescription($description);

        // Le signalement est créé avec le statut "nouveau"
        $signalement->setStatut('nouveau');

        // Association éventuelle à un utilisateur connecté
        $signalement->setUser($this->getUser());

        // Identifiant anonyme basé sur la session
        $signalement->setAnonymousToken(
            $request->getSession()->getId()
        );

        // Dates de création et de mise à jour
        $signalement->setCreatedAt(
            new \DateTimeImmutable()
        );

        $signalement->setUpdatedAt(
            new \DateTime()
        );

        // ==========================
        // ASSOCIATION ÉTABLISSEMENT
        // ==========================
        //
        // Si l'utilisateur possède un établissement,
        // le signalement lui est rattaché.
        //
        if ($this->getUser()?->getEtablissement()) {
            $signalement->setEtablissement(
                $this->getUser()->getEtablissement()
            );
        }

        // Sauvegarde en base de données
        $em->persist($signalement);
        $em->flush();

        // ==========================
        // CRÉATION D'UNE ALERTE
        // ==========================
        //
        // Chaque signalement génère automatiquement
        // une alerte destinée à l'administration.
        //
        $alerte = new Alerte();

        $alerte->setSeverite($severite);
        $alerte->setStatut('nouveau');

        $alerte->setNoteInterne(
            'Signalement soumis via le wizard.'
        );

        $alerte->setCreatedAt(
            new \DateTimeImmutable()
        );

        $alerte->setSignalement($signalement);

        $em->persist($alerte);
        $em->flush();

        // ==========================
        // ATTRIBUTION DES POINTS
        // ==========================
        //
        // Récompense l'utilisateur connecté
        // pour encourager les signalements.
        //
        $user = $this->getUser();

        if ($user) {
            $couragePoint = new CouragePoint();

            $couragePoint->setUser($user);
            $couragePoint->setPoints(50);
            $couragePoint->setRaison('Signalement envoyé');
            $couragePoint->setCreatedAt(new \DateTimeImmutable());

            $em->persist($couragePoint);
            $em->flush();

            $totalPoints = 0;

            foreach ($user->getCouragePoints() as $point) {
                $totalPoints += $point->getPoints();
            }

            $badges = $em->getRepository(Badge::class)->findAll();

            foreach ($badges as $badge) {
                if ($badge->getPointsRequis() !== null && $totalPoints >= $badge->getPointsRequis()) {
                    $alreadyHasBadge = false;

                    foreach ($user->getUserBadges() as $userBadge) {
                        if ($userBadge->getBadge() === $badge) {
                            $alreadyHasBadge = true;
                            break;
                        }
                    }

                    if (!$alreadyHasBadge) {
                        $userBadge = new UserBadge();
                        $userBadge->setUser($user);
                        $userBadge->setBadge($badge);
                        $userBadge->setObtainedAt(new \DateTimeImmutable());

                        $em->persist($userBadge);
                    }
                }
            }

            $em->flush();
        }

        // ==========================
        // REDIRECTION FINALE
        // ==========================

        return $this->redirectToRoute(
            'app_signalement_confirmation'
        );
    }

    /**
     * Affiche la page de confirmation
     * après l'envoi d'un signalement.
     */
    // #[Route('/signalement/confirmation', name: 'app_signalement_confirmation')]
    // public function confirmation(): Response
    // {
    //     return $this->render(
    //         'signalement/confirmation.html.twig'
    //     );
    // }

    #[Route('/signalement/confirmation', name: 'app_signalement_confirmation')]
    public function confirmation(): Response
    {
        $totalPoints = 0;

        if ($this->getUser()) {
            foreach ($this->getUser()->getCouragePoints() as $point) {
                $totalPoints += $point->getPoints();
            }
        }

        return $this->render(
            'signalement/confirmation.html.twig',
            [
                'totalPoints' => $totalPoints,
            ]
        );
    }
}
