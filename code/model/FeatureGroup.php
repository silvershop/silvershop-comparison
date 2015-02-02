<?php

/**
 * @package shop_comparsion
 */
class FeatureGroup extends DataObject {

	private static $db = array(
		"Title" => "Varchar"
	);

	private static $has_many = array(
		"Features" => "Feature"
	);

	private static $singular_name = "Feature Group";

	private static $plural_name = "Feature Groups";
}
