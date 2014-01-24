<?php

/**
 * Pivot table. Connects products with features, but also includes a value
 */
class ProductFeatureValue extends DataObject{

	private static $db = array(
		"Value" => "Varchar"
	);

	private static $has_one = array(
		"Product" => "Product",
		"Feature" => "Feature"
	);

	private static $summary_fields	=  array(
		"Feature.Title" => "Feature",
		"Value" => "Value",
		"Feature.Unit" => "Unit"
	);

	private static $singular_name = "Feature";
	private static $plural_name = "Features";

	function getCMSFields(){
		$fields = new FieldList();
		$feature = $this->Feature();
		if($feature->exists()){
			$fields->push(ReadonlyField::create("FeatureTitle","Feature", $feature->Title));
			$fields->push($feature->getValueField());
		}else{
			$features = Feature::get()
				->filter("ID:not",$this->Product()->Features()->getIDList());
			$fields->push(DropdownField::create("FeatureID","Feature",$features->map()->toArray()));
		}
		
		return $fields;
	}

	function getTitle(){
		return $this->Feature()->Title;
	}

	//validation: must have a product, feature, then a value must be from given feature
		//can't add feature to a product that already has it

}