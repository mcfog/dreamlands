<?php namespace Dreamlands;

use Dreamlands\Utility\DViewTrait;
use Lit\Core\Interfaces\IView;
use Lit\Core\Traits\ViewTrait;
use Zend\Diactoros\Response\InjectContentTypeTrait;

abstract class DView implements IView
{
    use ViewTrait;
    use InjectContentTypeTrait;
    use DViewTrait;
}
