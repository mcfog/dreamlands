<?php namespace Dreamlands\ViewModel;

trait ExposedByViewModelTrait
{
    public function expose()
    {
        return call_user_func([static::VIEW_MODEL_CLASS, 'stub'], [$this]);
    }
}
