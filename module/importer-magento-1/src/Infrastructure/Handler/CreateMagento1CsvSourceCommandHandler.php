<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\ImporterMagento1\Infrastructure\Handler;

use Ergonode\Importer\Domain\Repository\SourceRepositoryInterface;
use Ergonode\ImporterMagento1\Domain\Command\CreateMagento1CsvSourceCommand;
use Ergonode\ImporterMagento1\Domain\Entity\Magento1CsvSource;

/**
 */
class CreateMagento1CsvSourceCommandHandler
{
    /**
     * @var SourceRepositoryInterface
     */
    private SourceRepositoryInterface $repository;

    /**
     * @param SourceRepositoryInterface $repository
     */
    public function __construct(SourceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CreateMagento1CsvSourceCommand $command
     *
     * @throws \Exception
     */
    public function __invoke(CreateMagento1CsvSourceCommand $command)
    {
        $source = new Magento1CsvSource(
            $command->getId(),
            $command->getName(),
            $command->getDefaultLanguage(),
            $command->getLanguages(),
            $command->getAttributes(),
            $command->getImport(),
            $command->getHost()
        );

        $this->repository->save($source);
    }
}
