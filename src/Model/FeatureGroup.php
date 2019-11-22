<?php

namespace SilverShop\Comparison\Model;

use SilverShop\Comparison\GridField\GridFieldConfig_FeatureGroup;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;

class FeatureGroup extends DataObject
{
    private static $db = [
        "Title" => "Varchar"
    ];

    private static $has_many = [
        "Features" => Feature::class
    ];

    private static $singular_name = "Feature Group";

    private static $plural_name = "Feature Groups";

    private static $table_name = 'SilverShop_FeatureGroup';


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Features');
        $config = new GridFieldConfig_FeatureGroup();
        $field = new GridField('Features', 'Features', $this->owner->Features(), $config);
        $fields->addFieldToTab('Root.Main', $field);
        return $fields;
    }


}
