<?php

namespace SilverShop\Comparison\Extension;

use SilverShop\Comparison\GridField\GridFieldConfig_ProductFeatures;
use SilverShop\Comparison\Model\Feature;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverShop\Comparison\Model\ProductFeatureValue;
use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\View\ArrayData;

/**
 * Class ProductFeaturesExtension
 *
 * @package SilverShop\Comparison\Extension
 *
 * @method  HasManyList<ProductFeatureValue> Features()
 * @extends Extension<(Product & static)>
 */
class ProductFeaturesExtension extends Extension
{
    private static array $has_many = [
        'Features' => ProductFeatureValue::class
    ];

    public function updateCMSFields(FieldList $fieldList): void
    {
        $gridFieldConfigProductFeatures = GridFieldConfig_ProductFeatures::create();

        if ($this->owner->exists()) {
            $sortByGroup = Config::inst()->get(Feature::class, 'sort_features_by_group');
            if ($sortByGroup) {
                $features = $this->owner->Features()
                    ->leftJoin('SilverShop_Feature', '"SilverShop_Feature"."ID"="SilverShop_ProductFeatureValue"."FeatureID"')
                    ->leftJoin('SilverShop_FeatureGroup', '"SilverShop_FeatureGroup"."ID"="SilverShop_Feature"."GroupID"')
                    ->Sort('"SilverShop_FeatureGroup"."Title" ASC, "SilverShop_Feature"."Sort" ASC');
            } else {
                $features = $this->owner->Features();
            }

            $grid = GridField::create("Features", "Features", $features, $gridFieldConfigProductFeatures);
            $fieldList->addFieldToTab("Root.Features", $grid);
        }

        // quick add all from feature group
        $dropdownField = DropdownField::create('QuickAddFeatureGroupID', 'Add all features from group', FeatureGroup::get()->map('ID', 'Title'));
        $dropdownField->setEmptyString('-');
        $dropdownField->setDescription('After save, all features of selected group will be added to the product');

        $fieldList->addFieldToTab('Root.Features', $dropdownField);
    }

    public function CompareLink(): ?string
    {
        if ($this->isCompared()) {
            return $this->CompareRemoveLink();
        }

        return $this->CompareAddLink();
    }

    public function CompareAddLink(): ?string
    {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("add/" . $this->owner->ID);
        }

        return null;
    }

    public function CompareRemoveLink(): ?string
    {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("remove/" . $this->owner->ID);
        }

        return null;
    }

    public function isCompared(): bool
    {
        $products = Controller::curr()->getRequest()->getSession()->get("ProductComparisons");

        if ($products) {
            $products = explode(",", (string) $products);

            return in_array($this->owner->ID, $products);
        }

        return false;
    }

    public function addAllFeaturesFromGroup($groupid): void
    {
        if ($group = FeatureGroup::get()->byID($groupid)) {
            $features = $group->Features();
            $sort = $this->owner->Features()->max('Sort') + 1;

            foreach ($features as $feature) {
                // add empty ProductFeatureValue if not present
                if (!$this->owner->Features()->filter('FeatureID', $feature->ID)->first()) {
                    // does not exist yet, so add
                    $productFeatureValue = ProductFeatureValue::create();
                    $productFeatureValue->FeatureID = $feature->ID;
                    $productFeatureValue->ProductID = $this->owner->ID; // seems to be obsolete, item is linked via many_many
                    $productFeatureValue->Sort = $sort;
                    $productFeatureValue->write();
                    $this->owner->Features()->add($productFeatureValue);
                    $sort++;
                }
            }
        }
    }

    /**
     * Override features list with grouping.
     */
    public function GroupedFeatures($showungrouped = false): ArrayList
    {
        $features = $this->owner->Features()
            ->innerJoin("SilverShop_Feature", "SilverShop_Feature.ID = SilverShop_ProductFeatureValue.FeatureID");

        $featuresids = $features->getIDList();

        // check sorting option
        $sortByGroup = Config::inst()->get(Feature::class, 'sort_features_by_group');

        //figure out feature groups
        $groups = FeatureGroup::get()
            ->innerJoin("SilverShop_Feature", "SilverShop_Feature.GroupID = SilverShop_FeatureGroup.ID")
            ->innerJoin("SilverShop_ProductFeatureValue", "SilverShop_Feature.ID = SilverShop_ProductFeatureValue.FeatureID")
            ->where("SilverShop_ProductFeatureValue.ID IN (" . implode(',', $featuresids) . ")");

        if ($sortByGroup) {
            $groups = $groups->Sort('Title ASC');
        }

        $groupids = $groups->getIDList();

        //pack existing features into seperate lists
        $arrayList = ArrayList::create();
        if (!empty($groupids)) {
            foreach ($groupids as $groupid) {
                $group = FeatureGroup::get()->byID($groupid);
                if ($sortByGroup) {
                    // sort on order within group
                    $children = $features->filter("GroupID", $groupid)->sort('"SilverShop_Feature"."Sort"');
                } else {
                    // sort on order at product level, default
                    $children = $features->filter("GroupID", $groupid);
                }

                $arrayList->push(
                    ArrayData::create(
                        [
                        'Group' => $group,
                        'Children' => $children
                        ]
                    )
                );
            }

            if ($showungrouped) {
                $ungrouped = $features->filter("GroupID:not", $groupids);
                if ($ungrouped->exists() && $showungrouped) {
                    $arrayList->push(
                        ArrayData::create(
                            [
                            'Children' => $ungrouped
                            ]
                        )
                    );
                }
            }
        }

        return $arrayList;
    }

    public function onBeforeDelete(): void
    {
        $this->owner->Features()->removeAll();
    }

    public function onAfterWrite(): void
    {
        if (isset($_POST['QuickAddFeatureGroupID']) && $groupid=(int) $_POST['QuickAddFeatureGroupID']) {
            $this->addAllFeaturesFromGroup($groupid);
        }
    }
}
