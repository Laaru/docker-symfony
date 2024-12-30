<?php

namespace App\Entity\DTO\Collection;

use App\Entity\DTO\ProductUpdateDTO;

class ProductDTOCollection implements \IteratorAggregate
{
    private array $products;

    public function __construct(ProductUpdateDTO ...$products)
    {
        $this->products = $products;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->products);
    }

    public function count(): int
    {
        return count($this->products);
    }
}
