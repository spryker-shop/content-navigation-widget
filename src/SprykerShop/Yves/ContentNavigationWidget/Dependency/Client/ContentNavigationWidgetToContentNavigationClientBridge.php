<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentNavigationWidget\Dependency\Client;

use Generated\Shared\Transfer\ContentNavigationTypeTransfer;

class ContentNavigationWidgetToContentNavigationClientBridge implements ContentNavigationWidgetToContentNavigationClientInterface
{
    /**
     * @var \Spryker\Client\ContentNavigation\ContentNavigationClientInterface
     */
    protected $contentNavigationClient;

    /**
     * @param \Spryker\Client\ContentNavigation\ContentNavigationClientInterface $contentNavigationClient
     */
    public function __construct($contentNavigationClient)
    {
        $this->contentNavigationClient = $contentNavigationClient;
    }

    public function executeNavigationTypeByKey(string $contentKey, string $localeName): ?ContentNavigationTypeTransfer
    {
        return $this->contentNavigationClient->executeNavigationTypeByKey($contentKey, $localeName);
    }
}
