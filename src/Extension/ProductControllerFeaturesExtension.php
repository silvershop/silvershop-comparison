<?php

namespace SilverShop\Comparison\Extension;

use SilverShop\Comparison\Model\Feature;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;


class ProductControllerFeaturesExtension extends Extension
{
    /**
     * Override features list with grouping.
     */
    public function GroupedFeatures($showungrouped = false): ArrayList
    {
        $features = $this->owner->Features()
            ->innerJoin("SilverShop_Feature","SilverShop_Feature.ID = SilverShop_ProductFeatureValue.FeatureID");

        $featuresids = $features->getIDList();

        // check sorting option
        $sortByGroup = Config::inst()->get(Feature::class, 'sort_features_by_group');

        //figure out feature groups
        $groups = FeatureGroup::get()
            ->innerJoin("SilverShop_Feature","SilverShop_Feature.GroupID = SilverShop_FeatureGroup.ID")
            ->innerJoin("SilverShop_ProductFeatureValue","SilverShop_Feature.ID = SilverShop_ProductFeatureValue.FeatureID")
            ->where("SilverShop_ProductFeatureValue.ID IN (" . implode(',',$featuresids) .")" );

        if( $sortByGroup ){
            $groups = $groups->Sort('Title ASC');
        }

        $groupids = $groups->getIDList();

        //pack existing features into seperate lists
        $result = new ArrayList();
        if(!empty($groupids)){
            foreach($groupids as $groupid) {
                $group = FeatureGroup::get()->byID($groupid);
                if( $sortByGroup ){
                    // sort on order within group
                    $children = $features->filter("GroupID", $groupid)->sort("\"SilverShop_Feature\".\"Sort\"");
                } else {
                    // sort on order at product level, default
                    $children = $features->filter("GroupID", $groupid);
                }
                $result->push(new ArrayData(
                    [
                        'Group' => $group,
                        'Children' => $children
                    ]
                ));
            }

            if( $showungrouped ) {
                $ungrouped = $features->filter("GroupID:not", $groupids);
                if ($ungrouped->exists() && $showungrouped) {
                    $result->push(new ArrayData(array(
                        'Children' => $ungrouped
                    )));
                }
            }
        }

        return $result;
    }
}
