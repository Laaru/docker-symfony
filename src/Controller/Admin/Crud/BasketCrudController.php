<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Basket;
use App\Form\AdminPanel\BasketItemType;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Bundle\SecurityBundle\Security;

class BasketCrudController extends AbstractCrudController
{
    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return Basket::class;
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

    public function configureFields(string $pageName): iterable
    {
        /** @var array $fields */
        $fields = parent::configureFields($pageName);

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $fields = array_filter($fields, function ($field) {
                return !in_array($field->getAsDto()->getProperty(), ['createdAt', 'updatedAt']);
            });
            $fields[] = AssociationField::new('userRelation', 'user')
                ->setFormTypeOption('choice_label', 'phone')
                ->setFormTypeOption('query_builder', function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u');
                });
            $fields[] = CollectionField::new('items', 'Order Items')
                ->setEntryType(BasketItemType::class)
                ->setFormTypeOption('by_reference', false)
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOption('entry_options', [
                    'label' => false,
                ]);
        }

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            $fields[] = AssociationField::new('userRelation', 'user')
                ->formatValue(fn ($user) => $user->getPhone());
            $fields[] = AssociationField::new('items')
                ->onlyOnDetail()
                ->formatValue(function ($value) {
                    return implode(', ', $value->map(
                        fn ($orderItem) => sprintf(
                            '<a href="/admin/product/%d">%d</a>',
                            $orderItem->getProduct()->getId(),
                            $orderItem->getProduct()->getId()
                        )
                    )->toArray());
                });
        }

        return $fields;
    }
}
