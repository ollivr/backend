<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See license.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\Designer\Domain\ValueObject\TemplateElement;

use JMS\Serializer\Annotation as JMS;

/**
 */
class UiTemplateElementProperty extends AbstractTemplateElementProperty
{
    public const VARIANT = 'ui';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $label;

    /**
     * @param string $label
     */
    public function __construct(string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getVariant(): string
    {
        return self::VARIANT;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}