<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentNavigationWidget\Dependency\Client;

use Generated\Shared\Transfer\NavigationStorageTransfer;

interface ContentNavigationWidgetToNavigationStorageClientInterface
{
    /**
     * @param string $navigationKey
     * @param string $localeName
     *
     * @return \Generated\Shared\Transfer\NavigationStorageTransfer|null
     */
    public function findNavigationTreeByKey($navigationKey, $localeName);

    public function saveNavigationTree(NavigationStorageTransfer $navigationStorageTransfer, string $navigationKey, string $localeName): void;
}
