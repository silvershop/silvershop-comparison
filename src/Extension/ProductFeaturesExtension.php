<?php

namespace SilverShop\Comparison\Extension;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldSortableRows;
use SilverStripe\Forms\DropdownField;
use SilverShop\Comparison\Model\Feature;
use SilverStripe\Forms\HiddenField;
use SilverShop\Comparison\Pagetypes\ProductComparisonPage;

class ProductFeaturesExtension extends DataExtension
{
    private static $has_many = [
        'Features' => 'ProductFeatureValue'
    ];

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab("Root.Features",
            $grid = GridField::create("Features", "Features", $this->owner->Features(),
                GridFieldConfig_RecordEditor::create()
            )
        );

        $grid->getConfig()
            ->removeComponentsByType('GridFieldDataColumns')
            ->removeComponentsByType('GridFieldAddNewButton')
            ->addComponent(new GridFieldAddNewInlineButton())
            ->addComponent(new GridFieldEditableColumns())
            ->addComponent(new GridFieldSortableRows());

        $grid->getConfig()->getComponentByType('GridFieldEditableColumns')->setDisplayFields(array(
            'FeatureID'  => function($record, $column, $grid) {
                $dropdown = new DropdownField($column, 'Feature', Feature::get()->map('ID', 'Title')->toArray());
                $dropdown->addExtraClass('on_feature_select_fetch_value_field');

                return $dropdown;
            },
            'Value' => function($record, $column, $grid) {
                if($record->FeatureID) {
                    $field = $record->Feature()->getValueField();
                    $field->setName($column);

                    return $field;
                }

                return new HiddenField($column);
            }
        ));
    }

    public function CompareLink() {
        if ($this->isCompared()) {
            return $this->CompareRemoveLink();
        }

        return $this->CompareAddLink();
    }

    public function CompareAddLink() {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("add/". $this->owner->ID);
        }
    }

    public function CompareRemoveLink() {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("remove/". $this->owner->ID);
        }
    }

    public function isCompared() {
        $products = Controller::curr()->getSession()->get("ProductComparisons");

        if ($products) {
            $products = explode(",", $products);

            return in_array($this->owner->ID, $products);
        }

        return false;
    }
}
