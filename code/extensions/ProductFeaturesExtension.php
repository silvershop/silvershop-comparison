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
				$dropdown = new DropdownField($column, 'Feature', Feature::get()->map('ID', 'Title')->toArray());
				$dropdown->addExtraClass('on_feature_select_fetch_value_field');

				return $dropdown;
			},
			'Value' => function($record, $column, $grid) {
				if($record->FeatureID) {
					$field = $record->Feature()->getValueField();
					$field->setName($column);

					return $field;
				}

				return new HiddenField($column);
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