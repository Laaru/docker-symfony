<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Crud\UserCrudController;
use App\Entity\Basket;
use App\Entity\Color;
use App\Entity\Log;
use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Entity\Product;
use App\Entity\Store;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('App');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home')
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Users', 'fa-solid fa-user', User::class)
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Stores', 'fa-solid fa-shop', Store::class)
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Products', 'fa-solid fa-boxes-stacked', Product::class)
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Product Colors', 'fa-solid fa-droplet', Color::class)
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Orders', 'fa-solid fa-clipboard', Order::class)
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Order Statuses', 'fa-solid fa-bars-progress', OrderStatus::class)
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Baskets', 'fa-solid fa-cart-shopping', Basket::class)
                ->setPermission('ROLE_MANAGER'),
            MenuItem::linkToCrud('Logs', 'fas fa-list', Log::class)
                ->setPermission('ROLE_ADMIN'),
        ];
    }
}
