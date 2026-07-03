<?php

namespace App\Controller\Admin;

use App\Entity\Signalement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SignalementCrudController extends AbstractCrudController
{
    /**
     * Définit l'entité gérée par ce contrôleur CRUD.
     */
    public static function getEntityFqcn(): string
    {
        return Signalement::class;
    }

    /**
     * Personnalise l'affichage de la liste des signalements.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Nom affiché dans l'interface EasyAdmin
            ->setEntityLabelInSingular('Signalement')
            ->setEntityLabelInPlural('Signalements')

            // Les signalements les plus récents apparaissent en premier
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    /**
     * Désactive certaines actions afin de préserver
     * l'intégrité des données.
     *
     * Un signalement doit uniquement être créé par un élève
     * et ne peut pas être supprimé depuis l'administration.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE);
    }

    /**
     * Configure les champs affichés dans le formulaire
     * et dans la liste des signalements.
     */
    public function configureFields(string $pageName): iterable
    {
        return [

            // Type de harcèlement déclaré
            TextField::new('type', 'Type'),

            // Zone où s'est produit l'incident
            TextField::new('zone', 'Zone'),

            // Niveau de gravité du signalement
            ChoiceField::new('severite', 'Sévérité')
                ->setChoices([
                    'Faible' => 'low',
                    'Moyenne' => 'medium',
                    'Élevée' => 'high',
                ]),

            // Description rédigée par l'élève
            TextareaField::new('description', 'Description'),

            // Permet d'indiquer si la situation est récurrente
            BooleanField::new('estRecurrent', 'Récurrent'),

            // Suivi du traitement du signalement
            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'Nouveau' => 'nouveau',
                    'En cours' => 'en_cours',
                    'Traité' => 'traite',
                ]),

            // Jeton utilisé pour garantir l'anonymat
            // Caché dans la liste pour éviter de surcharger l'interface
            TextField::new('anonymousToken', 'Token anonyme')
                ->hideOnIndex(),

            // Date de création du signalement
            DateTimeField::new('createdAt', 'Créé le'),

            // Date de la dernière modification
            // Utile uniquement dans le détail
            DateTimeField::new('updatedAt', 'Mis à jour le')
                ->hideOnIndex(),

            // Utilisateur ayant créé le signalement
            // Non modifiable depuis l'administration
            AssociationField::new('user', 'Utilisateur')
                ->hideOnForm(),

            // Établissement concerné
            AssociationField::new('etablissement', 'Établissement'),
        ];
    }
}