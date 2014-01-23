<?php

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