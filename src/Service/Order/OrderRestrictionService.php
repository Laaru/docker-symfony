<?php

namespace App\Service\Order;

use App\Entity\Basket;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderRestrictionService
{
    public function checkRestriction(?Basket $basket = null): void
    {
        if (!$basket || $basket->isEmpty()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Basket is empty');
        }

        if ($basket->getTotalItemsCount() > 20) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The order cannot contain more than 20 items.');
        }
    }
}
