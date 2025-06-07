<?php

namespace SilverShop\Comparison\Control;

use SilverStripe\Security\SecurityToken;
use SilverShop\Comparison\Model\Feature;
use SilverStripe\Control\Controller;

class ProductFeatureValueFieldController extends Controller
{
    private static array $allowed_actions = [
        'index'
    ];

    public function index($request) {
        if (!SecurityToken::inst()->checkRequest($request)) {
            return $this->httpError(403);
        }

        $id = $request->getVar('ID');

        if (!$id) {
            return $this->httpError(400);
        }

        $feature = Feature::get()->byId($id);

        if (!$feature) {
            return $this->httpError(404);
        }

        if (!$feature->canView()) {
            return $this->httpError(403);
        }

        $field = $feature->getValueField()->setName($request->getVar('Name'));

        return $field->forTemplate();
    }
}
