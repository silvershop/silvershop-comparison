<?php

namespace SilverShop\Comparison\Tests\Unit\Model;

use SilverShop\Comparison\Model\Feature;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverStripe\Dev\SapphireTest;

class FeatureGroupTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testFeatureCanBelongToMultipleGroups(): void
    {
        $size = $this->createGroup('Size');
        $shipping = $this->createGroup('Shipping');
        $weight = $this->createFeature('Weight');

        $size->Features()->add($weight);
        $shipping->Features()->add($weight);

        $this->assertEqualsCanonicalizing(
            [$size->ID, $shipping->ID],
            $weight->Groups()->column('ID')
        );
        $this->assertSame('Shipping, Size - Weight', $weight->listTitle());
        $this->assertSame('Shipping, Size', $weight->getGroupNames());
    }

    public function testListTitleWithoutGroups(): void
    {
        $this->assertSame('Weight', $this->createFeature('Weight')->listTitle());
    }

    public function testSortedFeaturesUsesPerGroupOrder(): void
    {
        $size = $this->createGroup('Size');
        $shipping = $this->createGroup('Shipping');
        $weight = $this->createFeature('Weight');
        $height = $this->createFeature('Height');

        $size->Features()->add($weight, ['Sort' => 2]);
        $size->Features()->add($height, ['Sort' => 1]);
        $shipping->Features()->add($weight, ['Sort' => 1]);
        $shipping->Features()->add($height, ['Sort' => 2]);

        $this->assertSame(['Height', 'Weight'], $size->SortedFeatures()->column('Title'));
        $this->assertSame(['Weight', 'Height'], $shipping->SortedFeatures()->column('Title'));
    }

    public function testDeletingGroupKeepsSharedFeatures(): void
    {
        $size = $this->createGroup('Size');
        $shipping = $this->createGroup('Shipping');
        $weight = $this->createFeature('Weight');
        $size->Features()->add($weight);
        $shipping->Features()->add($weight);

        $size->delete();

        $weight = Feature::get()->byID($weight->ID);
        $this->assertNotNull($weight);
        $this->assertSame([$shipping->ID], $weight->Groups()->column('ID'));
    }

    public function testDeletingFeatureUnlinksGroups(): void
    {
        $size = $this->createGroup('Size');
        $weight = $this->createFeature('Weight');
        $size->Features()->add($weight);

        $weight->delete();

        $this->assertSame(0, $size->Features()->count());
        $this->assertSame(0, (int) $size->Features()->dataQuery()->query()->setSelect('COUNT(*)')->execute()->value());
    }

    public function testDuplicatingGroupLinksRatherThanCopiesFeatures(): void
    {
        $size = $this->createGroup('Size');
        $weight = $this->createFeature('Weight');
        $size->Features()->add($weight, ['Sort' => 5]);

        $copy = $size->duplicate();

        $this->assertSame(1, Feature::get()->count());
        $this->assertSame([$weight->ID], $copy->Features()->column('ID'));
        $this->assertSame(5, (int) $copy->Features()->first()?->Sort);
    }

    private function createGroup(string $title): FeatureGroup
    {
        $group = FeatureGroup::create(['Title' => $title]);
        $group->write();

        return $group;
    }

    private function createFeature(string $title): Feature
    {
        $feature = Feature::create(['Title' => $title]);
        $feature->write();

        return $feature;
    }
}
