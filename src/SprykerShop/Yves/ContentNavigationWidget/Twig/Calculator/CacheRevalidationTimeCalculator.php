<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerShop\Yves\ContentNavigationWidget\Twig\Calculator;

use Generated\Shared\Transfer\NavigationNodeStorageTransfer;
use Generated\Shared\Transfer\NavigationStorageTransfer;
use SprykerShop\Yves\ContentNavigationWidget\ContentNavigationWidgetConfig;

class CacheRevalidationTimeCalculator
{
    public function __construct(protected ContentNavigationWidgetConfig $contentNavigationWidgetConfig)
    {
    }

    public function calculateRevalidationTime(NavigationStorageTransfer $navigationStorageTransfer): void
    {
        $nearestTime = null;

        foreach ($navigationStorageTransfer->getNodes() as $navigationNodeStorageTransfer) {
            $nearestTime = $this->findNearestValidityTime($navigationNodeStorageTransfer, $nearestTime);
        }

        if ($nearestTime === null) {
            $nearestTime = time() + $this->contentNavigationWidgetConfig->getDefaultNavigationRevalidationTimeInSeconds();
        }

        $navigationStorageTransfer->setRevalidteTime((string)$nearestTime);
    }

    protected function findNearestValidityTime(NavigationNodeStorageTransfer $navigationNodeStorageTransfer, ?int $currentNearestTime): ?int
    {
        $now = time();

        // Check validFrom
        if ($navigationNodeStorageTransfer->getValidFrom() !== null) {
            $validFromTimestamp = strtotime($navigationNodeStorageTransfer->getValidFrom());
            $currentNearestTime = $this->getCurrentNearestTime($validFromTimestamp, $now, $currentNearestTime);
        }

        // Check validTo
        if ($navigationNodeStorageTransfer->getValidTo() !== null) {
            $validToTimestamp = strtotime($navigationNodeStorageTransfer->getValidTo());
            $currentNearestTime = $this->getCurrentNearestTime($validToTimestamp, $now, $currentNearestTime);
        }

        // Recursively check children nodes
        foreach ($navigationNodeStorageTransfer->getChildren() as $childNode) {
            $currentNearestTime = $this->findNearestValidityTime($childNode, $currentNearestTime);
        }

        return $currentNearestTime;
    }

    public function getCurrentNearestTime(int|bool $timestampToCompare, int $now, ?int $currentNearestTime): ?int
    {
        if ($timestampToCompare !== false && $timestampToCompare > $now) {
            if ($currentNearestTime === null || $timestampToCompare < $currentNearestTime) {
                return (int)$timestampToCompare;
            }
        }

        return $currentNearestTime;
    }
}
