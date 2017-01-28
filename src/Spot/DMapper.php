<?php namespace Dreamlands\Spot;

use Spot\Mapper;

class DMapper extends Mapper
{
    public function resolver()
    {
        return new DResolver($this);
    }
}
