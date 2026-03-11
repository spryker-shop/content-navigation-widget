<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentNavigationWidget;

use Spryker\Shared\Twig\TwigFunctionProvider;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToContentNavigationClientInterface;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToNavigationStorageClientInterface;
use SprykerShop\Yves\ContentNavigationWidget\Twig\Calculator\CacheRevalidationTimeCalculator;
use SprykerShop\Yves\ContentNavigationWidget\Twig\ContentNavigationTwigFunctionProvider;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * @method \SprykerShop\Yves\ContentNavigationWidget\ContentNavigationWidgetConfig getConfig()
 */
class ContentNavigationWidgetFactory extends AbstractFactory
{
    public function createContentNavigationTwigFunctionProvider(Environment $twig, string $localeName): TwigFunctionProvider
    {
        return new ContentNavigationTwigFunctionProvider(
            $twig,
            $localeName,
            $this->getContentNavigationClient(),
            $this->getNavigationStorageClient(),
            $this->getConfig(),
            $this->createCacheRevalidationTimeCalculator(),
        );
    }

    public function createContentNavigationTwigFunction(Environment $twig, string $localeName): TwigFunction
    {
        $functionProvider = $this->createContentNavigationTwigFunctionProvider($twig, $localeName);

        return new TwigFunction(
            $functionProvider->getFunctionName(),
            $functionProvider->getFunction(),
            $functionProvider->getOptions(),
        );
    }

    public function createCacheRevalidationTimeCalculator(): CacheRevalidationTimeCalculator
    {
        return new CacheRevalidationTimeCalculator(
            $this->getConfig(),
        );
    }

    public function getContentNavigationClient(): ContentNavigationWidgetToContentNavigationClientInterface
    {
        return $this->getProvidedDependency(ContentNavigationWidgetDependencyProvider::CLIENT_CONTENT_NAVIGATION);
    }

    public function getNavigationStorageClient(): ContentNavigationWidgetToNavigationStorageClientInterface
    {
        return $this->getProvidedDependency(ContentNavigationWidgetDependencyProvider::CLIENT_NAVIGATION_STORAGE);
    }
}
