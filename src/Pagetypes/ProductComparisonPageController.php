<?php

namespace SilverShop\Comparison\Pagetypes;

use Page_Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Director;

class ProductComparisonPageController extends PageController
{
    private static $allowed_actions = [
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
     *
     * @return ArrayList
     */
    public function getComparedTableList() {
        if($max = Config::inst()->get(ProductComparisonPage::class, 'max_product_Comparisons')) {
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

        if ($max = Config::inst()->get(ProductComparisonPage::class, 'max_product_Comparisons')) {
            if ($pad && $out->count() < $max) {
                for($i = $out->count(); $i < $max; $i++) {
                    $out->push(new ArrayData(array(
                        'Padded' => true
                    )));
                }
            }
        }

        return $out;
    }

    public function add($request) {
        $result = $this->addToSelection($request->param('ID'));

        if (Director::is_ajax()) {
            if($result === null) {
                $this->response->setStatusCode(404);

                return $this->renderWith('CompareMessage_Missing');
            } else if($result === false) {
                return $this->customise(new ArrayData(array(
                    'Count' => Config::inst()->get(ProductComparisonPage::class, 'max_product_Comparisons')
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

        if (Director::is_ajax()) {
            if($result === null) {
                $this->response->setStatusCode(404);
            }

            return;
        }

        $this->redirect($this->Link());
    }

}
