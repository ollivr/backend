<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\ExporterShopware6\Infrastructure\Mapper\Product;

use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\ExporterShopware6\Domain\Entity\Shopware6Channel;
use Ergonode\ExporterShopware6\Infrastructure\Calculator\AttributeTranslationInheritanceCalculator;
use Ergonode\ExporterShopware6\Infrastructure\Client\Shopware6ProductMediaClient;
use Ergonode\ExporterShopware6\Infrastructure\Mapper\Shopware6ProductMapperInterface;
use Ergonode\ExporterShopware6\Infrastructure\Model\Product\Shopware6ProductMedia;
use Ergonode\ExporterShopware6\Infrastructure\Model\Shopware6Product;
use Ergonode\Multimedia\Domain\Repository\MultimediaRepositoryInterface;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\SharedKernel\Domain\Aggregate\MultimediaId;
use Webmozart\Assert\Assert;

/**
 */
class Shopware6ProductGalleryMapper implements Shopware6ProductMapperInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $repository;

    /**
     * @var AttributeTranslationInheritanceCalculator
     */
    private AttributeTranslationInheritanceCalculator $calculator;

    /**
     * @var MultimediaRepositoryInterface
     */
    private MultimediaRepositoryInterface $multimediaRepository;

    /**
     * @var Shopware6ProductMediaClient
     */
    private Shopware6ProductMediaClient $mediaClient;

    /**
     * @param AttributeRepositoryInterface              $repository
     * @param AttributeTranslationInheritanceCalculator $calculator
     * @param MultimediaRepositoryInterface             $multimediaRepository
     * @param Shopware6ProductMediaClient               $mediaClient
     */
    public function __construct(
        AttributeRepositoryInterface $repository,
        AttributeTranslationInheritanceCalculator $calculator,
        MultimediaRepositoryInterface $multimediaRepository,
        Shopware6ProductMediaClient $mediaClient
    ) {
        $this->repository = $repository;
        $this->calculator = $calculator;
        $this->multimediaRepository = $multimediaRepository;
        $this->mediaClient = $mediaClient;
    }

    /**
     * {@inheritDoc}
     */
    public function map(
        Shopware6Product $shopware6Product,
        AbstractProduct $product,
        Shopware6Channel $channel,
        ?Language $language = null
    ): Shopware6Product {
        if (null === $channel->getAttributeProductGallery()) {
            return $shopware6Product;
        }
        $attribute = $this->repository->load($channel->getAttributeProductGallery());

        Assert::notNull($attribute);

        if (false === $product->hasAttribute($attribute->getCode())) {
            return $shopware6Product;
        }

        $value = $product->getAttribute($attribute->getCode());
        $calculateValue = $this->calculator->calculate($attribute, $value, $language ?: $channel->getDefaultLanguage());
        if ($calculateValue) {
            $gallery = explode(',', $calculateValue);
            foreach ($gallery as $galleryValue) {
                $multimediaId = new MultimediaId($galleryValue);
                $this->getShopware6MultimediaId($multimediaId, $shopware6Product, $channel);
            }
        }

        return $shopware6Product;
    }

    /**
     * @param MultimediaId     $multimediaId
     * @param Shopware6Product $shopware6Product
     * @param Shopware6Channel $channel
     *
     * @return Shopware6Product
     */
    private function getShopware6MultimediaId(
        MultimediaId $multimediaId,
        Shopware6Product $shopware6Product,
        Shopware6Channel $channel
    ): Shopware6Product {
        $multimedia = $this->multimediaRepository->load($multimediaId);
        if ($multimedia) {
            $shopwareId = $this->mediaClient->findOrCreateMedia($channel, $multimedia);
            if ($shopwareId) {
                $shopware6Product->addMedia(new Shopware6ProductMedia(null, $shopwareId));
            }
        }

        return $shopware6Product;
    }
}
