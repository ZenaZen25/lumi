<?php

namespace App\Controller\Admin;

use App\Entity\Signalement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;


class SignalementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Signalement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Signalement')
            ->setEntityLabelInPlural('Signalements')
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
            TextField::new('type', 'Type'),
            TextField::new('zone', 'Zone'),
            ChoiceField::new('severite', 'Sévérité')
                ->setChoices([
                    'Faible' => 'low',
                    'Moyenne' => 'medium',
                    'Élevée' => 'high',
                ]),
            TextareaField::new('description', 'Description'),
            BooleanField::new('estRecurrent', 'Récurrent'),
            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'Nouveau' => 'nouveau',
                    'En cours' => 'en_cours',
                    'Traité' => 'traite',
                ]),
            TextField::new('anonymousToken', 'Token anonyme')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Créé le'),
            DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnIndex(),
            AssociationField::new('user', 'Utilisateur')->hideOnForm(),
            AssociationField::new('etablissement', 'Établissement'),
        ];
    }
}
