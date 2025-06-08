<?php
namespace SilverShop\Comparison\GridField;

use SilverShop\Comparison\Model\Feature;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldFooter;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\TextField;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

/**
 * Allows editing of records contained within the GridField, instead of only allowing the ability to view records in
 * the GridField.
 */
class GridFieldConfig_FeatureGroup extends GridFieldConfig
{
    /**
     *
     * @param int  $itemsPerPage   - How many items per page should show up
     * @param bool $showPagination Whether the `Previous` and `Next` buttons should display or not, leave as null to use default
     * @param bool $showAdd        Whether the `Add` button should display or not, leave as null to use default
     */
    public function __construct($itemsPerPage = null, $showPagination = null, $showAdd = null)
    {
        parent::__construct();

        $displayFields = [
            'Title' => [
                'title' => 'Title',
                'field' => TextField::class,
            ],
            'Unit' => [
                'title' => 'Unit',
                'field' => TextField::class,
            ],
            'ValueType' => function ($record, $column, $grid) {
                return DropdownField::create($column, "Value Type", singleton(Feature::class)->dbObject('ValueType')->enumValues());
            }
        ];

        $this->addComponent($editableColumns = GridFieldEditableColumns::create());
        $editableColumns->setDisplayFields($displayFields);
        $sortByGroup = Config::inst()->get(Feature::class, 'sort_features_by_group');
        if ($sortByGroup) {
            $this->addComponent(GridFieldOrderableRows::create());
        }
        $this->addComponent(GridFieldButtonRow::create('before'));
        $this->addComponent(GridFieldAddNewInlineButton::create('buttons-before-left'));
        $this->addComponent(GridFieldToolbarHeader::create());
        $this->addComponent(GridFieldTitleHeader::create());
        $this->addComponent(GridFieldFooter::create());
        $this->addComponent(GridFieldDeleteAction::create());
        $this->extend('updateConfig');
    }
}
