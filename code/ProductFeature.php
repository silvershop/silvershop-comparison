<?php

class Feature extends Dataobject{

	private static $db = array(
		'Title' => 'Varchar',
		'Unit' => 'Varchar'
	);
	private static $has_many = array(
		"Values" => "FeatureValue",
		"Products" => "Product_Feature"
	);
	private static $has_one = array(	
		"Group" => "ProductFeatureGroup"
	);
	private static $belongs_many_many = array(
		"Product" => "Product"
	);
	private static $summary_fields = array(
		"Title" => "Title",
		"Unit" => "Unit",
		"Values.count" => "Values"
	);
	private static $singular_name = "Feature";
	private static $plural_name = "Features";

	function getCMSFields(){
		$fields = new FieldList(
			TextField::create("Title"),
			TextField::create("Unit")
		);
		if($this->isInDB()){
			$fields->push(GridField::create("Values","Values",
				$this->Values(),
				GridFieldConfig_RecordEditor::create()
			));
		}
		$groups = FeatureGroup::get();
		if($groups->exists()){
			$fields->push(
				ListboxField::create("GroupID","Group",$groups->map()->toArray())
			);
		}

		return $fields;
	}

	//validate: must have title

}

class FeatureValue extends Dataobject{

	private static $db = array(
		"Value" => "Varchar"
	);
	private static $has_one = array(
		"Feature" => "Feature"
	);
	private static $summary_fields = array(
		"Value" => "Value",
		"Feature.Unit" => "Unit"
	);
	private static $singular_name = "Value";
	private static $plural_name = "Values";

	function getCMSFields(){
		return new FieldList(
			TextField::create("Value")
		);
	}

	function getTitle(){
		return $this->Value;
	}

	function forTemplate(){
		return $this->Value;
	}

	//validate: must have value

}

class FeatureGroup extends DataObject{

	private static $db = array(
		"Title" => "Varchar"
	);

	private static $has_many = array(
		"Features" => "Feature"
	);

	private static $singular_name = "Group";
	private static $plural_name = "Groups";

	//validate: must have title
}
