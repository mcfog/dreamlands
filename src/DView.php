<?php namespace Dreamlands;

use Dreamlands\Utility\DViewTrait;
use Lit\Air\Injection\SetterInjector;
use Lit\Core\Interfaces\IView;
use Lit\Core\Traits\ViewTrait;
use Zend\Diactoros\Response\InjectContentTypeTrait;

abstract class DView implements IView
{
    use ViewTrait;
    use InjectContentTypeTrait;
    use DViewTrait;

    const SETTER_INJECTOR = SetterInjector::class;
}
