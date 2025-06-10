<?php

namespace SilverShop\Comparison\Pagetypes;

use Page;
use SilverShop\Comparison\Model\Feature;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataList;

class ProductComparisonPage extends Page
{
    private static int $max_product_comparisons = 0;

    private static string $icon = 'silvershop/comparison:images/compare.png';

    private static string $singular_name = 'Product Comparison Page';

    private static string $plural_name = 'Product Comparison Pages';

    private static string $table_name = 'SilverShop_ProductComparisonPage';

    public function addToSelection(int $id): ?bool
    {
        if ($product = Product::get()->byID($id)) {
            $all = $this->getSelectionIDs();
            $all[$id] = $id;

            if ($max = static::config()->get('max_product_comparisons')) {
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

    protected function setSelectionIDs(array $ids): static
    {
        Controller::curr()->getRequest()->getSession()->set("ProductComparisons", implode(',', $ids));

        return $this;
    }

    protected function getSelectionIDs(): array
    {
        if ($ids = Controller::curr()->getRequest()->getSession()->get("ProductComparisons")) {
            $ids = explode(',', $ids);

            return array_combine($ids, $ids);
        }

        return [];
    }

    public function Comp(): ?DataList
    {
        $ids = $this->getSelectionIDs();
        if ($ids) {
            return Product::get()->filter("ID", $ids);
        }
        return null;
    }

    public function getProductCount(): int
    {
        return count($this->getSelectionIDs());
    }

    public function Features(): DataList
    {
         return Feature::get()
             ->leftJoin("SilverShop_ProductFeatureValue", "\"SilverShop_Feature\".\"ID\" = \"SilverShop_ProductFeatureValue\".\"FeatureID\"")
             ->filter("ProductID", $this->getSelectionIDs());
    }
}
