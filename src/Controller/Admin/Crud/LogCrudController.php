<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Log;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class LogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
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

        return $actions
            ->remove(Crud::PAGE_INDEX, 'edit')
            ->remove(Crud::PAGE_INDEX, 'delete')
            ->remove(Crud::PAGE_DETAIL, 'edit')
            ->remove(Crud::PAGE_DETAIL, 'delete');
    }
}
