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
     * @var    int
     */
    private static $max_product_Comparisons;

    private static string $icon = 'silvershop/comparison:images/compare.png';

    private static string $singular_name = 'Product Comparison Page';

    private static string $plural_name = 'Product Comparison Pages';

    public function addToSelection(int $id): ?bool
    {
        if ($product = Product::get()->byID($id)) {
            $all = $this->getSelectionIDs();
            $all[$id] = $id;

            if ($max = static::config()->get('max_product_Comparisons')) {
                if (count($all) > $max) {
                    return false;
                }
            }

            $this->setSelectionIDs($all);

            return true;
        }

        return null;
    }

    public function removeFromSelection(int $id): ?bool
    {
        if ($product = Product::get()->byID($id)) {
            $all = $this->getSelectionIDs();

            if (isset($all[$id])) {
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
    protected function setSelectionIDs(array $ids)
    {
        Controller::curr()->getRequest()->getSession()->set("ProductComparisons", implode(',', $ids));

        return $this;
    }

    /**
     * @return array
     */
    protected function getSelectionIDs()
    {
        if ($ids = Controller::curr()->getRequest()->getSession()->get("ProductComparisons")) {
            $ids = explode(',', $ids);

            return array_combine($ids, $ids);
        }

        return [];
    }

    /**
     * @return DataList
     */
    public function Comp()
    {
        $ids = $this->getSelectionIDs();
        if ($ids) {
            return Product::get()->filter("ID", $ids);
        }
    }


    /**
     * @return int
     */
    public function getProductCount()
    {
        return count($this->getSelectionIDs());
    }

    /**
     * @return DataList
     */
    public function Features()
    {
         return Feature::get()
             ->leftJoin("SilverShop_ProductFeatureValue", "\"SilverShop_Feature\".\"ID\" = \"SilverShop_ProductFeatureValue\".\"FeatureID\"")
             ->filter("ProductID", $this->getSelectionIDs());
    }
}
