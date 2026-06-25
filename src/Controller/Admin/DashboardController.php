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
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator
                ->setController(SignalementCrudController::class)
                ->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('LUMI Administration');
    }

    public function configureMenuItems(): iterable
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion');

        yield MenuItem::linkToUrl(
            'Signalements',
            'fa fa-triangle-exclamation',
            $adminUrlGenerator->setController(SignalementCrudController::class)->generateUrl()
        );

        yield MenuItem::linkToUrl(
            'Alertes',
            'fa fa-bell',
            $adminUrlGenerator->setController(AlerteCrudController::class)->generateUrl()
        );

        yield MenuItem::linkToUrl(
            'Utilisateurs',
            'fa fa-users',
            $adminUrlGenerator->setController(UserCrudController::class)->generateUrl()
        );

        yield MenuItem::linkToUrl(
            'Badges',
            'fa fa-award',
            $adminUrlGenerator->setController(BadgeCrudController::class)->generateUrl()
        );

        yield MenuItem::linkToUrl(
            'Établissements',
            'fa fa-school',
            $adminUrlGenerator->setController(EtablissementCrudController::class)->generateUrl()
        );
    }
}