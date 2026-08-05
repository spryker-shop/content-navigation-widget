<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentNavigationWidget;

use Spryker\Client\Store\StoreClientInterface;
use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToContentNavigationClientBridge;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToNavigationStorageClientBridge;

/**
 * @method \SprykerShop\Yves\ContentNavigationWidget\ContentNavigationWidgetConfig getConfig()
 */
class ContentNavigationWidgetDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string CLIENT_CONTENT_NAVIGATION = 'CLIENT_CONTENT_NAVIGATION';

    public const string CLIENT_NAVIGATION_STORAGE = 'CLIENT_NAVIGATION_STORAGE';

    public const string CLIENT_STORE = 'CLIENT_STORE';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addContentNavigationClient($container);
        $container = $this->addNavigationStorageClient($container);
        $container = $this->addStoreClient($container);

        return $container;
    }

    protected function addContentNavigationClient(Container $container): Container
    {
        $container->set(static::CLIENT_CONTENT_NAVIGATION, function (Container $container) {
            return new ContentNavigationWidgetToContentNavigationClientBridge(
                $container->getLocator()->contentNavigation()->client(),
            );
        });

        return $container;
    }

    protected function addNavigationStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_NAVIGATION_STORAGE, function (Container $container) {
            return new ContentNavigationWidgetToNavigationStorageClientBridge(
                $container->getLocator()->navigationStorage()->client(),
            );
        });

        return $container;
    }

    protected function addStoreClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORE, function (Container $container): StoreClientInterface {
            return $container->getLocator()->store()->client();
        });

        return $container;
    }
}
