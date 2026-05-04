<?php

namespace App\Core\Ui;

use App\Core\TableLayout;
use App\Core\Ui\Attribute\Continues;
use App\Core\Ui\Attribute\TableCol;
use ReflectionClass;
use ReflectionProperty;

final class TransformTable {

    private ReflectionClass $className;
    public function __construct(string $className, private ?TableLayout $table = null) {
        $this->className = new ReflectionClass($className);
        if (is_null($table)) {
            $table = new TableLayout();
        }
    }

    public function export() {
        foreach ($this->className->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            if (count($property->getAttributes(TableCol::class)) > 0) {
                if (count($property->getAttributes(Continues::class)) > 0) {
                    continue;
                }
                $col = $property->getAttributes(TableCol::class)[0];
                /**
                 * @var TableCol
                 */
                $col = $col->newInstance();
                
                $this->table->addCol($property->getName(), $col->getTitle(), $col->getRender(), $col->getOption());
            }
        }
    }
}