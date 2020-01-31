<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\ImporterMagento2\Infrastructure\Builder;

use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Attribute\Domain\Entity\AttributeId;
use Ergonode\ImporterMagento2\Infrastructure\Provider\AttributeProposalProvider;
use Ergonode\ImporterMagento2\Infrastructure\Configuration\ImportConfiguration;
use Ergonode\ImporterMagento2\Infrastructure\Configuration\Column\ProposalColumn;
use Ergonode\ImporterMagento2\Infrastructure\Configuration\Column\AttributeColumn;
use Ergonode\ImporterMagento2\Infrastructure\Configuration\Column\ConfigurationColumnInterface;

/**
 */
class ImportConfigurationBuilder
{
    /**
     * @var AttributeRepositoryInterface;
     */
    private AttributeRepositoryInterface $repository;

    /**
     * @var AttributeProposalProvider
     */
    private AttributeProposalProvider $provider;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param AttributeProposalProvider    $provider
     */
    public function __construct(AttributeRepositoryInterface $repository, AttributeProposalProvider $provider)
    {
        $this->repository = $repository;
        $this->provider = $provider;
    }

    /**
     * @param array $headers
     * @param array $lines
     *
     * @return ImportConfiguration
     *
     * @throws \Exception
     */
    public function propose(array $headers, array $lines): ImportConfiguration
    {
        $table = [];
        foreach ($headers as $header) {
            foreach ($lines as $line) {
                $table[$header][] = $line[$header];
            }
        }

        $result = new ImportConfiguration();
        foreach ($table as $name => $column) {
            $result->add($this->calculate($name, $column));
        }

        return $result;
    }

    /**
     * @param string $name
     * @param array  $values
     *
     * @return ConfigurationColumnInterface
     *
     * @throws \Exception
     */
    private function calculate(string $name, array $values): ConfigurationColumnInterface
    {
        $attributeCode = new AttributeCode($name);
        $attributeId = AttributeId::fromKey($attributeCode);

        $attribute = $this->repository->load($attributeId);

        if (null === $attribute) {
            $calculator = $this->provider->provide($name, $values);
            $attributeType = $calculator->getTypeProposal();

            return new ProposalColumn(
                $name,
                $attributeCode->getValue(),
                $attributeType
            );
        }

        return new AttributeColumn(
            $name,
            $attributeCode->getValue()
        );
    }
}