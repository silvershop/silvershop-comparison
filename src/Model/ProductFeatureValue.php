<?php

namespace SilverShop\Comparison\Model;

use SilverShop\Page\Product;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\LiteralField;

/**
 * Pivot table. Connects products with features, but also includes a value.
 */
class ProductFeatureValue extends DataObject
{

    private static $db = [
        "Value" => "Varchar",
        "Sort" => 'Int'
    ];

    private static $default_sort = 'Sort ASC';

    private static $has_one = [
        "Product" => Product::class,
        "Feature" => Feature::class
    ];

    private static $summary_fields  =  array(
        "Feature.Title" => "Feature",
        "Value" => "Value",
        "Feature.Unit" => "Unit"
    );

    private static $singular_name = "Feature";

    private static $plural_name = "Features";

    private static $table_name = 'SilverShop_ProductFeatureValue';

    public function getCMSFields() {
        $fields = new FieldList();
        $feature = $this->Feature();

        $field = new TextField('ProductID','ProductID');
        $fields->push($field);

        if ($feature->exists()) {
            $fields->push(ReadonlyField::create("FeatureTitle","Feature", $feature->Title));
            $fields->push($feature->getValueField());
        } else {
            $selected = Feature::get()
                ->innerJoin("SilverShop_ProductFeatureValue","SilverShop_Feature.ID = SilverShop_ProductFeatureValue.FeatureID")
                ->filter("ProductID", Controller::curr()->currentPageID())
                ->getIDList();
            $features = Feature::get();
            if(!empty($selected)){
                $features = $features->filter("ID:not",$selected);
            }
            $fields->push(DropdownField::create("FeatureID","Feature",$features->map()->toArray()));
            $fields->push(LiteralField::create("creationnote", "<p class=\"message\">You can choose a value for this feature after saving.</p>"));
        }

        return $fields;
    }

    public function getTitle() {
        return $this->Feature()->Title;
    }

    public function TypedValue() {
        return $this->Feature()->getValueDBField($this->Value);
    }
}
