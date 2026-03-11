<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentNavigationWidget;

use Spryker\Yves\Kernel\AbstractBundleConfig;
use SprykerShop\Shared\ContentNavigationWidget\ContentNavigationWidgetConstants;

class ContentNavigationWidgetConfig extends AbstractBundleConfig
{
    /**
     * @uses \Spryker\Shared\ContentNavigation\ContentNavigationConfig::WIDGET_TEMPLATE_IDENTIFIER_TREE_INLINE
     *
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_TREE_INLINE = 'tree-inline';

    /**
     * @uses \Spryker\Shared\ContentNavigation\ContentNavigationConfig::WIDGET_TEMPLATE_IDENTIFIER_TREE
     *
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_TREE = 'tree';

    /**
     * @uses \Spryker\Shared\ContentNavigation\ContentNavigationConfig::WIDGET_TEMPLATE_IDENTIFIER_LIST_INLINE
     *
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_LIST_INLINE = 'list-inline';

    /**
     * @uses \Spryker\Shared\ContentNavigation\ContentNavigationConfig::WIDGET_TEMPLATE_IDENTIFIER_LIST
     *
     * @var string
     */
    protected const WIDGET_TEMPLATE_IDENTIFIER_LIST = 'list';

    protected const int DEFAULT_NAVIGATION_REVALIDATION_TIME_IN_SECONDS = 3600;

    /**
     * @api
     *
     * @return array<string>
     */
    public function getAvailableTemplateList(): array
    {
        return [
            static::WIDGET_TEMPLATE_IDENTIFIER_TREE_INLINE => '@ContentNavigationWidget/views/navigation/tree-inline.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_TREE => '@ContentNavigationWidget/views/navigation/tree.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_LIST_INLINE => '@ContentNavigationWidget/views/navigation/list-inline.twig',
            static::WIDGET_TEMPLATE_IDENTIFIER_LIST => '@ContentNavigationWidget/views/navigation/list.twig',
        ];
    }

    /**
     * Specification:
     * - Determines if rendered navigation content should be cached in storage.
     * - When enabled, navigation is rendered once and stored for future requests.
     * - When disabled, navigation is rendered on every request.
     *
     * @api
     *
     * @return bool
     */
    public function isNavigationCacheEnabled(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Returns the default revalidation time in seconds for navigation cache.
     * - Used when no specific validity dates are set on navigation nodes.
     * - Default is 24 hours (86400 seconds).
     *
     * @api
     *
     * @return int
     */
    public function getDefaultNavigationRevalidationTimeInSeconds(): int
    {
        return $this->get(
            ContentNavigationWidgetConstants::NAVIGATION_REVALIDATION_TIME_IN_SECONDS,
            static::DEFAULT_NAVIGATION_REVALIDATION_TIME_IN_SECONDS,
        );
    }
}
