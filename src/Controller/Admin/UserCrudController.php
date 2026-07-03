<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    /**
     * Indique à EasyAdmin quelle entité est gérée
     * par ce contrôleur CRUD.
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * Configure les champs affichés dans
     * l'interface d'administration.
     *
     * Les champs sensibles (mot de passe, sessionToken...)
     * ne sont volontairement pas affichés.
     */
    public function configureFields(string $pageName): iterable
    {
        return [

            // Identifiant unique (lecture seule)
            IdField::new('id')
                ->hideOnForm(),

            // Prénom de l'utilisateur
            TextField::new('prenom', 'Prénom'),

            // Adresse email utilisée pour la connexion
            TextField::new('email', 'Email'),

            // Classe de l'élève
            TextField::new('codeClasse', 'Code classe'),

            // Choix du rôle parmi les rôles disponibles
            ChoiceField::new('role', 'Rôle')
                ->setChoices([
                    'Élève' => 'ELEVE',
                    'Référent' => 'REFERENT',
                    'Administrateur' => 'ADMIN',
                ]),

            // Établissement auquel appartient l'utilisateur
            AssociationField::new('etablissement', 'Établissement'),

            // Indique si le compte est anonyme
            BooleanField::new('isAnonymous', 'Anonyme'),
        ];
    }
}