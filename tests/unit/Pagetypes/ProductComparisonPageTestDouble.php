<?php

namespace SilverShop\Comparison\Tests\Unit\Pagetypes;

use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverStripe\Dev\TestOnly;

/**
 * Exposes ProductComparisonPage's protected selection methods to tests.
 */
class ProductComparisonPageTestDouble extends ProductComparisonPage implements TestOnly
{
    private static string $table_name = 'SilverShop_ProductComparisonPageTestDouble';

    /**
     * @param array<int, int> $ids
     */
    public function exposeSetSelectionIDs(array $ids): static
    {
        return $this->setSelectionIDs($ids);
    }

    /**
     * @return array<int, int>
     */
    public function exposeGetSelectionIDs(): array
    {
        return $this->getSelectionIDs();
    }
}
