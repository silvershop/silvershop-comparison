<?php

namespace SilverShop\Comparison\Model;

use SilverShop\Comparison\GridField\GridFieldConfig_FeatureGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * @property string $Title
 * @method   HasManyList<Feature> Features()
 */
class FeatureGroup extends DataObject
{
    private static array $db = [
        "Title" => "Varchar"
    ];

    private static array $has_many = [
        "Features" => Feature::class
    ];

    private static string $singular_name = "Feature Group";

    private static string $plural_name = "Feature Groups";

    private static string $table_name = 'SilverShop_FeatureGroup';


    public function getCMSFields(): FieldList
    {
        $fieldList = parent::getCMSFields();
        $fieldList->removeByName('Features');

        $gridFieldConfigFeatureGroup = GridFieldConfig_FeatureGroup::create();
        $gridField = GridField::create('Features', 'Features', $this->owner->Features(), $gridFieldConfigFeatureGroup);
        $fieldList->addFieldToTab('Root.Main', $gridField);
        return $fieldList;
    }
}
