<?php

namespace App\Core\Ui;

use App\Core\Schema;
use App\Core\SchemaLayout\ArraySchema;
use App\Core\SchemaLayout\HtmlSchema;
use App\Core\SchemaLayout\NumberSchema;
use App\Core\SchemaLayout\ObjectSchema;
use App\Core\SchemaLayout\RangeSchema;
use App\Core\SchemaLayout\StringSchema;
use App\Core\Ui\Attribute\Continues;
use App\Core\Ui\Attribute\Form;
use ReflectionClass;

final class TransformForm
{

    private ReflectionClass $className;
    public function __construct(string $className, private ?Schema $schema = null)
    {
        $this->className = new ReflectionClass($className);
        if ($schema === null) {
            $schema = new Schema();
        }
    }

    public function export()
    {
        $baseObject = new ObjectSchema();

        foreach ($this->className->getProperties() as $property) {
            if (count($property->getAttributes(Form::class)) > 0) {
                if (count($property->getAttributes(Continues::class)) > 0) {
                    continue;
                }
                /**
                 * @var Form
                 */
                $form = ($property->getAttributes(Form::class)[0])->newInstance();

                /**
                 * @var StringSchema|NumberSchema|ObjectSchema|ArraySchema|HtmlSchema|RangeSchema
                 */
                $baseTypeObject = match ($form->getType()) {
                    'string' => new StringSchema(),
                    'number' => new NumberSchema(),
                    'object' => new ObjectSchema(),
                    'array' => new ArraySchema(),
                    'html' => new HtmlSchema(),
                    'range' => new RangeSchema(),
                    default => new StringSchema(),
                };

                $baseTypeObject->setTitle($form->getTitle())
                    ->setDescription($form->getDescription())
                    ->setPlaceholder($form->getPlaceholder())
                    ->setProps($form->getProps())
                    ->setFormate($form->getFormat())
                    ->setRules($form->getRules())
                    ->setMinLength($form->getMin())
                    ->setMaxLength($form->getMax())
                    ->setEnum($form->getEnum())
                    ->setEnumNames($form->getEnumNames());
                
                $options = $form->getOptions();

                foreach($options as $name => $option) {
                    $baseTypeObject->setUiSchema($name, $option);
                }

                if ($form->getReadonly()) {
                    $baseTypeObject->isReadonly();
                }

                if ($form->getHidden()) {
                    $baseTypeObject->isHidden();
                }

                if ($form->getRequired()) {
                    $baseTypeObject->isRequired();
                }

                if ($form->getDisabled()) {
                    $baseTypeObject->isDisable();
                }

                $baseObject->setProperties($property->getName(), $baseTypeObject);
            }
        }

        $this->schema->setSchema($baseObject);
    }
}
