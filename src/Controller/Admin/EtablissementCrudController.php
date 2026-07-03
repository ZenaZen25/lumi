<?php

namespace App\Controller\Admin;

use App\Entity\Etablissement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EtablissementCrudController extends AbstractCrudController
{
    /**
     * Définit l'entité gérée par ce contrôleur CRUD.
     */
    public static function getEntityFqcn(): string
    {
        return Etablissement::class;
    }

    /**
     * Configure les champs affichés dans EasyAdmin.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            // Identifiant unique de l'établissement
            IdField::new('id', 'ID')
                ->hideOnForm(),

            // Nom officiel de l'établissement
            TextField::new('nom', 'Nom'),

            // Code UAI de l'établissement
            TextField::new('codeUai', 'Code UAI'),

            // Adresse email de contact
            TextField::new('emailContact', 'Email de contact'),

            // Date de création de l'établissement
            DateTimeField::new('createdAt', 'Créé le')
                ->hideOnForm(),
        ];
    }
}