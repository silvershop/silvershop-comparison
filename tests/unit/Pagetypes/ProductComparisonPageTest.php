<?php

namespace SilverShop\Comparison\Tests\Unit\Pagetypes;

use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;

class ProductComparisonPageTest extends SapphireTest
{
    protected static $extra_dataobjects = [
        ProductComparisonPageTestDouble::class,
    ];

    public function testSelectionMethodsAreSafeWithoutCurrentController(): void
    {
        $property = new \ReflectionProperty(Controller::class, 'controller_stack');
        $originalStack = $property->getValue();
        $property->setValue(null, []);

        try {
            $page = ProductComparisonPageTestDouble::create();

            $this->assertSame([], $page->exposeGetSelectionIDs());

            $result = $page->exposeSetSelectionIDs([1 => 1]);
            $this->assertSame($page, $result);
            $this->assertSame([], $page->exposeGetSelectionIDs());
            $this->assertSame(0, $page->getProductCount());
        } finally {
            $property->setValue(null, $originalStack);
        }
    }
}
