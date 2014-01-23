<?php

class ProductFeaturesExtension extends DataExtension{

	private static $has_many = array(
		'Features' => 'Product_Features'
	);

	function updateCMSFields(FieldList $fields){
		$fields->addFieldToTab("Root.Features",
			GridField::create("Features", "Features", $this->owner->Features(),
				GridFieldConfig_RecordEditor::create()
			)
		);
	}

	function CompareLink(){
		if($comparepage = ProductComparisonPage::get()->first()){
			return Controller::join_links(
				$comparepage->Link(),
				"add",
				$this->owner->ID
			);
		}
	}

	function CompareRemoveLink(){
		if($comparepage = ProductComparisonPage::get()->first()){
			return Controller::join_links(
				$comparepage->Link(),
				"remove",
				$this->owner->ID
			);
		}
	}

}

class Product_ControllerFeaturesExtension extends Extension{

	/**
	 * Override features list with grouping.
	 */
	function GroupedFeatures($showungrouped = false){
		$features = $this->owner->Features()
			->innerJoin("Feature","Feature.ID = Product_Features.FeatureID");
		//figure out feature groups
		$groupids = FeatureGroup::get()
				->innerJoin("Feature","Feature.GroupID = FeatureGroup.ID")
				->innerJoin("Product_Features","Feature.ID = Product_Features.FeatureID")
				->filter("ProductID",$this->owner->ID)
				->getIDList();
		//pack existin features into seperate lists
		$result = new ArrayList();
		foreach($groupids as $groupid){
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