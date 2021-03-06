<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\Category\Domain\Provider\Dictionary;

use Ergonode\Category\Application\Provider\CategoryTypeProvider;
use Ergonode\Core\Domain\ValueObject\Language;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 */
class CategoryTypeDictionaryProvider
{
    /**
     * @var CategoryTypeProvider
     */
    private CategoryTypeProvider $provider;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param CategoryTypeProvider $provider
     * @param TranslatorInterface  $translator
     */
    public function __construct(CategoryTypeProvider $provider, TranslatorInterface $translator)
    {
        $this->provider = $provider;
        $this->translator = $translator;
    }


    /**
     * @param Language $language
     *
     * @return array
     */
    public function getDictionary(Language $language): array
    {
        $result = [];
        foreach ($this->provider->provide() as $type) {
            $result[$type] = $this->translator->trans($type, [], 'category', $language->getCode());
        }

        return $result;
    }
}
