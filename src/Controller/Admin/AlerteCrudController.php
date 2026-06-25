<?php

namespace App\Controller\Admin;

use App\Entity\Alerte;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Doctrine\ORM\QueryBuilder;


class AlerteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Alerte::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Alerte')
            ->setEntityLabelInPlural('Alertes')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('severite', 'Sévérité')
                ->setChoices([
                    'Faible' => 'low',
                    'Moyenne' => 'medium',
                    'Élevée' => 'high',
                ]),

            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'Nouveau' => 'nouveau',
                    'En cours' => 'en_cours',
                    'Traité' => 'traite',
                ]),

            TextareaField::new('noteInterne', 'Note interne'),

            DateTimeField::new('createdAt', 'Créée le')
                ->hideOnForm(),

            DateTimeField::new('treatedAt', 'Traitée le'),

            AssociationField::new('signalement', 'Signalement')
                ->hideOnForm(),

            AssociationField::new('treatedBy', 'Traité par')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder
                        ->andWhere('entity.role IN (:roles)')
                        ->setParameter('roles', ['ADMIN', 'REFERENT']);
                }),
        ];
    }
}
