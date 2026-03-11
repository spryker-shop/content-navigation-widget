<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\ContentNavigationWidget\Twig\Calculator;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\NavigationNodeStorageTransfer;
use Generated\Shared\Transfer\NavigationStorageTransfer;
use SprykerShop\Yves\ContentNavigationWidget\ContentNavigationWidgetConfig;
use SprykerShop\Yves\ContentNavigationWidget\Twig\Calculator\CacheRevalidationTimeCalculator;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShop
 * @group Yves
 * @group ContentNavigationWidget
 * @group Twig
 * @group Calculator
 * @group CacheRevalidationTimeCalculatorTest
 * Add your own group annotations below this line
 */
class CacheRevalidationTimeCalculatorTest extends Unit
{
    protected const int DEFAULT_REVALIDATION_TIME_SECONDS = 259200; // 72 hours

    protected const string DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    public function testCalculateRevalidationTimeWithNoValidityDatesUsesDefaultTime(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer(null, null),
            $this->createNavigationNodeStorageTransfer(null, null),
        ]);

        $expectedTime = time() + static::DEFAULT_REVALIDATION_TIME_SECONDS;

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertNotNull($navigationStorageTransfer->getRevalidteTime());
        $actualTime = (int)$navigationStorageTransfer->getRevalidteTime();
        // Allow 2 seconds tolerance for test execution time
        $this->assertGreaterThanOrEqual($expectedTime - 2, $actualTime);
        $this->assertLessThanOrEqual($expectedTime + 2, $actualTime);
    }

    public function testCalculateRevalidationTimeWithFutureValidFromReturnsNearestTime(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $nearestTime = strtotime('+5 days');
        $fartherTime = strtotime('+10 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($fartherTime), null),
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($nearestTime), null),
        ]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$nearestTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithFutureValidToReturnsNearestTime(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $nearestTime = strtotime('+3 days');
        $fartherTime = strtotime('+15 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer(null, $this->formatDateTime($fartherTime)),
            $this->createNavigationNodeStorageTransfer(null, $this->formatDateTime($nearestTime)),
        ]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$nearestTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithMixedValidityDatesReturnsNearestFutureTime(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $nearestTime = strtotime('+2 days');
        $middleTime = strtotime('+7 days');
        $farthestTime = strtotime('+20 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($farthestTime), null),
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($nearestTime), null),
            $this->createNavigationNodeStorageTransfer(null, $this->formatDateTime($middleTime)),
        ]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$nearestTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithPastDatesIgnoresThem(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $pastTime = strtotime('-5 days');
        $futureTime = strtotime('+8 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($pastTime), null),
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($futureTime), null),
        ]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$futureTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithOnlyPastDatesUsesDefaultTime(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $pastTime1 = strtotime('-10 days');
        $pastTime2 = strtotime('-3 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($pastTime1), null),
            $this->createNavigationNodeStorageTransfer(null, $this->formatDateTime($pastTime2)),
        ]);

        $expectedTime = time() + static::DEFAULT_REVALIDATION_TIME_SECONDS;

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $actualTime = (int)$navigationStorageTransfer->getRevalidteTime();
        $this->assertGreaterThanOrEqual($expectedTime - 2, $actualTime);
        $this->assertLessThanOrEqual($expectedTime + 2, $actualTime);
    }

    public function testCalculateRevalidationTimeWithNestedChildrenConsidersAllNodes(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $parentTime = strtotime('+10 days');
        $childTime = strtotime('+4 days'); // Nearest
        $grandchildTime = strtotime('+15 days');

        $grandchildNode = $this->createNavigationNodeStorageTransfer($this->formatDateTime($grandchildTime), null);
        $childNode = $this->createNavigationNodeStorageTransfer($this->formatDateTime($childTime), null, [$grandchildNode]);
        $parentNode = $this->createNavigationNodeStorageTransfer($this->formatDateTime($parentTime), null, [$childNode]);

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([$parentNode]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$childTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithInvalidDateFormatIgnoresThem(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $validTime = strtotime('+6 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer('invalid-date-format', null),
            $this->createNavigationNodeStorageTransfer($this->formatDateTime($validTime), null),
            $this->createNavigationNodeStorageTransfer(null, 'another-invalid-date'),
        ]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$validTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithEmptyNodesUsesDefaultTime(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $navigationStorageTransfer = $this->createNavigationStorageTransfer([]);
        $expectedTime = time() + static::DEFAULT_REVALIDATION_TIME_SECONDS;

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $actualTime = (int)$navigationStorageTransfer->getRevalidteTime();
        $this->assertGreaterThanOrEqual($expectedTime - 2, $actualTime);
        $this->assertLessThanOrEqual($expectedTime + 2, $actualTime);
    }

    public function testCalculateRevalidationTimeWithBothValidFromAndValidToSelectsNearest(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $nearestTime = strtotime('+1 day');
        $fartherTime = strtotime('+30 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer(
                $this->formatDateTime($nearestTime),
                $this->formatDateTime($fartherTime),
            ),
        ]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$nearestTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithValidToBeforeValidFromSelectsValidTo(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $validToTime = strtotime('+2 days'); // Nearest
        $validFromTime = strtotime('+5 days');

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([
            $this->createNavigationNodeStorageTransfer(
                $this->formatDateTime($validFromTime),
                $this->formatDateTime($validToTime),
            ),
        ]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$validToTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithMultipleLevelsOfNestingFindsDeepestNode(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $level1Time = strtotime('+20 days');
        $level2Time = strtotime('+12 days');
        $level3Time = strtotime('+1 hour'); // Nearest - deepest level
        $level4Time = strtotime('+5 days');

        // Build nested structure: level1 -> level2 -> level3 -> level4
        $level4Node = $this->createNavigationNodeStorageTransfer($this->formatDateTime($level4Time), null);
        $level3Node = $this->createNavigationNodeStorageTransfer($this->formatDateTime($level3Time), null, [$level4Node]);
        $level2Node = $this->createNavigationNodeStorageTransfer($this->formatDateTime($level2Time), null, [$level3Node]);
        $level1Node = $this->createNavigationNodeStorageTransfer($this->formatDateTime($level1Time), null, [$level2Node]);

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([$level1Node]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$level3Time, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithNullChildrenArrayDoesNotFail(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $futureTime = strtotime('+3 days');

        $nodeWithoutChildren = new NavigationNodeStorageTransfer();
        $nodeWithoutChildren->setValidFrom($this->formatDateTime($futureTime));
        // Note: Not setting children at all (will be null internally)

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([$nodeWithoutChildren]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$futureTime, $navigationStorageTransfer->getRevalidteTime());
    }

    public function testCalculateRevalidationTimeWithMixedNullAndValidDatesInChildren(): void
    {
        // Arrange
        $calculator = $this->createCalculator();
        $nearestTime = strtotime('+4 days');

        $childWithNoDates = $this->createNavigationNodeStorageTransfer(null, null);
        $childWithValidDate = $this->createNavigationNodeStorageTransfer($this->formatDateTime($nearestTime), null);
        $childWithInvalidDate = $this->createNavigationNodeStorageTransfer('not-a-date', null);

        $parentNode = $this->createNavigationNodeStorageTransfer(null, null, [
            $childWithNoDates,
            $childWithValidDate,
            $childWithInvalidDate,
        ]);

        $navigationStorageTransfer = $this->createNavigationStorageTransfer([$parentNode]);

        // Act
        $calculator->calculateRevalidationTime($navigationStorageTransfer);

        // Assert
        $this->assertEquals((string)$nearestTime, $navigationStorageTransfer->getRevalidteTime());
    }

    protected function createCalculator(): CacheRevalidationTimeCalculator
    {
        $configMock = $this->createMock(ContentNavigationWidgetConfig::class);
        $configMock->method('getDefaultNavigationRevalidationTimeInSeconds')
            ->willReturn(static::DEFAULT_REVALIDATION_TIME_SECONDS);

        return new CacheRevalidationTimeCalculator($configMock);
    }

    /**
     * @param array<\Generated\Shared\Transfer\NavigationNodeStorageTransfer> $nodes
     *
     * @return \Generated\Shared\Transfer\NavigationStorageTransfer
     */
    protected function createNavigationStorageTransfer(array $nodes): NavigationStorageTransfer
    {
        $navigationStorageTransfer = new NavigationStorageTransfer();
        $navigationStorageTransfer->setNodes(new ArrayObject($nodes));

        return $navigationStorageTransfer;
    }

    /**
     * @param string|null $validFrom
     * @param string|null $validTo
     * @param array<\Generated\Shared\Transfer\NavigationNodeStorageTransfer> $children
     *
     * @return \Generated\Shared\Transfer\NavigationNodeStorageTransfer
     */
    protected function createNavigationNodeStorageTransfer(
        ?string $validFrom = null,
        ?string $validTo = null,
        array $children = []
    ): NavigationNodeStorageTransfer {
        $navigationNodeStorageTransfer = new NavigationNodeStorageTransfer();
        $navigationNodeStorageTransfer->setValidFrom($validFrom);
        $navigationNodeStorageTransfer->setValidTo($validTo);
        $navigationNodeStorageTransfer->setChildren(new ArrayObject($children));

        return $navigationNodeStorageTransfer;
    }

    protected function formatDateTime(int $timestamp): string
    {
        return date(static::DATETIME_FORMAT, $timestamp);
    }
}
