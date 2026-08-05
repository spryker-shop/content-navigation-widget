<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ContentNavigationWidget\Twig;

use ArrayObject;
use DateTime;
use Generated\Shared\Transfer\NavigationStorageTransfer;
use Spryker\Client\ContentNavigation\Exception\MissingNavigationTermException;
use Spryker\Client\Store\StoreClientInterface;
use Spryker\Shared\Twig\TwigFunctionProvider;
use SprykerShop\Yves\ContentNavigationWidget\ContentNavigationWidgetConfig;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToContentNavigationClientInterface;
use SprykerShop\Yves\ContentNavigationWidget\Dependency\Client\ContentNavigationWidgetToNavigationStorageClientInterface;
use SprykerShop\Yves\ContentNavigationWidget\Twig\Calculator\CacheRevalidationTimeCalculator;
use Twig\Environment;

class ContentNavigationTwigFunctionProvider extends TwigFunctionProvider
{
    /**
     * @uses \Spryker\Shared\ContentNavigation\ContentNavigationWidgetConfig::TWIG_FUNCTION_NAME
     */
    protected const string TWIG_FUNCTION_NAME_CONTENT_NAVIGATION = 'content_navigation';

    protected const string MESSAGE_NAVIGATION_NOT_FOUND = '<b>Content Navigation with key %s not found.</b>';

    protected const string MESSAGE_NAVIGATION_WRONG_TYPE = '<b>Content Navigation could not be rendered because the content item with key %s is not an navigation.</b>';

    protected const string MESSAGE_NAVIGATION_WRONG_TEMPLATE = '<b>"%s" is not supported name of template.</b>';

    public function __construct(
        protected Environment $twig,
        protected string $localeName,
        protected ContentNavigationWidgetToContentNavigationClientInterface $contentNavigationClient,
        protected ContentNavigationWidgetToNavigationStorageClientInterface $navigationStorageClient,
        protected ContentNavigationWidgetConfig $contentNavigationWidgetConfig,
        protected CacheRevalidationTimeCalculator $cacheRevalidationTimeCalculator,
        protected StoreClientInterface $storeClient
    ) {
    }

    public function getFunctionName(): string
    {
        return static::TWIG_FUNCTION_NAME_CONTENT_NAVIGATION;
    }

    public function getFunction(): callable
    {
        return function (string $contentKey, string $templateIdentifier) {
            $availableTemplate = $this->findTemplate($templateIdentifier);
            if (!$availableTemplate) {
                return $this->getMessageNavigationWrongTemplate($templateIdentifier);
            }
            try {
                $contentNavigationTypeTransfer = $this->contentNavigationClient->executeNavigationTypeByKey($contentKey, $this->localeName);
                if (!$contentNavigationTypeTransfer) {
                    return $this->getMessageNavigationNotFound($contentKey);
                }
            } catch (MissingNavigationTermException $e) {
                return $this->getMessageNavigationWrongType($contentKey);
            }

            $navigationStorageTransfer = $this->navigationStorageClient->findNavigationTreeByKey(
                $contentNavigationTypeTransfer->getNavigationKey(),
                $this->localeName,
            );

            if (!$navigationStorageTransfer) {
                return $this->getMessageNavigationNotFound($contentKey);
            }
            $renderedContentCacheKey = $this->getRenderedContentCacheKey($availableTemplate);

            if (
                $this->contentNavigationWidgetConfig->isNavigationCacheEnabled() &&
                $this->isShouldBeRevalidated($navigationStorageTransfer, $renderedContentCacheKey)
            ) {
                return $navigationStorageTransfer->getRenderedContent()[$renderedContentCacheKey];
            }

            if (!$navigationStorageTransfer->getIsActive()) {
                return '';
            }

            $navigationStorageTransfer = $this->optimizeNavigationStorageNodes($navigationStorageTransfer);

            $renderedContent = $this->twig->render(
                $availableTemplate,
                ['navigation' => $navigationStorageTransfer],
            );

            if ($this->contentNavigationWidgetConfig->isNavigationCacheEnabled()) {
                $sharedContentData = $navigationStorageTransfer->getRenderedContent();
                $sharedContentData[$renderedContentCacheKey] = $renderedContent;
                $navigationStorageTransfer->setRenderedContent($sharedContentData);
                $this->cacheRevalidationTimeCalculator->calculateRevalidationTime($navigationStorageTransfer);

                $this->navigationStorageClient->saveNavigationTree(
                    $navigationStorageTransfer,
                    $contentNavigationTypeTransfer->getNavigationKey(),
                    $this->localeName,
                );
            }

            return $renderedContent;
        };
    }

    protected function findTemplate(string $templateIdentifier): ?string
    {
        $availableTemplateList = $this->contentNavigationWidgetConfig->getAvailableTemplateList();

        return $availableTemplateList[$templateIdentifier] ?? null;
    }

    protected function getMessageNavigationNotFound(string $contentKey): string
    {
        return sprintf(static::MESSAGE_NAVIGATION_NOT_FOUND, $contentKey);
    }

    protected function getMessageNavigationWrongTemplate(string $templateIdentifier): string
    {
        return sprintf(static::MESSAGE_NAVIGATION_WRONG_TEMPLATE, $templateIdentifier);
    }

    protected function getMessageNavigationWrongType(string $contentKey): string
    {
        return sprintf(static::MESSAGE_NAVIGATION_WRONG_TYPE, $contentKey);
    }

    protected function optimizeNavigationStorageNodes(NavigationStorageTransfer $navigationStorageTransfer): NavigationStorageTransfer
    {
        $now = new DateTime();

        $optimizedNavigationNodeStorageTransfers = new ArrayObject();

        foreach ($navigationStorageTransfer->getNodes() as $navigationNodeStorageTransfer) {
            $isValidFrom = $navigationNodeStorageTransfer->getValidFrom() === null || new DateTime($navigationNodeStorageTransfer->getValidFrom()) <= $now;
            $isValidTo = $navigationNodeStorageTransfer->getValidTo() === null || new DateTime($navigationNodeStorageTransfer->getValidTo()) >= $now;
            $isActiveAndValid = $navigationNodeStorageTransfer->getIsActive() && $isValidFrom && $isValidTo;
            $hasChildren = $navigationNodeStorageTransfer->getChildren()->count() > 0;

            $navigationNodeStorageTransfer->setIsValidFrom($isValidFrom);
            $navigationNodeStorageTransfer->setIsValidTo($isValidTo);
            $navigationNodeStorageTransfer->setIsActiveAndValid($isActiveAndValid);
            $navigationNodeStorageTransfer->setHasChildren($hasChildren);

            $optimizedNavigationNodeStorageTransfers->append($navigationNodeStorageTransfer);
        }
        $navigationStorageTransfer->setNodes($optimizedNavigationNodeStorageTransfers);

        return $navigationStorageTransfer;
    }

    protected function isShouldBeRevalidated(NavigationStorageTransfer $navigationStorageTransfer, string $renderedContentCacheKey): bool
    {
        if (!isset($navigationStorageTransfer->getRenderedContent()[$renderedContentCacheKey])) {
            return false;
        }

        if ($navigationStorageTransfer->getRevalidteTime() === null) {
            return true;
        }

        return (int)$navigationStorageTransfer->getRevalidteTime() >= time();
    }

    protected function getRenderedContentCacheKey(string $availableTemplate): string
    {
        return sprintf('%s:%s', $this->storeClient->getCurrentStore()->getName(), $availableTemplate);
    }
}
