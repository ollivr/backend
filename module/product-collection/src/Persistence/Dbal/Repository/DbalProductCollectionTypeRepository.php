<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\ProductCollection\Persistence\Dbal\Repository;

use Ergonode\EventSourcing\Domain\AbstractAggregateRoot;
use Ergonode\ProductCollection\Domain\Entity\ProductCollectionType;
use Ergonode\SharedKernel\Domain\Aggregate\ProductCollectionTypeId;
use Ergonode\ProductCollection\Domain\Event\ProductCollectionTypeDeletedEvent;
use Ergonode\ProductCollection\Domain\Repository\ProductCollectionTypeRepositoryInterface;
use Ergonode\EventSourcing\Infrastructure\Manager\EventStoreManager;
use Webmozart\Assert\Assert;

/**
 */
class DbalProductCollectionTypeRepository implements ProductCollectionTypeRepositoryInterface
{
    /**
     * @var EventStoreManager
     */
    private EventStoreManager $manager;

    /**
     * @param EventStoreManager $manager
     */
    public function __construct(EventStoreManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(ProductCollectionTypeId $id): bool
    {
        return $this->manager->exists($id);
    }

    /**
     * @param ProductCollectionTypeId $id
     *
     * @return AbstractAggregateRoot|null
     *
     * @throws \ReflectionException
     */
    public function load(ProductCollectionTypeId $id): ?AbstractAggregateRoot
    {
        $aggregate = $this->manager->load($id);
        Assert::nullOrIsInstanceOf($aggregate, ProductCollectionType::class);

        return $aggregate;
    }

    /**
     * {@inheritDoc}
     */
    public function save(AbstractAggregateRoot $aggregateRoot): void
    {
        $this->manager->save($aggregateRoot);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    public function delete(AbstractAggregateRoot $aggregateRoot): void
    {
        $aggregateRoot->apply(new ProductCollectionTypeDeletedEvent($aggregateRoot->getId()));
        $this->save($aggregateRoot);

        $this->manager->delete($aggregateRoot);
    }
}
