<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\ExporterShopware6\Persistence\Dbal\Query;

use Doctrine\DBAL\Connection;
use Ergonode\ExporterShopware6\Domain\Query\Shopware6MultimediaQueryInterface;
use Ergonode\SharedKernel\Domain\Aggregate\ChannelId;
use Ergonode\SharedKernel\Domain\Aggregate\MultimediaId;

/**
 */
class DbalShopware6MultimediaQuery implements Shopware6MultimediaQueryInterface
{
    private const TABLE = 'exporter.shopware6_multimedia';
    private const FIELDS = [
        'channel_id',
        'multimedia_id',
        'shopware6_id',
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
     * @param ChannelId $channel
     * @param string    $shopwareId
     *
     * @return MultimediaId|null
     */
    public function loadByShopwareId(ChannelId $channel, string $shopwareId): ?MultimediaId
    {
        $query = $this->connection->createQueryBuilder();
        $record = $query
            ->select(self::FIELDS)
            ->from(self::TABLE, 'm')
            ->where($query->expr()->eq('channel_id', ':channelId'))
            ->setParameter(':channelId', $channel->getValue())
            ->andWhere($query->expr()->eq('shopware6_id', ':shopware6Id'))
            ->setParameter(':shopware6Id', $shopwareId)
            ->execute()
            ->fetch();

        if ($record) {
            return new MultimediaId($record['multimedia_id']);
        }

        return null;
    }

    /**
     * @param ChannelId          $channel
     * @param \DateTimeImmutable $dateTime
     */
    public function cleanData(ChannelId $channel, \DateTimeImmutable $dateTime): void
    {
        $query = $this->connection->createQueryBuilder();
        $query->delete(self::TABLE, 'm')
            ->where($query->expr()->eq('m.channel_id', ':channelId'))
            ->setParameter(':channelId', $channel->getValue())
            ->andWhere($query->expr()->lt('m.update_at', ':updateAt'))
            ->setParameter(':updateAt', $dateTime->format('Y-m-d H:i:s'))
            ->execute();
    }
}
