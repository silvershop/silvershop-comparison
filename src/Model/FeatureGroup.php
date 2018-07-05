<?php

namespace SilverShop\Comparison\Model;

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
}
