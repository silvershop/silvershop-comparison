<?php

namespace SilverShop\Comparison\Extension;

use SilverShop\Comparison\GridField\GridFieldConfig_ProductFeatures;
use SilverShop\Comparison\Model\Feature;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Forms\DropdownField;
use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverShop\Comparison\Model\ProductFeatureValue;

/**
 * Class ProductFeaturesExtension
 * @package SilverShop\Comparison\Extension
 *
 * @method ManyManyList Features
 */

class ProductFeaturesExtension extends DataExtension
{
    private static $many_many = [
        'Features' => ProductFeatureValue::class
    ];

    public function updateCMSFields(FieldList $fields) {
        $config = new GridFieldConfig_ProductFeatures();

        $sortByGroup = Config::inst()->get(Feature::class, 'sort_features_by_group');
        if( $sortByGroup ){
            $features = $this->owner->Features()
                ->leftJoin('SilverShop_Feature',"\"SilverShop_Feature\".\"ID\"=\"SilverShop_ProductFeatureValue\".\"FeatureID\"")
                ->leftJoin('SilverShop_FeatureGroup',"\"SilverShop_FeatureGroup\".\"ID\"=\"SilverShop_Feature\".\"GroupID\"")
                ->Sort("\"SilverShop_FeatureGroup\".\"Title\" ASC, \"SilverShop_Feature\".\"Sort\" ASC");
        } else {
            $features = $this->owner->Features();
        }

        $grid = GridField::create("Features", "Features", $features, $config);
        $fields->addFieldToTab("Root.Features",$grid);

        // quick add all from feature group
        $field = new DropdownField('QuickAddFeatureGroupID','Add all features from group',FeatureGroup::get()->map('ID','Title'));
        $field->setEmptyString('-');
        $field->setDescription('After save, all features of selected group will be added to the product');
        $fields->addFieldToTab('Root.Features',$field);
    }

    public function CompareLink() {
        if ($this->isCompared()) {
            return $this->CompareRemoveLink();
        }

        return $this->CompareAddLink();
    }

    public function CompareAddLink() {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("add/". $this->owner->ID);
        }
    }

    public function CompareRemoveLink() {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("remove/". $this->owner->ID);
        }
    }

    public function isCompared() {
        $products = Controller::curr()->getRequest()->getSession()->get("ProductComparisons");

        if ($products) {
            $products = explode(",", $products);

            return in_array($this->owner->ID, $products);
        }

        return false;
    }

    public function addAllFeaturesFromGroup($groupid){
        if( $group = FeatureGroup::get()->byID($groupid) ){
            $features = $group->Features();
            $sort = $this->owner->Features()->max('Sort'); // not correct way

            foreach( $features as $feature ){
                // add empty ProductFeatureValue if not present
                if( !$this->owner->Features()->filter('FeatureID',$feature->ID)->first() ){
                    // does not exist yet, so add
                    $productFeatureValue = ProductFeatureValue::create();
                    $productFeatureValue->FeatureID = $feature->ID;
                    $productFeatureValue->ProductID = $this->owner->ID; // seems to be obsolete, item is linked via many_many
                    $productFeatureValue->write();
                    $this->owner->Features()->add($productFeatureValue,['Sort'=>$sort]);
                    $sort++;
                }
            }
        }
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        $this->owner->Features()->removeAll();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if( isset($_POST['QuickAddFeatureGroupID']) && $groupid=(int) $_POST['QuickAddFeatureGroupID'] ){
            $this->addAllFeaturesFromGroup($groupid);
        }
    }

}
