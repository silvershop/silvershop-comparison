<?php

class ProductComparisonPage extends Page{
	

}

class ProductComparisonPage_Controller extends Page_Controller{
	
	//add product to compare
	//remove product from compare
	//display comparison
	private static $allowed_actions = array(
		'add'
	);

	function Comp(){
		return Product::get()->filter("ID",$this->getSelectionIDs());
	}

	function Features(){
		//get either the combination of, or the intersection of features for each product
		
	}

	function add($request){
		$this->addToSelection($request->param('ID'));
		$this->redirect($this->Link());
	}

	function remove($request){
		$this->removeFromSelection($request->param('ID'));
		$this->redirect($this->Link());
	}

	function addToSelection($id){
		if($product = Product::get()->byID($id)){
			$all = $this->getSelectionIDs();
			$all[$id] = $id;
			$this->setSelectionIDs($all);
		}
		//exception
	}

	function removeFromSelection($id){
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
			return explode(',',$ids);
		}
		return array();
	}

}