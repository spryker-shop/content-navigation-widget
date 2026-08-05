<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\ContentNavigationWidget\Twig;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ContentNavigationTypeTransfer;
use Generated\Shared\Transfer\NavigationNodeStorageTransfer;
use Generated\Shared\Transfer\NavigationStorageTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Client\Store\StoreClientInterface;
use SprykerShop\Yves\ContentNavigationWidget\ContentNavigationWidgetConfig;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToContentNavigationClientInterface;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToNavigationStorageClientInterface;
use SprykerShop\Yves\ContentNavigationWidget\Twig\Calculator\CacheRevalidationTimeCalculator;
use SprykerShop\Yves\ContentNavigationWidget\Twig\ContentNavigationTwigFunctionProvider;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShop
 * @group Yves
 * @group ContentNavigationWidget
 * @group Twig
 * @group ContentNavigationTwigFunctionProviderTest
 * Add your own group annotations below this line
 */
class ContentNavigationTwigFunctionProviderTest extends Unit
{
    protected const string LOCALE_NAME = 'en_US';

    protected const string STORE_NAME_DE = 'DE';

    protected const string STORE_NAME_US = 'US';

    protected const string CONTENT_KEY = 'cn-1';

    protected const string NAVIGATION_KEY = 'main-navigation';

    protected const string TEMPLATE_IDENTIFIER = 'tree-inline';

    protected const string TEMPLATE_PATH = '@ContentNavigationWidget/views/navigation/tree-inline.twig';

    protected const string RENDERED_CONTENT_FORMAT = 'rendered-for-%s';

    protected const string RENDERED_CONTENT_CACHE_KEY_FORMAT = '%s:%s';

    protected string $currentStoreName = self::STORE_NAME_DE;

    public function testRenderedNavigationCacheIsNotSharedBetweenStores(): void
    {
        // Arrange
        $this->currentStoreName = static::STORE_NAME_DE;
        $navigationStorageTransfer = $this->createNavigationStorageTransfer();
        $contentNavigationTwigFunction = $this->createContentNavigationTwigFunction($navigationStorageTransfer);

        // Act
        $renderedContentForStoreDe = $contentNavigationTwigFunction(static::CONTENT_KEY, static::TEMPLATE_IDENTIFIER);
        $this->currentStoreName = static::STORE_NAME_US;
        $renderedContentForStoreUs = $contentNavigationTwigFunction(static::CONTENT_KEY, static::TEMPLATE_IDENTIFIER);

        // Assert
        $this->assertSame(sprintf(static::RENDERED_CONTENT_FORMAT, static::STORE_NAME_DE), $renderedContentForStoreDe);
        $this->assertSame(sprintf(static::RENDERED_CONTENT_FORMAT, static::STORE_NAME_US), $renderedContentForStoreUs);
        $this->assertRenderedContentIsCachedPerStore($navigationStorageTransfer);
    }

    protected function assertRenderedContentIsCachedPerStore(NavigationStorageTransfer $navigationStorageTransfer): void
    {
        $renderedContent = $navigationStorageTransfer->getRenderedContent();

        $this->assertArrayHasKey($this->getRenderedContentCacheKeyForStore(static::STORE_NAME_DE), $renderedContent);
        $this->assertArrayHasKey($this->getRenderedContentCacheKeyForStore(static::STORE_NAME_US), $renderedContent);
        $this->assertSame(
            sprintf(static::RENDERED_CONTENT_FORMAT, static::STORE_NAME_DE),
            $renderedContent[$this->getRenderedContentCacheKeyForStore(static::STORE_NAME_DE)],
        );
        $this->assertSame(
            sprintf(static::RENDERED_CONTENT_FORMAT, static::STORE_NAME_US),
            $renderedContent[$this->getRenderedContentCacheKeyForStore(static::STORE_NAME_US)],
        );
    }

    protected function getRenderedContentCacheKeyForStore(string $storeName): string
    {
        return sprintf(static::RENDERED_CONTENT_CACHE_KEY_FORMAT, $storeName, static::TEMPLATE_PATH);
    }

    protected function createContentNavigationTwigFunction(NavigationStorageTransfer $navigationStorageTransfer): callable
    {
        $contentNavigationWidgetConfigMock = $this->createContentNavigationWidgetConfigMock();

        $contentNavigationTwigFunctionProvider = new ContentNavigationTwigFunctionProvider(
            $this->createTwigEnvironmentMock(),
            static::LOCALE_NAME,
            $this->createContentNavigationClientMock(),
            $this->createNavigationStorageClientMock($navigationStorageTransfer),
            $contentNavigationWidgetConfigMock,
            new CacheRevalidationTimeCalculator($contentNavigationWidgetConfigMock),
            $this->createStoreClientMock(),
        );

        return $contentNavigationTwigFunctionProvider->getFunction();
    }

    protected function createNavigationStorageTransfer(): NavigationStorageTransfer
    {
        return (new NavigationStorageTransfer())
            ->setIsActive(true)
            ->setKey(static::NAVIGATION_KEY)
            ->addNodes((new NavigationNodeStorageTransfer())->setIsActive(true)->setTitle('Category A'))
            ->addNodes((new NavigationNodeStorageTransfer())->setIsActive(true)->setTitle('Category B'));
    }

    protected function createTwigEnvironmentMock(): Environment
    {
        $twigEnvironmentMock = $this->getMockBuilder(Environment::class)
            ->setConstructorArgs([new FilesystemLoader()])
            ->onlyMethods(['render'])
            ->getMock();
        $twigEnvironmentMock->method('render')
            ->willReturnCallback(fn (): string => sprintf(static::RENDERED_CONTENT_FORMAT, $this->currentStoreName));

        return $twigEnvironmentMock;
    }

    protected function createContentNavigationClientMock(): ContentNavigationWidgetToContentNavigationClientInterface
    {
        $contentNavigationClientMock = $this->createMock(ContentNavigationWidgetToContentNavigationClientInterface::class);
        $contentNavigationClientMock->method('executeNavigationTypeByKey')
            ->willReturn((new ContentNavigationTypeTransfer())->setNavigationKey(static::NAVIGATION_KEY));

        return $contentNavigationClientMock;
    }

    protected function createNavigationStorageClientMock(
        NavigationStorageTransfer $navigationStorageTransfer
    ): ContentNavigationWidgetToNavigationStorageClientInterface {
        // The same transfer instance is returned for both stores to simulate the cache blob shared per navigation-key and locale.
        $navigationStorageClientMock = $this->createMock(ContentNavigationWidgetToNavigationStorageClientInterface::class);
        $navigationStorageClientMock->method('findNavigationTreeByKey')
            ->willReturn($navigationStorageTransfer);

        return $navigationStorageClientMock;
    }

    protected function createContentNavigationWidgetConfigMock(): ContentNavigationWidgetConfig
    {
        $contentNavigationWidgetConfigMock = $this->createMock(ContentNavigationWidgetConfig::class);
        $contentNavigationWidgetConfigMock->method('isNavigationCacheEnabled')->willReturn(true);
        $contentNavigationWidgetConfigMock->method('getAvailableTemplateList')
            ->willReturn([static::TEMPLATE_IDENTIFIER => static::TEMPLATE_PATH]);
        $contentNavigationWidgetConfigMock->method('getDefaultNavigationRevalidationTimeInSeconds')->willReturn(3600);

        return $contentNavigationWidgetConfigMock;
    }

    protected function createStoreClientMock(): StoreClientInterface
    {
        $storeClientMock = $this->createMock(StoreClientInterface::class);
        $storeClientMock->method('getCurrentStore')
            ->willReturnCallback(fn (): StoreTransfer => (new StoreTransfer())->setName($this->currentStoreName));

        return $storeClientMock;
    }
}
