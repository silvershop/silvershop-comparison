<?php

/**
 * @package shop_comparsion
 */
class ProductFeaturesExtension extends DataExtension {

	private static $has_many = array(
		'Features' => 'ProductFeatureValue'
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldToTab("Root.Features",
			$grid = GridField::create("Features", "Features", $this->owner->Features(),
				GridFieldConfig_RecordEditor::create()
			)
		);

		$grid->getConfig()
			->removeComponentsByType('GridFieldDataColumns')
			->removeComponentsByType('GridFieldAddNewButton')
			->addComponent(new GridFieldAddNewInlineButton())
			->addComponent(new GridFieldEditableColumns())
			->addComponent(new GridFieldOrderableRows());

		$grid->getConfig()->getComponentByType('GridFieldEditableColumns')->setDisplayFields(array(
			'FeatureID'  => function($record, $column, $grid) {
				return new DropdownField($column, 'Feature', Feature::get()->map('ID', 'Title')->toArray());
			},
			'Value' => function($record, $column, $grid) {
				return new TextField($column, 'Value');
			}
		));
	}

	public function CompareLink() {
		if($this->isCompared()) {
			return $this->CompareRemoveLink();
		}
		
		return $this->CompareAddLink();
	}

	public function CompareAddLink() {
		if($page = ProductComparisonPage::get()->first()) {
			return $page->Link("add/". $this->owner->ID);
		}
	}

	public function CompareRemoveLink() {
		if($page = ProductComparisonPage::get()->first()) {
			return $page->Link("remove/". $this->owner->ID);
		}
	}

	public function isCompared() {
		$products = Session::get("ProductComparisons");

		if($products) {
			$products = explode(",", $products);

			return in_array($this->owner->ID, $products);
		}

		return false;
	}
}

/**
 * @package shop_comparsion
 */
class Product_ControllerFeaturesExtension extends Extension {

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