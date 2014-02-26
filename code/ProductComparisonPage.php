<?php

/**
 * @package shop_comparsion
 */
class ProductComparisonPage extends Page{
	
	private static $icon = 'shop_comparison/images/compare.png';

}

/**
 * @package shop_comparsion
 */
class ProductComparisonPage_Controller extends Page_Controller {
	
	private static $allowed_actions = array(
		'add','remove'
	);

	public function Comp() {
		return Product::get()->filter("ID",$this->getSelectionIDs());
	}

	public function ValuesForFeature($id){
		$out = new Arraylist();
		foreach($this->Comp() as $comp){
			$out->push(
				ProductFeatureValue::get()
					->filter("ProductID",$comp->ID)
					->filter("FeatureID",$id)
					->first()
			);
		}
		return $out;
	}

	public function Features(){
		 return Feature::get()
		 	->leftJoin("ProductFeatureValue","\"Feature\".\"ID\" = \"ProductFeatureValue\".\"FeatureID\"")
		 	->filter("ProductID", $this->getSelectionIDs());
	}

	public function add($request){
		$this->addToSelection($request->param('ID'));
		//TODO: if first, then redirect back with success message
		$this->redirect($this->Link());
	}

	public function remove($request){
		$this->removeFromSelection($request->param('ID'));
		$this->redirect($this->Link());
	}

	public function addToSelection($id){
		if($product = Product::get()->byID($id)){
			$all = $this->getSelectionIDs();
			$all[$id] = $id;
			$this->setSelectionIDs($all);
		}
		//exception
	}

	public function removeFromSelection($id){
		if($product = Product::get()->byID($id)){
			$all = $this->getSelectionIDs();
			unset($all[$id]);
			$this->setSelectionIDs($all);
		}
		//exception
	}

	private function setSelectionIDs(array $ids){
		Session::set("ProductComparisons",implode(',',$ids));
	}

	private function getSelectionIDs(){
		if($ids = Session::get("ProductComparisons")){
			$ids = explode(',',$ids);
			return array_combine($ids, $ids);
		}
		return array();
	}

}