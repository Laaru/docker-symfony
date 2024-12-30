<?php

namespace App\EventListener;

use App\Entity\Color;
use App\Entity\Product;
use App\Entity\Store;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheInvalidationListener
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache
    ) {}

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->invalidateCache($args);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->invalidateCache($args);
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->invalidateCache($args);
    }

    private function invalidateCache(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Product || $entity instanceof Color || $entity instanceof Store) {
            $this->cache->invalidateTags(['products', 'colors', 'stores']);
        }
    }
}
