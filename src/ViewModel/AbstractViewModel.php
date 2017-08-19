<?php namespace Dreamlands\ViewModel;

use Lit\Bolt\BoltContainerStub;

abstract class AbstractViewModel implements IExposed
{
    public static function stub(array $extraParameters = [])
    {
        return BoltContainerStub::of(static::class, $extraParameters);
    }

    public function toJson($option = 0)
    {
        return json_encode($this->toArray(), $option);
    }

    abstract public function toArray();

    public function expose()
    {
        return $this->toArray();
    }
}
