<?php

namespace SilverShop\Comparison\Model;

use SilverShop\Page\Product;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;

/**
 * @property string $Title
 * @property ?string $Unit
 * @property ?string $ValueType
 * @method   HasManyList<Product> Products()
 * @method   HasManyList<ProductFeatureValue> ProductFeatureValues()
 * @method   ManyManyList<Product> Product()
 * @method   ManyManyList<FeatureGroup> Groups()
 */
class Feature extends DataObject
{

    use Configurable;

    // if set to true, features are always sorted like they are within each FeatureGroup
    private static bool $sort_features_by_group = false;

    private static array $db = [
        'Title' => 'Varchar',
        'Unit' => 'Varchar',
        'ValueType' => "Enum('Boolean,Number,String','String')",
    ];

    private static array $has_many = [
        "Products" => Product::class,
        'ProductFeatureValues' => ProductFeatureValue::class
    ];

    private static array $cascade_deletes = [
        'ProductFeatureValues',
    ];

    private static array $cascade_duplicates = [
        'ProductFeatureValues',
    ];

    private static array $belongs_many_many = [
        "Product" => Product::class,
        "Groups" => FeatureGroup::class . '.Features',
    ];

    private static array $summary_fields = [
        "Title" => "Title",
        "Unit" => "Unit"
    ];

    private static string $default_sort = 'Title ASC';

    private static string $table_name = 'SilverShop_Feature';

    private static string $singular_name = "Feature";

    private static string $plural_name = "Features";

    public function getCMSFields(): FieldList
    {
        $fieldList = FieldList::create(
            TextField::create("Title"),
            TextField::create("Unit"),
            DropdownField::create(
                "ValueType",
                "Value Type",
                $this->dbObject('ValueType')->enumValues()
            )
        );

        $featureGroupDataList = FeatureGroup::get();

        if ($featureGroupDataList->exists()) {
            $fieldList->insertAfter(
                "Unit",
                ListboxField::create("Groups", "Groups", $featureGroupDataList->map()->toArray())
            );
        }

        $this->extend('updateCMSFields', $fieldList);

        return $fieldList;
    }

    public function listTitle(): string
    {
        $groupNames = $this->getGroupNames();

        if ($groupNames !== '') {
            return $groupNames . ' - ' . $this->Title;
        }

        return $this->Title;
    }

    /**
     * Comma separated titles of the groups this feature belongs to.
     */
    public function getGroupNames(): string
    {
        return implode(', ', $this->Groups()->sort('Title')->column('Title'));
    }

    /**
     * @return array<string, string>
     */
    public function summaryFields(): array
    {
        $fields = parent::summaryFields();

        if (FeatureGroup::get()->exists()) {
            $fields['GroupNames'] = 'Groups';
        }

        return $fields;
    }

    protected function onBeforeDelete(): void
    {
        parent::onBeforeDelete();

        $this->Groups()->removeAll();
    }

    public function requireDefaultRecords(): void
    {
        parent::requireDefaultRecords();

        $this->migrateLegacyGroup();
    }

    /**
     * Features used to belong to a single group via a `GroupID` has_one, with
     * their order in that group held in `Feature.Sort`. Copy those assignments
     * onto the many_many join table, then retire the legacy columns so this
     * only ever runs once.
     */
    protected function migrateLegacyGroup(): void
    {
        $dataSchema = DataObject::getSchema();
        $dbSchema = DB::get_schema();
        $featureTable = $dataSchema->tableName(self::class);

        // Bail if already migrated, or if a project has reinstated the field itself
        if (!$dbSchema
            || !$featureTable
            || !$dbSchema->hasField($featureTable, 'GroupID')
            || $dataSchema->databaseField(self::class, 'GroupID')
        ) {
            return;
        }

        $groupTable = $dataSchema->tableName(FeatureGroup::class);
        $relation = FeatureGroup::featuresJoin();
        $joinTable = $relation['join'];
        $groupField = $relation['parentField'];
        $featureField = $relation['childField'];

        $migrateSort = $dbSchema->hasField($featureTable, 'Sort')
            && !$dataSchema->databaseField(self::class, 'Sort');

        DB::query(sprintf(
            'INSERT INTO "%1$s" ("%2$s", "%3$s", "Sort")
            SELECT "f"."GroupID", "f"."ID", %4$s
            FROM "%5$s" "f"
            INNER JOIN "%6$s" "g" ON "g"."ID" = "f"."GroupID"
            WHERE NOT EXISTS (
                SELECT 1 FROM "%1$s" "j" WHERE "j"."%2$s" = "f"."GroupID" AND "j"."%3$s" = "f"."ID"
            )',
            $joinTable,
            $groupField,
            $featureField,
            $migrateSort ? '"f"."Sort"' : '0',
            $featureTable,
            $groupTable
        ));

        $migrated = DB::affected_rows();

        $dbSchema->dontRequireField($featureTable, 'GroupID');

        if ($migrateSort) {
            $dbSchema->dontRequireField($featureTable, 'Sort');
        }

        DB::alteration_message(
            sprintf('Migrated %d feature group assignments to %s', $migrated, $joinTable),
            'changed'
        );
    }

    public function getValueField(): CheckboxField|NumericField|TextField|LiteralField
    {
        $fields = [
            'Boolean' => CheckboxField::create("Value"),
            'Number' => NumericField::create("Value"),
            'String' => TextField::create("Value")
        ];

        return $fields[(string) $this->ValueType] ?? LiteralField::create("Value", _t('Feature.SAVETOADDVALUE', 'Save record to add value.'));
    }

    public function getValueDBField(mixed $value): DBBoolean|DBFloat|DBVarchar
    {
        $field = match ($this->ValueType) {
            'Boolean' => DBBoolean::create(),
            'Number' => DBFloat::create(),
            default => DBVarchar::create(),
        };
        $field->setValue($value);

        return $field;
    }
}
