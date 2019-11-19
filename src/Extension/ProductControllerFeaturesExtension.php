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
            ->innerJoin("SilverShop_Feature","SilverShop_Feature.ID = SilverShop_ProductFeatureValue.FeatureID");

        $featuresids = $features->getIDList();

        //figure out feature groups
        $groupids = FeatureGroup::get()
            ->innerJoin("SilverShop_Feature","SilverShop_Feature.GroupID = SilverShop_FeatureGroup.ID")
            ->innerJoin("SilverShop_ProductFeatureValue","SilverShop_Feature.ID = SilverShop_ProductFeatureValue.FeatureID")
            ->where("SilverShop_ProductFeatureValue.ID IN (" . implode(',',$featuresids) .")" )
            ->getIDList();

        //pack existing features into seperate lists
        $result = new ArrayList();
        if(!empty($groupids)){
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
        }

        return $result;
    }
}
