<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/referent', routeName: 'referent')]
class ReferentDashboardController extends AbstractDashboardController
{
    /**
     * Quand le référent ouvre /referent,
     * il est redirigé vers la liste des signalements.
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_signalement_index');
    }

    /**
     * Titre du dashboard référent.
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('LUMI Référent');
    }

    /**
     * Menu visible pour les référents :
     * uniquement Signalements et Alertes.
     */
    public function configureMenuItems(): iterable
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToUrl(
            'Signalements',
            'fa fa-triangle-exclamation',
            $adminUrlGenerator
                ->setController(SignalementCrudController::class)
                ->generateUrl()
        );

        yield MenuItem::linkToUrl(
            'Alertes',
            'fa fa-bell',
            $adminUrlGenerator
                ->setController(AlerteCrudController::class)
                ->generateUrl()
        );
    }
}