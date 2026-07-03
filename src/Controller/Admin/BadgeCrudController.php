<?php

namespace App\Controller\Admin;

use App\Entity\Badge;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BadgeCrudController extends AbstractCrudController
{
    /**
     * Définit l'entité gérée par ce contrôleur CRUD.
     */
    public static function getEntityFqcn(): string
    {
        return Badge::class;
    }

    /**
     * Configure les champs affichés dans EasyAdmin.
     *
     * Les badges sont utilisés pour récompenser les élèves
     * lorsqu'ils atteignent un certain nombre de Points Courage.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            // Identifiant unique du badge
            IdField::new('id', 'ID')
                ->hideOnForm(),

            // Nom du badge affiché dans le profil élève
            TextField::new('nom', 'Nom'),

            // Description du badge
            TextareaField::new('description', 'Description'),

            // Icône associée au badge
            TextField::new('icone', 'Icône'),

            // Nombre de points nécessaires pour obtenir le badge
            IntegerField::new('pointsRequis', 'Points requis'),
        ];
    }
}