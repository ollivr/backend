<?php

/**
 * Copyright © Ergonode Sp. z o.o. All rights reserved.
 * See license.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\Channel\Persistence\Dbal\Query;

use Doctrine\DBAL\Connection;
use Ergonode\Channel\Domain\Query\SchedulerQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ChannelId;
use Doctrine\DBAL\DBALException;

/**
 */
class DbalSchedulerQuery implements SchedulerQueryInterface
{
    private const TABLE = 'exporter.scheduler';
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
     * @param \DateTime $time
     *
     * @return array
     */
    public function getReadyToRun(\DateTime $time): array
    {
        $sub = $this->connection->createQueryBuilder();
        $sub->select('id, active, start, last, current_timestamp AS actual')
            ->addSelect('current_timestamp - concat(hour, \':\', minute)::TIME AS expected')
            ->from(self::TABLE);

        $qb = $result = $this->connection->createQueryBuilder();
        $records = $qb
            ->select('*')
            ->from(sprintf('(%s)', $sub->getSQL()), 't')
            ->andWhere($qb->expr()->lte('start', 'actual'))
            ->andWhere($qb->expr()->eq('active', ':active'))
            ->setParameter(':active', true, \PDO::PARAM_BOOL)
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('last'),
                    $qb->expr()->lte('last', 'expected')
                )
            )
            ->execute()
            ->fetchAll();

        $result = [];
        foreach ($records as $record) {
            $result[] = new ChannelId($record['id']);
        }

        return $result;
    }

    /**
     * @param ChannelId $id
     * @param \DateTime $time
     *
     * @throws DBALException
     */
    public function markAsRun(ChannelId $id, \DateTime $time): void
    {
        $this->connection->update(
            self::TABLE,
            [
                'last' => $time->format('Y-m-d H:i:s'),
            ],
            [
                'id' => $id->getValue(),
            ]
        );
    }
}
