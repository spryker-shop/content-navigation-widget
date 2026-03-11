<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Shared\ContentNavigationWidget;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface ContentNavigationWidgetConstants
{
    /**
     * Specification:
     * - Defines the revalidation time in seconds for navigation cache.
     * - Used when no specific validity dates are set on navigation nodes.
     *
     * @api
     */
    public const string NAVIGATION_REVALIDATION_TIME_IN_SECONDS = 'CONTENT_NAVIGATION_WIDGET:NAVIGATION_REVALIDATION_TIME_IN_SECONDS';
}
