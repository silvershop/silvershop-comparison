<?php
namespace SilverShop\Comparison\GridField;

use SilverShop\Comparison\Model\Feature;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
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
    public function __construct()
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
            'ValueType' => fn($record, $column, $grid) => DropdownField::create($column, "Value Type", singleton(Feature::class)->dbObject('ValueType')->enumValues())
        ];

        $this->addComponent($editableColumns = GridFieldEditableColumns::create());
        $editableColumns->setDisplayFields($displayFields);
        // Order within the group is held on the many_many join table
        $this->addComponent(GridFieldOrderableRows::create('Sort'));

        $this->addComponent(GridFieldButtonRow::create('before'));
        $this->addComponent(GridFieldAddNewInlineButton::create('buttons-before-left'));
        // Features can belong to several groups, so allow linking existing ones
        $this->addComponent(GridFieldAddExistingAutocompleter::create('buttons-before-right', ['Title']));
        $this->addComponent(GridFieldToolbarHeader::create());
        $this->addComponent(GridFieldTitleHeader::create());
        $this->addComponent(GridFieldFooter::create());
        // Unlink from this group only; the feature may be used by other groups and products
        $this->addComponent(GridFieldDeleteAction::create(true));
        $this->extend('updateConfig');
    }
}
