<?php

namespace SilverShop\Comparison\Pagetypes;

use PageController;
use SilverShop\Comparison\Model\ProductFeatureValue;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\ArrayData;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * @extends PageController<ProductComparisonPage>
 */
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
     *
     * @return ArrayList<ArrayData>|null
     */
    public function getComparedTableList(): ?ArrayList
    {
        if ($max = Config::inst()->get(ProductComparisonPage::class, 'max_product_comparisons')) {
            $output = ArrayList::create();
            $products = $this->data()->Comp();
            $previousHadProduct = true;

            for ($i = 1; $i <= $max; $i++) {
                $product = $products?->limit(1, $i - 1)->first();

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


    /**
     * @return ArrayList<ProductFeatureValue|ArrayData>
     */
    public function ValuesForFeature(int $id, bool $pad = false): ArrayList
    {
        $outputArrayList = ArrayList::create();

        foreach ($this->data()->Comp() ?? [] as $comp) {
            $value = ProductFeatureValue::get()
                ->filter("ProductID", $comp->ID)
                ->filter("FeatureID", $id)
                ->first();

            // keep columns aligned with the compared products when a product has no value for this feature
            $outputArrayList->push($value ?? ArrayData::create());
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

    public function add(HTTPRequest $request): DBHTMLText|HTTPResponse
    {
        $result = $this->data()->addToSelection((int) $request->param('ID'));

        if ($request->isAjax()) {
            if ($result === null) {
                $this->response->setStatusCode(404);
                return $this->renderWith('SilverShop\\Comparison\\Includes\\CompareMessage_Missing');
            }

            if ($result === false) {
                return $this->customise(
                    ArrayData::create(
                        [
                            'Count' => Config::inst()->get(ProductComparisonPage::class, 'max_product_comparisons')
                        ]
                    )
                )->renderWith('SilverShop\\Comparison\\Includes\\CompareMessage_Exceeded');
            }

            return $this->renderWith('SilverShop\\Comparison\\Includes\\CompareMessage_Success');
        }

        return $this->redirect($this->Link());
    }

    public function remove(HTTPRequest $request): void
    {
        $result = $this->data()->removeFromSelection((int) $request->param('ID'));

        if ($request->isAjax()) {
            if ($result === null) {
                $this->response->setStatusCode(404);
            }

            return;
        }

        $this->redirect($this->Link());
    }
}
