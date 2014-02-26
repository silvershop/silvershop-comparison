<?php

/**
 * @package shop_comparsion
 */
class ProductComparisonPage extends Page {

	/**
	 * @config
	 * @var int
	 */
	private static $max_product_comparsions;


	/**
	 * @var string
	 */
	private static $icon = 'shop_comparison/images/compare.png';

}

/**
 * @package shop_comparsion
 */
class ProductComparisonPage_Controller extends Page_Controller {
	
	private static $allowed_actions = array(
		'add',
		'remove'
	);

	public function Comp() {
		return Product::get()->filter("ID", $this->getSelectionIDs());
	}

	/**
	 * Returns an {@link ArrayList} with all the values up to 
	 * maxproductcomparsions filled with objects. For instance, when generating
	 * a fixed 4 column table, the last columns can be empty. Checking for a 
	 * product is done via if $Product
	 *
	 * <code>
	 *	<% loop ComparedTableList %>
	 *		<div class="col-3">
	 *			<% if Product %>$Name<% end_if %>
	 *		</div>
	 *	<% end_loop %>
	 *
	 * Ensure you have set MaxProductComparsions through the config API.
	 *
	 * @return ArrayList
	 */
	public function getComparedTableList() {
		if($max = Config::inst()->get('ProductComparisonPage', 'max_product_comparsions')) {
			$output = new ArrayList();
			$products = $this->Comp();
			$previousHadProduct = true;

			for($i = 1; $i <= $max; $i++) {
				$product = $products->limit(1, $i - 1)->First();

				$output->push(new ArrayData(array(
					'First' => ($i == 1),
					'Last' => ($i == $max),
					'Product' => $product,
					'IsFirstNonProduct' => (!$product && $previousHadProduct)
				)));


				if(!$product) {
					$previousHadProduct = false;
				}
			}

			return $output;
		}

		return null;
	}


	public function ValuesForFeature($id, $pad = false) {
		$out = new Arraylist();

		foreach($this->Comp() as $comp){
			$out->push(
				ProductFeatureValue::get()
					->filter("ProductID",$comp->ID)
					->filter("FeatureID",$id)
					->first()
			);
		}

		if($max = Config::inst()->get('ProductComparisonPage', 'max_product_comparsions')) {
			if($pad && $out->count() < $max) {
				for($i = $out->count(); $i < $max; $i++) {
					$out->push(new ArrayData(array(
						'Padded' => true
					)));
				}
			}
		}

		return $out;
	}

	public function Features() {
		 return Feature::get()
		 	->leftJoin("ProductFeatureValue","\"Feature\".\"ID\" = \"ProductFeatureValue\".\"FeatureID\"")
		 	->filter("ProductID", $this->getSelectionIDs());
	}

	public function add($request) {
		$result = $this->addToSelection($request->param('ID'));

		if(Director::is_ajax()) {
			if($result === null) {
				$this->response->setStatusCode(404);

				return $this->renderWith('CompareMessage_Missing');
			} else if($result === false) {
				return $this->customise(new ArrayData(array(
					'Count' => Config::inst()->get('ProductComparisonPage', 'max_product_comparsions')
				)))->renderWith('CompareMessage_Exceeded');
			} else {
				return $this->renderWith('CompareMessage_Success');
			}

			return $this->response;
		}

		$this->redirect($this->Link());
	}

	public function remove($request) {
		$result = $this->removeFromSelection($request->param('ID'));

		if(Director::is_ajax()) {
			if($result === null) {
				$this->response->setStatusCode(404);
			}

			return;
		}

		$this->redirect($this->Link());
	}

	/**
	 * @param int $id
	 * 
	 * @return bool|null
	 */
	public function addToSelection($id) {
		if($product = Product::get()->byID($id)) {
			$all = $this->getSelectionIDs();
			$all[$id] = $id;

			if($max = Config::inst()->get('ProductComparisonPage', 'max_product_comparsions')) {
				if(count($all) > $max) {
					return false;
				}
			}

			$this->setSelectionIDs($all);

			return true;
		}

		return null;
	}

	/**
	 * @param int $id
	 *
	 * @return bool|null
	 */
	public function removeFromSelection($id) {
		if($product = Product::get()->byID($id)) {
			$all = $this->getSelectionIDs();
			
			if(isset($all[$id])) {
				unset($all[$id]);
			}

			$this->setSelectionIDs($all);

			return true;
		}

		return null;
	}

	private function setSelectionIDs(array $ids) {
		Session::set("ProductComparisons", implode(',',$ids));
	}

	private function getSelectionIDs() {
		if($ids = Session::get("ProductComparisons")) {
			$ids = explode(',',$ids);
		
			return array_combine($ids, $ids);
		}

		return array();
	}

}