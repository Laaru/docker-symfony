<?php

namespace App\Service\Catalog;

use App\Entity\DTO\CatalogRequestDTO;
use App\Repository\ProductRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CatalogProductService
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly ProductRepository $productRepository
    ) {}

    /**
     * @throws InvalidArgumentException
     *
     * @return array<mixed>
     */
    public function getPaginatedProducts(CatalogRequestDTO $catalogRequestDTO): array
    {

        $cacheKey = $this->getCacheKeyFromDTO($catalogRequestDTO);

        return $this->cache->get($cacheKey, function (ItemInterface $cacheItem) use ($catalogRequestDTO) {
            $cacheItem->tag(['products', 'colors', 'stores']);

            $queryBuilder = $this->productRepository->createQueryBuilder('p');
            if (null !== $catalogRequestDTO->priceMin) {
                $queryBuilder
                    ->andWhere('p.basePrice >= :priceMin')
                    ->setParameter('priceMin', $catalogRequestDTO->priceMin);
            }
            if (null !== $catalogRequestDTO->priceMax) {
                $queryBuilder
                    ->andWhere('p.basePrice <= :priceMax')
                    ->setParameter('priceMax', $catalogRequestDTO->priceMax);
            }
            if (null !== $catalogRequestDTO->colorId) {
                $queryBuilder
                    ->andWhere('p.color = :color')
                    ->setParameter('color', $catalogRequestDTO->colorId);
            }
            if (null !== $catalogRequestDTO->storeId) {
                $queryBuilder
                    ->join('p.stores', 's')
                    ->andWhere('s.id = :store')
                    ->setParameter('store', $catalogRequestDTO->storeId);
            }

            $queryBuilder->orderBy(
                'p.' . $catalogRequestDTO->sort,
                'asc' === $catalogRequestDTO->order ? 'ASC' : 'DESC'
            );

            $limit = 30;
            $queryBuilder
                ->setFirstResult(($catalogRequestDTO->page - 1) * $limit)
                ->setMaxResults($limit);

            $paginator = new Paginator($queryBuilder);
            $totalItems = count($paginator);
            $totalPages = ceil($totalItems / $limit);

            $products = iterator_to_array($paginator->getIterator());

            return [
                'products' => $this->productRepository->normalizeCollection($products, 'list'),
                'pagination' => [
                    'total' => $totalItems,
                    'page' => $catalogRequestDTO->page,
                    'pages' => $totalPages,
                ],
            ];
        });
    }

    private function getCacheKeyFromDTO(object $dto): string
    {
        $properties = [];
        foreach (get_object_vars($dto) as $key => $value) {
            $properties[] = $key . '=' . (null === $value ? 'null' : (string) $value);
        }
        $dataString = implode('&', $properties);

        return md5($dataString);
    }
}
