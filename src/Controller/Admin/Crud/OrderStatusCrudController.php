<?php

namespace App\Controller\Admin\Crud;

use App\Entity\OrderStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Bundle\SecurityBundle\Security;

class OrderStatusCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderStatus::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function configureActions(Actions $actions): Actions
    {
        $showAction = Action::new('show', 'View')
            ->linkToCrudAction('detail')
            ->setCssClass('btn btn-info');

        $actions->add(Crud::PAGE_INDEX, $showAction);

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $actions = $actions
                ->remove(Crud::PAGE_INDEX, 'edit')
                ->remove(Crud::PAGE_DETAIL, 'edit');
        }
        $actions = $actions
            ->remove(Crud::PAGE_INDEX, 'delete')
            ->remove(Crud::PAGE_DETAIL, 'delete');

        return $actions;
    }
}
