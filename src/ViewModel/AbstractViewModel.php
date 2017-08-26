<?php namespace Dreamlands\ViewModel;

use Lit\Bolt\BoltContainerStub;

abstract class AbstractViewModel implements IExposed
{
    public function toJson($option = 0)
    {
        return json_encode($this->toDataObject(), $option);
    }

    abstract public function toDataObject();

    public function expose()
    {
        return $this->toDataObject();
    }
}
