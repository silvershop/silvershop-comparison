<?php

/**
 * @package shop_comparsion
 */
class ProductControllerFeaturesExtension extends Extension {

	/**
	 * Override features list with grouping.
	 */
	function GroupedFeatures($showungrouped = false){
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

		//ungrouped
		$ungrouped = $features->filter("GroupID:not", $groupids);
		if($ungrouped->exists() && $showungrouped){
			$result->push(new ArrayData(array(
				'Children' => $ungrouped
			)));
		}

		return $result;
	}
}