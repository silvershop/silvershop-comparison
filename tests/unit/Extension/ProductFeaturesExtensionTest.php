<?php

namespace SilverShop\Comparison\Tests\Unit\Extension;

use SilverShop\Comparison\Model\Feature;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverShop\Comparison\Model\ProductFeatureValue;
use SilverShop\Page\Product;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Model\ArrayData;

class ProductFeaturesExtensionTest extends SapphireTest
{
    protected $usesDatabase = true;

    private Product $product;

    private FeatureGroup $size;

    private FeatureGroup $shipping;

    private Feature $weight;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create(['Title' => 'Widget']);
        $this->product->write();

        $this->shipping = FeatureGroup::create(['Title' => 'Shipping']);
        $this->shipping->write();
        $this->size = FeatureGroup::create(['Title' => 'Size']);
        $this->size->write();

        $height = $this->addFeature('Height', 1);
        $weight = $this->weight = $this->addFeature('Weight', 2);
        $this->addFeature('Colour', 3);

        // Weight belongs to both groups; Colour to none
        $this->size->Features()->add($weight, ['Sort' => 2]);
        $this->size->Features()->add($height, ['Sort' => 1]);
        $this->shipping->Features()->add($weight, ['Sort' => 1]);
    }

    public function testSharedFeatureAppearsInEachGroup(): void
    {
        $grouped = $this->summarise($this->product->GroupedFeatures());

        // Groups in order of first appearance on the product, children in product order
        $this->assertSame([
            'Size' => ['Height', 'Weight'],
            'Shipping' => ['Weight'],
        ], $grouped);
    }

    public function testUngroupedFeaturesAreOptional(): void
    {
        $grouped = $this->summarise($this->product->GroupedFeatures(true));

        $this->assertSame(['Colour'], $grouped['']);
    }

    public function testUngroupedShownWhenNoGroupsExist(): void
    {
        $this->size->delete();
        $this->shipping->delete();

        $this->assertSame(
            ['' => ['Height', 'Weight', 'Colour']],
            $this->summarise($this->product->GroupedFeatures(true))
        );
    }

    public function testSortFeaturesByGroup(): void
    {
        Feature::config()->set('sort_features_by_group', true);
        $this->size->Features()->add($this->weight, ['Sort' => 0]);

        $this->assertSame([
            'Shipping' => ['Weight'],
            'Size' => ['Weight', 'Height'],
        ], $this->summarise($this->product->GroupedFeatures()));
    }

    public function testAddAllFeaturesFromGroup(): void
    {
        $other = Product::create(['Title' => 'Other']);
        $other->write();

        $other->addAllFeaturesFromGroup($this->size->ID);
        $other->addAllFeaturesFromGroup($this->size->ID);

        $this->assertSame(
            ['Height', 'Weight'],
            array_map(fn (ProductFeatureValue $value) => $this->featureTitle($value), $other->Features()->toArray())
        );
    }

    private function addFeature(string $title, int $sort): Feature
    {
        $feature = Feature::create(['Title' => $title]);
        $feature->write();

        $value = ProductFeatureValue::create([
            'FeatureID' => $feature->ID,
            'ProductID' => $this->product->ID,
            'Sort' => $sort,
        ]);
        $value->write();

        return $feature;
    }

    private function featureTitle(ProductFeatureValue $value): string
    {
        $feature = $value->Feature();

        return $feature->exists() ? (string) $feature->Title : '';
    }

    /**
     * @param iterable<ArrayData> $grouped
     * @return array<string, string[]>
     */
    private function summarise(iterable $grouped): array
    {
        $result = [];
        foreach ($grouped as $item) {
            $group = $item->hasField('Group') ? $item->getField('Group') : null;
            $result[$group ? (string) $group->Title : ''] = array_map(
                fn (ProductFeatureValue $value) => $this->featureTitle($value),
                $item->getField('Children')->toArray()
            );
        }

        return $result;
    }
}
