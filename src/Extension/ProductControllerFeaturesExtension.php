<?php

namespace SilverShop\Comparison\Extension;

use SilverStripe\Core\Extension;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;


class ProductControllerFeaturesExtension extends Extension
{
    /**
     * Override features list with grouping.
     */
    public function GroupedFeatures($showungrouped = false)
    {
        $features = $this->owner->Features()
            ->innerJoin("Feature","Feature.ID = ProductFeatureValue.FeatureID");

        //figure out feature groups
        $groupids = FeatureGroup::get()
                ->innerJoin("Feature","Feature.GroupID = FeatureGroup.ID")
                ->innerJoin("ProductFeatureValue","Feature.ID = ProductFeatureValue.FeatureID")
                ->filter("ProductID",$this->owner->ID)
                ->getIDList();
        //pack existin features into seperate lists
        $result = new ArrayList();

        foreach($groupids as $groupid) {
            $group = FeatureGroup::get()->byID($groupid);
            $result->push(new ArrayData(array(
                'Group' => $group,
                'Children' => $features->filter("GroupID", $groupid)
            )));
        }

        $ungrouped = $features->filter("GroupID:not", $groupids);

        if ($ungrouped->exists() && $showungrouped) {
            $result->push(new ArrayData(array(
                'Children' => $ungrouped
            )));
        }

        return $result;
    }
}
