<?php

namespace SilverShop\Comparison\Extension;

use SilverShop\Comparison\Model\FeatureGroup;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\ORM\ManyManyList;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\DropdownField;
use SilverShop\Comparison\Model\Feature;
use SilverStripe\Forms\HiddenField;
use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverShop\Comparison\Model\ProductFeatureValue;
use XD\Basic\GridField\GridFieldConfig_Editable;


/**
 * Class ProductFeaturesExtension
 * @package SilverShop\Comparison\Extension
 *
 * @method ManyManyList Features
 */

class ProductFeaturesExtension extends DataExtension
{
    private static $has_many = [
        'Features' => ProductFeatureValue::class
    ];

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab("Root.Features",
            $grid = GridField::create("Features", "Features", $this->owner->Features(),
                GridFieldConfig_RecordEditor::create()
            )
        );

        /** @propery GridFieldConfig $config */
        $config = $grid->getConfig();

        $config->removeComponentsByType(GridFieldDataColumns::class)
            ->removeComponentsByType(GridFieldAddNewButton::class)
            ->removeComponentsByType(GridField_ActionMenu::class)
            ->removeComponentsByType(GridFieldDeleteAction::class)
            ->removeComponentsByType(GridFieldEditButton::class)
            ->addComponent(new GridFieldAddNewInlineButton())
            ->addComponent(new GridFieldOrderableRows())
            ->addComponent(new GridFieldEditableColumns())
            ->addComponent(new GridFieldDeleteAction());

        $config->getComponentByType(GridFieldEditableColumns::class)->setDisplayFields(array(
            'FeatureID'  => function($record, $column, $grid) {
                $dropdown = new DropdownField($column, 'Feature', Feature::get()->map('ID', 'listTitle')->toArray());
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

        // quick add all from feature group
        $field = new DropdownField('QuickAddFeatureGroupID','Add all features from group',FeatureGroup::get()->map('ID','Title'));
        $field->setEmptyString('-');
        $field->setDescription('After save, all features of selected group will be added to the product');
        $fields->addFieldToTab('Root.Features',$field);
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
        $products = Controller::curr()->getRequest()->getSession()->get("ProductComparisons");

        if ($products) {
            $products = explode(",", $products);

            return in_array($this->owner->ID, $products);
        }

        return false;
    }

    public function addAllFeaturesFromGroup($groupid){
        if( $group = FeatureGroup::get()->byID($groupid) ){
            $features = $group->Features();
            foreach( $features as $feature ){
                // add empty ProductFeatureValue if not present
                if( !$this->owner->Features()->filter('FeatureID',$feature->ID)->first() ){
                    // does not exist yet, so add
                    $productFeatureValue = ProductFeatureValue::create();
                    $productFeatureValue->FeatureID = $feature->ID;
                    $productFeatureValue->ProductID = $this->owner->ID; // seems to be obsolete, item is linked via many_many
                    $productFeatureValue->write();
                    $this->owner->Features()->add($productFeatureValue);
                }
            }
        }
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        $this->owner->Features()->removeAll();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if( isset($_POST['QuickAddFeatureGroupID']) && $groupid=(int) $_POST['QuickAddFeatureGroupID'] ){
            $this->addAllFeaturesFromGroup($groupid);
        }
    }

}
