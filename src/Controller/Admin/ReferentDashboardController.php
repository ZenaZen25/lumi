<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/referent', routeName: 'referent')]
class ReferentDashboardController extends AbstractDashboardController
{
    /**
     * Quand le référent ouvre /referent,
     * il est redirigé vers la liste des signalements.
     */
    // public function index(): Response
    // {
    //     return $this->redirectToRoute('admin_signalement_index');
    // }
    #[Route('/pro', name: 'pro')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        return $this->render('referent/dashboard.html.twig');
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