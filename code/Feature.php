<?php

class Feature extends Dataobject{

	private static $db = array(
		'Title' => 'Varchar',
		'Unit' => 'Varchar',
		'ValueType' => "Enum('Boolean,Number,String','String')"
	);
	private static $has_many = array(
		"Products" => "Product_Feature"
	);
	private static $has_one = array(	
		"Group" => "FeatureGroup"
	);
	private static $belongs_many_many = array(
		"Product" => "Product"
	);
	private static $summary_fields = array(
		"Title" => "Title",
		"Unit" => "Unit"
	);
	private static $singular_name = "Feature";
	private static $plural_name = "Features";

	function getCMSFields(){
		$fields = new FieldList(
			TextField::create("Title"),
			TextField::create("Unit"),
			DropdownField::create("ValueType","Value Type", $this->dbObject('ValueType')->enumValues())
		);
		$groups = FeatureGroup::get();
		if($groups->exists()){
			$fields->insertAfter(
				DropdownField::create("GroupID","Group",$groups->map()->toArray())
					->setHasEmptyDefault(true)
			,"Unit");
		}

		return $fields;
	}

	function summaryFields(){
		$fields = parent::summaryFields();
		if(FeatureGroup::get()->exists()){
			$fields['Group.Title'] = 'Group';
		}
		return $fields;
	}

	function getValueField(){
		$fields = array(
			'Boolean' => CheckboxField::create("Value"),
			'Number' => NumericField::create("Value"),
			'String' => TextField::create("Value")
		);
		return $fields[$this->ValueType];
	}

	//TODO: on before delete - delete values

	//validate: must have title

}
