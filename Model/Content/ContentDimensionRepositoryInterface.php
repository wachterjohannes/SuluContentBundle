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

namespace Sulu\Bundle\ContentBundle\Model\Content;

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

interface ContentDimensionRepositoryInterface
{
    public function create(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ContentDimensionInterface;

    public function findOrCreate(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ContentDimensionInterface;

    public function findByResource(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ?ContentDimensionInterface;

    /**
     * @param DimensionIdentifierInterface[] $dimensionIdentifiers
     *
     * @return ContentDimensionInterface[]
     */
    public function findByDimensionIdentifiers(string $resourceKey, string $resourceId, array $dimensionIdentifiers): array;
}
