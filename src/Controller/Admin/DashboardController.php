<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    /**
     * Page d'entrée de l'administration.
     *
     * Quand un utilisateur arrive sur /admin,
     * il est redirigé automatiquement vers la liste
     * des signalements, qui est la page principale
     * pour les référents.
     */
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator
                ->setController(SignalementCrudController::class)
                ->generateUrl()
        );
    }

    /**
     * Configure le titre affiché dans EasyAdmin.
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('LUMI Administration');
    }

    /**
     * Configure le menu latéral de l'administration.
     *
     * Les référents peuvent accéder aux signalements
     * et aux alertes.
     *
     * Les administrateurs ont accès aux fonctionnalités
     * supplémentaires : utilisateurs, badges et établissements.
     */
    public function configureMenuItems(): iterable
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        // Lien vers la page principale du dashboard
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Section principale du menu
        yield MenuItem::section('Gestion');

        // Accessible aux ADMIN et REFERENT
        yield MenuItem::linkToUrl(
            'Signalements',
            'fa fa-triangle-exclamation',
            $adminUrlGenerator
                ->setController(SignalementCrudController::class)
                ->generateUrl()
        );

        // Accessible aux ADMIN et REFERENT
        yield MenuItem::linkToUrl(
            'Alertes',
            'fa fa-bell',
            $adminUrlGenerator
                ->setController(AlerteCrudController::class)
                ->generateUrl()
        );

        // Accessible uniquement aux ADMIN
        yield MenuItem::linkToUrl(
            'Utilisateurs',
            'fa fa-users',
            $adminUrlGenerator
                ->setController(UserCrudController::class)
                ->generateUrl()
        )->setPermission('ROLE_ADMIN');

        // Accessible uniquement aux ADMIN
        yield MenuItem::linkToUrl(
            'Badges',
            'fa fa-award',
            $adminUrlGenerator
                ->setController(BadgeCrudController::class)
                ->generateUrl()
        )->setPermission('ROLE_ADMIN');

        // Accessible uniquement aux ADMIN
        yield MenuItem::linkToUrl(
            'Établissements',
            'fa fa-school',
            $adminUrlGenerator
                ->setController(EtablissementCrudController::class)
                ->generateUrl()
        )->setPermission('ROLE_ADMIN');
    }
}