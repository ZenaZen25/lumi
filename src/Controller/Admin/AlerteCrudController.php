<?php

namespace App\Controller\Admin;

use App\Entity\Alerte;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class AlerteCrudController extends AbstractCrudController
{
    /**
     * Définit l'entité gérée par ce contrôleur CRUD.
     */
    public static function getEntityFqcn(): string
    {
        return Alerte::class;
    }

    /**
     * Personnalise l'affichage général des alertes.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Alerte')
            ->setEntityLabelInPlural('Alertes')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    /**
     * Désactive la création et la suppression manuelle.
     *
     * Les alertes sont générées automatiquement
     * à partir des signalements ou du chat ECHO.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE);
    }

    /**
     * Configure les champs affichés dans EasyAdmin.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            // Niveau de gravité de l'alerte
            ChoiceField::new('severite', 'Sévérité')
                ->setChoices([
                    'Faible' => 'low',
                    'Moyenne' => 'medium',
                    'Élevée' => 'high',
                ]),

            // Statut de traitement de l'alerte
            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'Nouveau' => 'nouveau',
                    'En cours' => 'en_cours',
                    'Traité' => 'traite',
                ]),

            // Note interne ajoutée par le référent ou l'administrateur
            TextareaField::new('noteInterne', 'Note interne'),

            // Date de création automatique de l'alerte
            DateTimeField::new('createdAt', 'Créée le')
                ->hideOnForm(),

            // Date à laquelle l'alerte a été traitée
            DateTimeField::new('treatedAt', 'Traitée le'),

            // Signalement lié à l'alerte
            // Non modifiable depuis le formulaire pour garder la cohérence
            AssociationField::new('signalement', 'Signalement')
                ->hideOnForm(),

            // Utilisateur ayant traité l'alerte
            // La liste est filtrée pour afficher uniquement
            // les administrateurs et les référents.
            AssociationField::new('treatedBy', 'Traité par')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder
                        ->andWhere('entity.role IN (:roles)')
                        ->setParameter('roles', ['ADMIN', 'REFERENT']);
                }),
        ];
    }
}