<?php

namespace SilverShop\Comparison\Tests\Unit\Model;

use SilverShop\Comparison\Model\Feature;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBVarchar;

class FeatureTest extends SapphireTest
{
    public function testGetValueFieldReturnsFieldForSupportedTypes(): void
    {
        $feature = Feature::create();
        $feature->ValueType = 'Boolean';
        $this->assertInstanceOf(CheckboxField::class, $feature->getValueField());

        $feature->ValueType = 'Number';
        $this->assertInstanceOf(NumericField::class, $feature->getValueField());

        $feature->ValueType = 'String';
        $this->assertInstanceOf(TextField::class, $feature->getValueField());
    }

    public function testGetValueFieldFallsBackForUnknownTypes(): void
    {
        $feature = Feature::create();
        $feature->ValueType = 'Unknown';

        $this->assertInstanceOf(LiteralField::class, $feature->getValueField());
    }

    public function testGetValueDbFieldBuildsTypedValueObject(): void
    {
        $feature = Feature::create();

        $feature->ValueType = 'Boolean';
        $boolean = $feature->getValueDBField('1');
        $this->assertInstanceOf(DBBoolean::class, $boolean);
        $this->assertTrue((bool) $boolean->getValue());

        $feature->ValueType = 'Number';
        $number = $feature->getValueDBField('3.14');
        $this->assertInstanceOf(DBFloat::class, $number);
        $this->assertSame(3.14, (float) $number->getValue());

        $feature->ValueType = 'String';
        $string = $feature->getValueDBField('abc');
        $this->assertInstanceOf(DBVarchar::class, $string);
        $this->assertSame('abc', $string->getValue());
    }
}
