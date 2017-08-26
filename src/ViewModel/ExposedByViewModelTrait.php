<?php namespace Dreamlands\ViewModel;

use Lit\Bolt\BoltContainerStub;

trait ExposedByViewModelTrait
{
    public function expose()
    {
        return BoltContainerStub::of(static::VIEW_MODEL_CLASS, [$this]);
    }
}
