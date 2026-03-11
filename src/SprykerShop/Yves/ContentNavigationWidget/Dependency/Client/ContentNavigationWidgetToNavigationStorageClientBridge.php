<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentNavigationWidget\Dependency\Client;

use Generated\Shared\Transfer\NavigationStorageTransfer;

class ContentNavigationWidgetToNavigationStorageClientBridge implements ContentNavigationWidgetToNavigationStorageClientInterface
{
    /**
     * @var \Spryker\Client\NavigationStorage\NavigationStorageClientInterface
     */
    protected $navigationStorageClient;

    /**
     * @param \Spryker\Client\NavigationStorage\NavigationStorageClientInterface $navigationStorageClient
     */
    public function __construct($navigationStorageClient)
    {
        $this->navigationStorageClient = $navigationStorageClient;
    }

    /**
     * @param string $navigationKey
     * @param string $localeName
     *
     * @return \Generated\Shared\Transfer\NavigationStorageTransfer|null
     */
    public function findNavigationTreeByKey($navigationKey, $localeName)
    {
        return $this->navigationStorageClient->findNavigationTreeByKey($navigationKey, $localeName);
    }

    public function saveNavigationTree(NavigationStorageTransfer $navigationStorageTransfer, string $navigationKey, string $localeName): void
    {
        $this->navigationStorageClient->saveNavigationTree($navigationStorageTransfer, $navigationKey, $localeName);
    }
}
