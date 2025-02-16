<?php

namespace Illuminate\Tests\Validation;

use Illuminate\Container\Container;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidationDimensionsRuleTest extends TestCase
{
    public function testWidth()
    {
        $rule = Dimensions::defaults()->width(100);

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 99,
            height: 100,
            message: 'validation.width'
        );
    }

//    public function testMinWidth()
//    {
//        $rule = Dimensions::defaults()->minWidth(100);
//
//        $this->passes(
//            $rule,
//            width: 100,
//            height: 100,
//        );
//
//        $this->fails(
//            $rule,
//            width: 99,
//            height: 100,
//            message: 'validation.min_width'
//        );
//    }

    public function testMaxWidth()
    {
        $rule = Dimensions::defaults()->maxWidth(100);

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 101,
            height: 100,
            message: 'validation.max_width'
        );
    }

    public function testWidthBetween()
    {
        $rule = Dimensions::defaults()->widthBetween(100, 200);

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 99,
            height: 100,
            message: 'validation.width_between'
        );
    }

    public function testHeight()
    {
        $rule = Dimensions::defaults()->height(100);

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 100,
            height: 99,
            message: 'validation.height'
        );
    }

    public function testMinHeight()
    {
        $rule = Dimensions::defaults()->minHeight(100);

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 100,
            height: 99,
            message: 'validation.min_height'
        );
    }

    public function testMaxHeight()
    {
        $rule = Dimensions::defaults()->maxHeight(100);

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 100,
            height: 101,
            message: 'validation.max_height'
        );
    }

    public function testHeightBetween()
    {
        $rule = Dimensions::defaults()->heightBetween(100, 200);

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 100,
            height: 99,
            message: 'validation.height_between'
        );
    }

    public function testRatio()
    {
        $rule = Dimensions::defaults()->ratio(1 / 2);

        $this->passes(
            $rule,
            width: 100,
            height: 200,
        );

        $this->fails(
            $rule,
            width: 100,
            height: 100,
            message: 'validation.ratio'
        );
    }

    public function testMinRatio()
    {
        $rule = Dimensions::defaults()->minRatio(1 / 2);

        $this->passes(
            $rule,
            width: 100,
            height: 200
        );

        $this->fails($rule,
            width: 100,
            height: 100,
            message: 'validation.min_ratio'
        );
    }

    public function testMaxRatio()
    {
        $rule = Dimensions::defaults()->maxRatio(1 / 1);

        $this->passes(
            $rule,
            width: 100,
            height: 100
        );

        $this->fails(
            $rule,
            width: 100,
            height: 200,
            message: 'validation.max_ratio'
        );
    }

    public function testRatioBetween()
    {
        $rule = Dimensions::defaults()->ratioBetween(1 / 2, 2 / 5);

        $this->passes(
            $rule,
            width: 100,
            height: 200
        );

        $this->fails(
            $rule,
            width: 100,
            height: 100,
            message: 'validation.ratio_between'
        );
    }

    public function testLegacyStringFormatIsSupported()
    {
        $rule = 'dimensions:min_width=100,max_width=200,min_height=100,max_height=200,ratio=1/1,min_ratio=1/1,max_ratio=2/5';

        $this->passes(
            $rule,
            width: 150,
            height: 150
        );

        $this->fails(
            $rule,
            width: 190,
            height: 210,
            message: 'validation.dimensions'
        );
    }

    public function testLegacyConstraintsPassedIntoConstructorViaRuleSupported()
    {
        $rule = Rule::dimensions([
            'min_width' => 100,
            'max_width' => 200,
            'min_height' => 100,
            'max_height' => 200,
            'ratio' => 1 / 1,
        ]);

        $this->passes(
            $rule,
            width: 150,
            height: 150
        );

        $this->fails(
            $rule,
            width: 190,
            height: 200,
            message: 'validation.ratio'
        );
    }

    public function testCustomRulesAdded()
    {
        $this->passes(
            Dimensions::defaults()
                ->width(100)->height(100)
                ->rules(['mimes:jpg']),
            width: 100,
            height: 100
        );

        $this->fails(
            Dimensions::defaults()
                ->width(100)->height(100)
                ->rules(['mimes:png']),
            width: 100,
            height: 100,
            message: 'validation.mimes'
        );
    }

    public function testMacroable()
    {
        Dimensions::macro('thumbnail', function () {
            return $this->width(100)->height(100);
        });

        $rule = Dimensions::defaults()->thumbnail();

        $this->passes(
            $rule,
            width: 100,
            height: 100,
        );

        $this->fails(
            $rule,
            width: 99,
            height: 100,
            message: 'validation.width'
        );
    }

    public function fails($rule, $width, $height, $message)
    {
        $this->assertValidationRules(
            $rule,
            UploadedFile::fake()->image('image.jpg', $width, $height),
            false,
            [$message]
        );
    }

    public function passes($rule, $width, $height)
    {
        $this->assertValidationRules(
            $rule,
            UploadedFile::fake()->image('image.jpg', $width, $height),
            true,
            []
        );
    }

    protected function assertValidationRules($rule, $values, $result, $messages)
    {
        $values = Arr::wrap($values);

        // assert that the translator is still resolvable
        $this->assertInstanceOf(
            Translator::class,
            Container::getInstance()->make('translator'),
            'The translator should be resolvable from the container.'
        );

        $translatorIsBound = Container::getInstance()->bound('translator');
        $translatorIsResolved = Container::getInstance()->resolved('translator');
        echo "Translator is bound: $translatorIsBound\n";
        echo "Translator is resolved: $translatorIsResolved\n";

        foreach ($values as $value) {
            $v = new Validator(
                Container::getInstance()->make('translator'),
                //resolve('translator'),
                ['my_file' => $value],
                ['my_file' => is_object($rule) ? clone $rule : $rule]
            );

            $this->assertSame($result, $v->passes());

            $this->assertSame(
                $result ? [] : ['my_file' => $messages],
                $v->messages()->toArray()
            );
        }
    }

    protected function setUp(): void
    {
        $originalContainer = Container::getInstance();
        echo "Original container: ".get_class($originalContainer)."\n";

        $container = new Container;
        Container::setInstance($container);

        echo "Setting translator in ".__METHOD__."\n";

        $container->bind('translator', function () {
            echo "Translator bound\n";
            return new Translator(new ArrayLoader, 'en');
        });

        // add a debug echo to ensure that the translator is in the container now.
        $container->resolving('translator', function ($translator) {
            echo "Translator resolved\n";
            echo "Translator: ".get_class($translator)."\n";
        });

        Facade::setFacadeApplication($container);

        (new ValidationServiceProvider($container))->register();
    }


    protected function tearDown(): void
    {
        Container::setInstance(null);

        Facade::clearResolvedInstances();

        Facade::setFacadeApplication(null);
    }
}
