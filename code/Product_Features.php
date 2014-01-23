<?php

/**
 * Pivot table. Connects products with features, but also includes a value
 */
class Product_Features extends DataObject{

	private static $has_one = array(
		"Product" => "Product",
		"Feature" => "Feature",
		"Value" => "FeatureValue"
	);

	private static $summary_fields	=  array(
		"Feature.Title" => "Feature",
		"Value.Value" => "Value",
		"Feature.Unit" => "Unit"
	);

	private static $singular_name = "Feature";
	private static $plural_name = "Features";

	function getCMSFields(){
		$fields = new FieldList();
		$feature = $this->Feature();
		if($feature->exists()){
			$fields->push(ReadonlyField::create("FeatureTitle","Feature", $feature->Title));
			$values = $feature->Values();
			$editfeaturelink = "admin/catalog/Feature/EditForm/field/Feature/item/$feature->ID/edit";
			if($values->exists()){
				$fields->push(
					DropdownField::create("ValueID","Value", $values->map('ID','Value'))
				);
			}else{
				$fields->push(LiteralField::create("ValueID","<p class=\"message bad\">This feature has no values.</p>"));
				//TODO: allow entering custom values
			}
			$fields->push(LiteralField::create("AddValues","<a href=\"$editfeaturelink\">Edit / add feature values</a>"));
		}else{
			$features = Feature::get()
				->filter("ID:not",$this->Product()->Features()->getIDList());
			$fields->push(DropdownField::create("FeatureID","Feature",$features->map()->toArray()));
			$fields->push(LiteralField::create("ValueID","<p class=\"message warning\">You can select a value for this feature once you have saved.</p>"));
		}
		
		return $fields;
	}

	function getTitle(){
		return $this->Feature()->Title;
	}

	//validation: must have a product, feature, then a value must be from given feature
		//can't add feature to a product that already has it

}