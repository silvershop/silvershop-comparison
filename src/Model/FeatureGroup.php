<?php

namespace SilverShop\Comparison\Model;

use LogicException;
use SilverShop\Comparison\GridField\GridFieldConfig_FeatureGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * A display grouping of features (e.g. "Dimensions", "Connectivity").
 *
 * A feature may belong to any number of groups. The order of a feature
 * within each group is stored on the join table (`Sort`), so the same feature
 * can be positioned independently in every group it appears in.
 *
 * @property string $Title
 * @method   ManyManyList<Feature> Features()
 */
class FeatureGroup extends DataObject
{
    private static array $db = [
        "Title" => "Varchar"
    ];

    private static array $many_many = [
        "Features" => Feature::class
    ];

    private static array $many_many_extraFields = [
        'Features' => [
            'Sort' => 'Int',
        ],
    ];

    // Duplicating a many_many relation links the same features (with their
    // sort order) to the new group rather than copying the features.
    private static array $cascade_duplicates = [
        'Features',
    ];

    private static string $singular_name = "Feature Group";

    private static string $plural_name = "Feature Groups";

    private static string $table_name = 'SilverShop_FeatureGroup';

    /**
     * Table and column names of the Features many_many join table.
     *
     * @return array{join: string, parentField: string, childField: string}
     */
    public static function featuresJoin(): array
    {
        $component = DataObject::getSchema()->manyManyComponent(self::class, 'Features');
        if (!$component) {
            throw new LogicException('FeatureGroup.Features many_many relation is not defined');
        }

        return [
            'join' => $component['join'],
            'parentField' => $component['parentField'],
            'childField' => $component['childField'],
        ];
    }

    /**
     * Features in this group, in their group order.
     *
     * @return ManyManyList<Feature>
     */
    public function SortedFeatures(): ManyManyList
    {
        $features = $this->Features();

        return $features->orderBy(sprintf(
            '"%s"."Sort" ASC, "%s"."Title" ASC',
            $features->getJoinTable(),
            DataObject::getSchema()->tableName(Feature::class)
        ));
    }

    public function getCMSFields(): FieldList
    {
        $fieldList = parent::getCMSFields();
        $fieldList->removeByName('Features');

        if ($this->exists()) {
            $gridField = GridField::create(
                'Features',
                'Features',
                $this->SortedFeatures(),
                GridFieldConfig_FeatureGroup::create()
            );
            $fieldList->addFieldToTab('Root.Main', $gridField);
        }

        return $fieldList;
    }

    protected function onBeforeDelete(): void
    {
        parent::onBeforeDelete();

        // Features may be shared with other groups, so only unlink them.
        $this->Features()->removeAll();
    }
}
