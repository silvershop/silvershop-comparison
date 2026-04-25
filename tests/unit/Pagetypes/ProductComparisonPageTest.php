<?php

namespace SilverShop\Comparison\Tests\Unit\Pagetypes;

use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;

class ProductComparisonPageTest extends SapphireTest
{
    public function testSelectionMethodsAreSafeWithoutCurrentController(): void
    {
        $property = new \ReflectionProperty(Controller::class, 'controller_stack');
        $property->setAccessible(true);
        $originalStack = $property->getValue();
        $property->setValue([]);

        try {
            $page = new ProductComparisonPageTestDouble();

            $this->assertSame([], $page->exposeGetSelectionIDs());

            $result = $page->exposeSetSelectionIDs(['1' => '1']);
            $this->assertSame($page, $result);
            $this->assertSame([], $page->exposeGetSelectionIDs());
            $this->assertSame(0, $page->getProductCount());
        } finally {
            $property->setValue($originalStack);
        }
    }
}

class ProductComparisonPageTestDouble extends ProductComparisonPage
{
    public function exposeSetSelectionIDs(array $ids): static
    {
        return $this->setSelectionIDs($ids);
    }

    public function exposeGetSelectionIDs(): array
    {
        return $this->getSelectionIDs();
    }
}
