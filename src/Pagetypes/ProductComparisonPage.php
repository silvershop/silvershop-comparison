<?php

namespace SilverShop\Comparison\Pagetypes;

use Page;
use SilverShop\Page\Product;
use SilverShop\Comparison\Model\Feature;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataList;

class ProductComparisonPage extends Page
{
    /**
     * @config
     * @var int
     */
    private static $max_product_Comparisons;

    /**
     * @var string
     */
    private static $icon = 'silvershop/comparison:images/compare.png';

    /**
     * @var string
     */
    private static $singular_name = 'Product Comparison Page';

    /**
     * @var string
     */
    private static $plural_name = 'Product Comparison Pages';

    /**
     * @param int $id
     *
     * @return bool|null
     */
    public function addToSelection($id) {
        if ($product = Product::get()->byID($id)) {
            $all = $this->getSelectionIDs();
            $all[$id] = $id;

            if ($max = static::config()->get('max_product_Comparisons')) {
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

    /**
     * @param array $ids
     *
     * @return ProductComparisonPage
     */
    protected function setSelectionIDs(array $ids) {
        Controller::curr()->getRequest()->getSession()->set("ProductComparisons", implode(',',$ids));

        return $this;
    }

    /**
     * @return array
     */
    protected function getSelectionIDs() {
        if($ids = Controller::curr()->getRequest()->getSession()->get("ProductComparisons")) {
            $ids = explode(',',$ids);

            return array_combine($ids, $ids);
        }

        return array();
    }

    /**
     * @return DataList
     */
    public function Comp() {
        $ids = $this->getSelectionIDs();
        if($ids){
            return Product::get()->filter("ID", $ids);
        }
    }


    /**
     * @return int
     */
    public function getProductCount() {
        return count($this->getSelectionIDs());
    }

    /**
     * @return DataList
     */
    public function Features() {
         return Feature::get()
            ->leftJoin("SilverShop_ProductFeatureValue","\"SilverShop_Feature\".\"ID\" = \"SilverShop_ProductFeatureValue\".\"FeatureID\"")
            ->filter("ProductID", $this->getSelectionIDs());
    }

}
