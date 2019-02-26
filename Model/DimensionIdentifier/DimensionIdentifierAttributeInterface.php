<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Model\DimensionIdentifier;

interface DimensionIdentifierAttributeInterface
{
    public function setDimensionIdentifier(DimensionIdentifierInterface $dimension): self;

    public function getDimensionIdentifier(): DimensionIdentifierInterface;

    public function getKey(): string;

    public function getValue(): string;
}