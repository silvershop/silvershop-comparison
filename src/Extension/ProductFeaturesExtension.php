<?php

namespace SilverShop\Comparison\Extension;

use SilverShop\Comparison\GridField\GridFieldConfig_ProductFeatures;
use SilverShop\Comparison\Model\Feature;
use SilverShop\Comparison\Model\FeatureGroup;
use SilverShop\Comparison\Model\ProductFeatureValue;
use SilverShop\Comparison\Pagetypes\ProductComparisonPage;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Model\ArrayData;

/**
 * Class ProductFeaturesExtension
 *
 * @package SilverShop\Comparison\Extension
 *
 * @method  HasManyList<ProductFeatureValue> Features()
 * @extends Extension<(Product & static)>
 */
class ProductFeaturesExtension extends Extension
{
    private static array $has_many = [
        'Features' => ProductFeatureValue::class
    ];

    private static array $cascade_deletes = [
        'Features',
    ];

    private static array $cascade_duplicates = [
        'Features',
    ];

    public function updateCMSFields(FieldList $fieldList): void
    {
        $gridFieldConfigProductFeatures = GridFieldConfig_ProductFeatures::create();

        if ($this->owner->exists()) {
            $sortByGroup = Config::inst()->get(Feature::class, 'sort_features_by_group');
            if ($sortByGroup) {
                // Features can sit in several groups so there is no single group
                // order to follow here; list them alphabetically instead.
                $features = $this->owner->Features()->sort('Feature.Title', 'ASC');
            } else {
                $features = $this->owner->Features();
            }

            $grid = GridField::create("Features", "Features", $features, $gridFieldConfigProductFeatures);
            $fieldList->addFieldToTab("Root.Features", $grid);
        }

        // quick add all from feature group
        $dropdownField = DropdownField::create('QuickAddFeatureGroupID', 'Add all features from group', FeatureGroup::get()->map('ID', 'Title'));
        $dropdownField->setEmptyString('-');
        $dropdownField->setDescription('After save, all features of selected group will be added to the product');

        $fieldList->addFieldToTab('Root.Features', $dropdownField);

        // Explanatory notice as the last item on the tab.
        $fieldList->addFieldToTab(
            'Root.Features',
            \SilverStripe\Forms\LiteralField::create(
                'featuresinfo',
                '<p class="message notice" style="display:flex;align-items:flex-start;gap:.5em">'
                . '<span class="font-icon-info-circled" aria-hidden="true"></span><span>' . _t(
                    self::class . '.FeaturesInfo',
                    'Features are descriptive specifications (e.g. Material, Warranty) shown on the product and '
                    . 'used for product comparison. They do not affect price, stock, or the options a customer '
                    . 'selects — those are set on the "Variations" tab.'
                ) . '</span></p>'
            )
        );
    }

    public function CompareLink(): ?string
    {
        if ($this->isCompared()) {
            return $this->CompareRemoveLink();
        }

        return $this->CompareAddLink();
    }

    public function CompareAddLink(): ?string
    {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("add/" . $this->owner->ID);
        }

        return null;
    }

    public function CompareRemoveLink(): ?string
    {
        if ($page = ProductComparisonPage::get()->first()) {
            return $page->Link("remove/" . $this->owner->ID);
        }

        return null;
    }

    public function isCompared(): bool
    {
        $request = $this->getCurrentRequest();
        $products = $request ? $request->getSession()->get('ProductComparisons') : null;

        if ($products) {
            $products = explode(",", (string) $products);

            return in_array((string) $this->owner->ID, $products, true);
        }

        return false;
    }

    public function addAllFeaturesFromGroup(int $groupId): void
    {
        if ($group = FeatureGroup::get()->byID($groupId)) {
            $features = $group->SortedFeatures();
            $sort = $this->owner->Features()->max('Sort') + 1;

            foreach ($features as $feature) {
                // add empty ProductFeatureValue if not present
                if (!$this->owner->Features()->filter('FeatureID', $feature->ID)->first()) {
                    // does not exist yet, so add
                    $productFeatureValue = ProductFeatureValue::create();
                    $productFeatureValue->FeatureID = $feature->ID;
                    $productFeatureValue->ProductID = $this->owner->ID; // seems to be obsolete, item is linked via many_many
                    $productFeatureValue->Sort = $sort;
                    $productFeatureValue->write();
                    $this->owner->Features()->add($productFeatureValue);
                    $sort++;
                }
            }
        }
    }

    /**
     * Product feature values bucketed by feature group. A feature that belongs
     * to several groups is listed under each of them.
     *
     * Features within a group follow that group's sort order. Groups appear
     * in the order their features first appear on the product, or
     * alphabetically when `Feature.sort_features_by_group` is enabled.
     *
     * @return ArrayList<ArrayData>
     */
    public function GroupedFeatures(bool $showungrouped = false): ArrayList
    {
        $values = $this->owner->Features()->filter('Feature.ID:GreaterThan', 0)->toArray();
        $arrayList = ArrayList::create();

        if (empty($values)) {
            return $arrayList;
        }

        $sortByGroup = (bool) Config::inst()->get(Feature::class, 'sort_features_by_group');
        $featureIDs = array_unique(array_map(fn (ProductFeatureValue $value) => (int) $value->FeatureID, $values));

        // Fetch every group membership for these features in one query.
        $relation = FeatureGroup::featuresJoin();
        $memberships = SQLSelect::create(
            [
                'GroupID' => sprintf('"%s"', $relation['parentField']),
                'FeatureID' => sprintf('"%s"', $relation['childField']),
                'Sort' => '"Sort"',
            ],
            sprintf('"%s"', $relation['join'])
        )
            ->addWhere([sprintf('"%s" IN (%s)', $relation['childField'], DB::placeholders($featureIDs)) => $featureIDs])
            ->execute();

        /** @var array<int, array<int, int>> $groupSortByFeature FeatureID => [GroupID => Sort] */
        $groupSortByFeature = [];
        foreach ($memberships as $row) {
            $groupSortByFeature[(int) $row['FeatureID']][(int) $row['GroupID']] = (int) $row['Sort'];
        }

        // Feature titles break ties in the group sort order
        $featureTitles = Feature::get()->byIDs($featureIDs)->map('ID', 'Title')->toArray();

        /** @var array<int, ProductFeatureValue[]> $byGroup */
        $byGroup = [];
        $ungrouped = [];
        foreach ($values as $value) {
            $groupSorts = $groupSortByFeature[(int) $value->FeatureID] ?? [];
            if (empty($groupSorts)) {
                $ungrouped[] = $value;
                continue;
            }
            foreach (array_keys($groupSorts) as $groupID) {
                $byGroup[$groupID][] = $value;
            }
        }

        if (!empty($byGroup)) {
            /** @var array<int, FeatureGroup> $groups */
            $groups = [];
            foreach (FeatureGroup::get()->byIDs(array_keys($byGroup)) as $group) {
                $groups[$group->ID] = $group;
            }

            $groupIDs = array_keys($byGroup);
            if ($sortByGroup) {
                usort($groupIDs, fn (int $a, int $b) => strcasecmp(
                    (string) ($groups[$a]->Title ?? ''),
                    (string) ($groups[$b]->Title ?? '')
                ));
            }

            foreach ($groupIDs as $groupID) {
                if (!isset($groups[$groupID])) {
                    continue;
                }

                $children = $byGroup[$groupID];
                $sortKey = fn (ProductFeatureValue $value) => [
                    $groupSortByFeature[(int) $value->FeatureID][$groupID],
                    (string) ($featureTitles[$value->FeatureID] ?? ''),
                ];
                usort($children, fn (ProductFeatureValue $a, ProductFeatureValue $b) => $sortKey($a) <=> $sortKey($b));

                $arrayList->push(ArrayData::create([
                    'Group' => $groups[$groupID],
                    'Children' => ArrayList::create($children),
                ]));
            }
        }

        if ($showungrouped && !empty($ungrouped)) {
            $arrayList->push(ArrayData::create([
                'Children' => ArrayList::create($ungrouped),
            ]));
        }

        return $arrayList;
    }

    public function onAfterWrite(): void
    {
        $request = $this->getCurrentRequest();
        $groupid = $request ? (int) $request->postVar('QuickAddFeatureGroupID') : 0;

        if ($groupid > 0) {
            $this->addAllFeaturesFromGroup($groupid);
        }
    }

    protected function getCurrentRequest(): ?HTTPRequest
    {
        $controller = Controller::curr();
        if (!$controller) {
            return null;
        }

        return $controller->getRequest();
    }
}
