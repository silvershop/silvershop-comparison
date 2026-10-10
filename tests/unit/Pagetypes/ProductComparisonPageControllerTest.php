<?php

namespace SilverShop\Comparison\Tests\Unit\Pagetypes;

use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverShop\Comparison\Pagetypes\ProductComparisonPageController;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

class ProductComparisonPageControllerTest extends SapphireTest
{
    public function testComparedTableListIsPaddedWhenNothingIsSelected(): void
    {
        Config::modify()->set(ProductComparisonPage::class, 'max_product_comparisons', 3);

        $controller = ProductComparisonPageController::create(ProductComparisonPage::create());
        $list = $controller->getComparedTableList();

        $this->assertNotNull($list);
        $this->assertCount(3, $list);
        $first = $list->first();
        $last = $list->last();
        $this->assertNotNull($first);
        $this->assertNotNull($last);
        $this->assertNull($first->Product);
        $this->assertTrue($first->IsFirstNonProduct);
        $this->assertFalse($last->IsFirstNonProduct);
    }
}
