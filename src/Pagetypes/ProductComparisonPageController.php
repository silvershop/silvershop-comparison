<?php

namespace SilverShop\Comparison\Pagetypes;

use PageController;
use SilverShop\Comparison\Model\ProductFeatureValue;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class ProductComparisonPageController extends PageController
{
    private static array $allowed_actions = [
        'add',
        'remove'
    ];

    /**
     * Returns an {@link ArrayList} with all the values up to
     * maxproductComparisons filled with objects. For instance, when generating
     * a fixed 4 column table, the last columns can be empty. Checking for a
     * product is done via if $Product
     *
     * <code>
     *  <% loop ComparedTableList %>
     *      <div class="col-3">
     *          <% if Product %>$Name<% end_if %>
     *      </div>
     *  <% end_loop %>
     *
     * Ensure you have set MaxProductComparisons through the config API.
     */
    public function getComparedTableList(): ?ArrayList
    {
        if ($max = Config::inst()->get(ProductComparisonPage::class, 'max_product_comparisons')) {
            $output = ArrayList::create();
            $products = $this->data()->Comp();
            $previousHadProduct = true;

            for ($i = 1; $i <= $max; $i++) {
                $product = $products->limit(1, $i - 1)->First();

                $output->push(
                    ArrayData::create(
                        [
                            'First' => ($i == 1),
                            'Last' => ($i == $max),
                            'Product' => $product,
                            'IsFirstNonProduct' => (!$product && $previousHadProduct)
                        ]
                    )
                );


                if (!$product) {
                    $previousHadProduct = false;
                }
            }

            return $output;
        }

        return null;
    }


    public function ValuesForFeature($id, $pad = false): ArrayList
    {
        $outputArrayList = ArrayList::create();

        foreach ($this->data()->Comp() as $comp) {
            $outputArrayList->push(
                ProductFeatureValue::get()
                    ->filter("ProductID", $comp->ID)
                    ->filter("FeatureID", $id)
                    ->first()
            );
        }

        if (($max = Config::inst()->get(ProductComparisonPage::class, 'max_product_comparisons')) && ($pad && $outputArrayList->count() < $max)) {
            for ($i = $outputArrayList->count(); $i < $max; $i++) {
                $outputArrayList->push(
                    ArrayData::create(
                        [
                            'Padded' => true
                        ]
                    )
                );
            }
        }

        return $outputArrayList;
    }

    public function add($request)
    {
        $result = $this->data()->addToSelection($request->param('ID'));

        if (Director::is_ajax()) {
            if ($result === null) {
                $this->response->setStatusCode(404);
                return $this->renderWith('CompareMessage_Missing');
            }

            if ($result === false) {
                return $this->customise(
                    ArrayData::create(
                        [
                            'Count' => Config::inst()->get(ProductComparisonPage::class, 'max_product_comparisons')
                        ]
                    )
                )->renderWith('CompareMessage_Exceeded');
            }

            return $this->renderWith('CompareMessage_Success');
        }

        $this->redirect($this->Link());
        return null;
    }

    public function remove($request): void
    {
        $result = $this->data()->removeFromSelection($request->param('ID'));

        if (Director::is_ajax()) {
            if ($result === null) {
                $this->response->setStatusCode(404);
            }

            return;
        }

        $this->redirect($this->Link());
    }
}
