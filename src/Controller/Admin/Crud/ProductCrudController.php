<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Product;
use App\Repository\ColorRepository;
use App\Repository\StoreRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Bundle\SecurityBundle\Security;

class ProductCrudController extends AbstractCrudController
{
    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var array $fields */
        $fields = parent::configureFields($pageName);

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $fields = array_filter($fields, function ($field) {
                return !in_array($field->getAsDto()->getProperty(), ['createdAt', 'updatedAt']);
            });

            $fields[] = AssociationField::new('stores')
                ->setFormTypeOption('choice_label', 'id')
                ->setFormTypeOption('query_builder', function (StoreRepository $repo) {
                    return $repo->createQueryBuilder('p');
                });
            $fields[] = AssociationField::new('color', 'Color')
                ->setFormTypeOption('choice_label', 'name')
                ->setFormTypeOption('query_builder', function (ColorRepository $repo) {
                    return $repo->createQueryBuilder('p');
                });
        }

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            $fields[] = AssociationField::new('stores')
                ->onlyOnDetail()
                ->formatValue(function ($value) {
                    return implode(', ', $value->map(
                        fn ($store) => sprintf('<a href="/admin/store/%d">%d</a>', $store->getId(), $store->getId())
                    )->toArray());
                });
            $fields[] = AssociationField::new('color')
                ->setLabel('Color')
                ->formatValue(static function ($color) {
                    return $color ? $color->getName() : 'No color';
                });
        }

        return $fields;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $showAction = Action::new('show', 'View')
            ->linkToCrudAction('detail')
            ->setCssClass('btn btn-info');

        $actions->add(Crud::PAGE_INDEX, $showAction);

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $actions = $actions
                ->remove(Crud::PAGE_INDEX, 'delete')
                ->remove(Crud::PAGE_DETAIL, 'delete');
        }

        return $actions;
    }
}
