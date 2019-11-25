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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionLoader\ContentDimensionLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\Message\LoadContentMessage;
use Sulu\Bundle\ContentBundle\Content\Application\MessageHandler\LoadContentMessageHandler;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class LoadContentMessageHandlerTest extends TestCase
{
    protected function createLoadContentMessageHandlerInstance(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionLoaderInterface $contentDimensionLoader,
        ViewFactoryInterface $viewFactory,
        ApiViewResolverInterface $viewResolver
    ): LoadContentMessageHandler {
        return new LoadContentMessageHandler(
            $dimensionRepository,
            $contentDimensionLoader,
            $viewFactory,
            $viewResolver
        );
    }

    protected function createContentInstance(): ContentInterface
    {
        return new class() extends AbstractContent {
            public static function getResourceKey(): string
            {
                return 'example';
            }

            public function createDimension(DimensionInterface $dimension): ContentDimensionInterface
            {
                throw new \RuntimeException('Should not be called in a unit test.');
            }

            public function getId()
            {
                return null;
            }
        };
    }

    public function testInvoke(): void
    {
        $dimension1 = $this->prophesize(DimensionInterface::class);
        $dimension1->getId()->willReturn('123-456');
        $dimension2 = $this->prophesize(DimensionInterface::class);
        $dimension2->getId()->willReturn('456-789');

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1->reveal());
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2->reveal());

        $content = $this->prophesize(ContentInterface::class);

        $attributes = [
            'locale' => 'de',
        ];

        $message = new LoadContentMessage($content->reveal(), $attributes);

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findByAttributes($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ]);

        $contentDimensionLoader = $this->prophesize(ContentDimensionLoaderInterface::class);
        $contentDimensionLoader->load($content->reveal(), $dimensionCollection)->willReturn($contentDimensionCollection);
        $contentView = $this->prophesize(ContentViewInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);
        $viewFactory->create($contentDimensionCollection)->willReturn($contentView->reveal())->shouldBeCalled();

        $viewResolver = $this->prophesize(ApiViewResolverInterface::class);
        $viewResolver->resolve($contentView->reveal())->willReturn(['resolved' => 'data'])->shouldBeCalled();

        $createContentMessageHandler = $this->createLoadContentMessageHandlerInstance(
            $dimensionRepository->reveal(),
            $contentDimensionLoader->reveal(),
            $viewFactory->reveal(),
            $viewResolver->reveal()
        );

        $this->assertSame(['resolved' => 'data'], $createContentMessageHandler->__invoke($message));
    }
}