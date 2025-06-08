<?php

namespace SilverShop\Comparison\Model;

use SilverShop\Page\Product;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;

/**
 * @property string $Title
 * @property ?string $Unit
 * @property ?string $ValueType
 * @property int $Sort
 * @property int $GroupID
 * @method   FeatureGroup Group()
 * @method   HasManyList<Product> Products()
 * @method   HasManyList<ProductFeatureValue> ProductFeatureValues()
 * @method   ManyManyList<Product> Product()
 */
class Feature extends DataObject
{

    use Configurable;

    // if set to true, features are always sorted like they are within the FeatureGroup
    private static bool $sort_features_by_group = false;

    private static array $db = [
        'Title' => 'Varchar',
        'Unit' => 'Varchar',
        'ValueType' => "Enum('Boolean,Number,String','String')",
        'Sort' => 'Int'
    ];

    private static array $has_many = [
        "Products" => Product::class,
        'ProductFeatureValues' => ProductFeatureValue::class
    ];

    private static array $has_one = [
        "Group" => FeatureGroup::class
    ];

    private static array $belongs_many_many = [
        "Product" => Product::class
    ];

    private static array $summary_fields = [
        "Title" => "Title",
        "Unit" => "Unit"
    ];

    private static string $default_sort = 'Sort ASC'; // sorting with in group

    private static string $table_name = 'SilverShop_Feature';

    private static string $singular_name = "Feature";

    private static string $plural_name = "Features";

    public function getCMSFields(): FieldList
    {
        $fields = FieldList::create(
            TextField::create("Title"),
            TextField::create("Unit"),
            DropdownField::create(
                "ValueType",
                "Value Type",
                $this->dbObject('ValueType')->enumValues()
            )
        );

        $groups = FeatureGroup::get();

        if ($groups->exists()) {
            $fields->insertAfter(
                "Unit",
                DropdownField::create("GroupID", "Group", $groups->map()->toArray())
                    ->setHasEmptyDefault(true)
            );
        }

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function listTitle(): string
    {
        if ($this->Group()->exists()) {
            return $this->Group()->Title . ' - ' . $this->Title;
        }
        return $this->Title;
    }

    public function summaryFields(): array
    {
        $fields = parent::summaryFields();

        if (FeatureGroup::get()->exists()) {
            $fields['Group.Title'] = 'Group';
        }

        return $fields;
    }

    public function getValueField(): CheckboxField|NumericField|TextField|LiteralField
    {
        $fields = [
            'Boolean' => CheckboxField::create("Value"),
            'Number' => NumericField::create("Value"),
            'String' => TextField::create("Value")
        ];

        if (isset($fields[$this->ValueType])) {
            return $fields[$this->ValueType];
        } else {
            return LiteralField::create("Value", _t('Feature.SAVETOADDVALUE', 'Save record to add value.'));
        }
    }

    public function getValueDBField($value): DBBoolean|DBFloat|DBVarchar
    {
        $fields = [
            'Boolean' => DBBoolean::create(),
            'Number' => DBFloat::create(),
            'String' => DBVarchar::create()
        ];
        $field =  $fields[$this->ValueType];
        $field->setValue($value);

        return $field;
    }
}
