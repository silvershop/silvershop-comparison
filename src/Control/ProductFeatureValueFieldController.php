<?php

namespace SilverShop\Comparison\Control;

use SilverShop\Comparison\Model\Feature;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\SecurityToken;

class ProductFeatureValueFieldController extends Controller
{
    private static array $allowed_actions = [
        'index'
    ];

    public function index(HTTPRequest $request): string
    {
        if (!SecurityToken::inst()->checkRequest($request)) {
            $this->httpError(403);
        }

        $id = $request->getVar('ID');

        if (!$id) {
            $this->httpError(400);
        }

        $feature = Feature::get()->byId($id);

        if (!$feature) {
            $this->httpError(404);
        }

        if (!$feature->canView()) {
            $this->httpError(403);
        }

        $name = $request->getVar('Name');
        $field = $feature->getValueField()->setName(is_string($name) ? $name : 'Value');

        return $field->forTemplate();
    }
}
