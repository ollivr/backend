<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\Condition\Persistence\Dbal\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Ergonode\Condition\Domain\Query\ConditionSetQueryInterface;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Grid\DbalDataSet;
use Ergonode\SharedKernel\Domain\Aggregate\AttributeId;
use Ergonode\SharedKernel\Domain\Aggregate\ConditionSetId;

/**
 */
class DbalConditionSetQuery implements ConditionSetQueryInterface
{
    private const TABLE = 'condition_set';
    private const FIELDS = [
        't.id',
        't.code',
    ];

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getDataSet(Language $language): DbalDataSet
    {
        $query = $this->getQuery();
        $query->addSelect(sprintf('(name->>\'%s\') AS name', $language->getCode()));
        $query->addSelect(sprintf('(description->>\'%s\') AS description', $language->getCode()));

        $result = $this->connection->createQueryBuilder();
        $result->select('*');
        $result->from(sprintf('(%s)', $query->getSQL()), 't');

        return new DbalDataSet($result);
    }

    /**
     * @param AttributeId $attributeId
     *
     * @return array
     */
    public function findNumericConditionRelations(AttributeId $attributeId): array
    {
        $qb = $this->connection->createQueryBuilder();

        $records = $qb->select('id')
            ->from(self::TABLE)
            ->where($qb->expr()->eq('conditions->0->>\'type\'', ':type'))
            ->where($qb->expr()->eq('conditions->0->>\'attribute\'', ':attribute_id'))
            ->setParameter(':type', 'NUMERIC_ATTRIBUTE_VALUE_CONDITION')
            ->setParameter(':attribute_id', $attributeId->getValue())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $result = [];
        foreach ($records as $record) {
            $result[] = new ConditionSetId($record);
        }

        return $result;
    }

    /**
     * @return QueryBuilder
     */
    private function getQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select(self::FIELDS)
            ->from(self::TABLE, 't');
    }
}
