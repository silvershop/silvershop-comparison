<?php

namespace SilverShop\Comparison\Tests\Unit\Pagetypes;

use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
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

    public function testCannotAddProductTheVisitorCannotView(): void
    {
        $this->logOut();

        $public = Product::create(['Title' => 'Public product']);
        $public->publishSingle();

        $restricted = Product::create(['Title' => 'Members only', 'CanViewType' => 'LoggedInUsers']);
        $restricted->publishSingle();

        $request = new HTTPRequest('GET', '/');
        $request->setSession(new Session([]));
        $controller = Controller::create();
        $controller->setRequest($request);
        $controller->pushCurrent();

        try {
            $page = ProductComparisonPageTestDouble::create();

            $this->assertTrue($page->addToSelection($public->ID));
            $this->assertNull($page->addToSelection($restricted->ID));
            $this->assertSame([$public->ID => $public->ID], $page->exposeGetSelectionIDs());
        } finally {
            $controller->popCurrent();
        }
    }
}
