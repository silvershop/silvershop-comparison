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
		"Group" => "FeatureGroup"
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

	//TODO: on before delete - delete values

	//validate: must have title

}
