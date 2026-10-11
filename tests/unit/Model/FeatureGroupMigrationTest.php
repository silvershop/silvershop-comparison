<?php

namespace SilverShop\Comparison\Tests\Unit\Model;

use SilverShop\Comparison\Model\Feature;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBInt;

/**
 * Covers upgrading from the single `Feature.GroupID` has_one to the many_many.
 */
class FeatureGroupMigrationTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testLegacyGroupAssignmentsAreMigrated(): void
    {
        $table = DataObject::getSchema()->tableName(Feature::class) ?? self::fail('Feature has no table');
        $this->addLegacyColumns($table);

        $size = FeatureGroup::create(['Title' => 'Size']);
        $size->write();
        $weight = Feature::create(['Title' => 'Weight']);
        $weight->write();
        $height = Feature::create(['Title' => 'Height']);
        $height->write();
        $orphan = Feature::create(['Title' => 'Orphan']);
        $orphan->write();

        $this->setLegacy($table, $weight->ID, $size->ID, 2);
        $this->setLegacy($table, $height->ID, $size->ID, 1);
        // Points at a group that no longer exists
        $this->setLegacy($table, $orphan->ID, 9999, 1);

        // Already linked: must not be duplicated
        $size->Features()->add($weight, ['Sort' => 2]);

        singleton(Feature::class)->requireDefaultRecords();

        $this->assertSame(['Height', 'Weight'], $size->SortedFeatures()->column('Title'));
        $this->assertSame(0, Feature::get()->byID($orphan->ID)?->Groups()->count());

        $schema = DB::get_schema() ?? self::fail('No database schema manager');
        $this->assertFalse($schema->hasField($table, 'GroupID'));
        $this->assertFalse($schema->hasField($table, 'Sort'));
        $this->assertTrue($schema->hasField($table, '_obsolete_GroupID'));

        // Second run is a no-op
        singleton(Feature::class)->requireDefaultRecords();
        $this->assertSame(2, $size->Features()->count());
    }

    private function addLegacyColumns(string $table): void
    {
        $schema = DB::get_schema() ?? self::fail('No database schema manager');
        $schema->schemaUpdate(function () use ($table) {
            foreach (['GroupID', 'Sort'] as $name) {
                $field = DBInt::create($name);
                $field->setTable($table);
                $field->requireField();
            }
        });
    }

    private function setLegacy(string $table, int $featureID, int $groupID, int $sort): void
    {
        DB::prepared_query(
            sprintf('UPDATE "%s" SET "GroupID" = ?, "Sort" = ? WHERE "ID" = ?', $table),
            [$groupID, $sort, $featureID]
        );
    }
}
