<?php

use Nette\PhpGenerator\ClassType;

require_once './vendor/autoload.php';

$class = new ClassType('Demo');

$class->setFinal()
    ->setExtends(EasySwoole\Http\AbstractInterface\Controller::class)
    ->addImplement(Countable::class)
    ->addComment("Description of class.\nSecond line\n")
    ->addComment('@property-read Nette\Forms\Form $form');

$class->addConstant('ID', 123)
    ->setProtected()
    ->setFinal();

$class->addProperty('items', [1,2,3])
    ->setPrivate()
    ->setStatic()
    ->addComment('@var int[]');

$class->addProperty('list')
    ->setType('array')
    ->setNullable()
    ->setInitialized();

$method = $class->addMethod('count')
    ->addComment('Count it.')
    ->addComment('@return int')
    ->setFinal()
    ->setProtected()
    ->setReturnType('int')
    ->setReturnNullable()
    ->setBody('return count($items ?: $this->items);');

$method->addParameter('items', [])
    ->setReference()
    ->setType('array');

$method = $class->addMethod('__construct');
$method->addPromotedParameter('name');
$method->addPromotedParameter('args', [])->setPrivate();

$printer = new \Nette\PhpGenerator\Printer();
echo $printer->printClass($class);