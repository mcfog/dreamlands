<?php namespace Dreamlands\Spot;

use Spot\Mapper;

/**
 * Class DMapper
 * @package Dreamlands\Spot
 */
class DMapper extends Mapper
{
    public function resolver()
    {
        return new DResolver($this);
    }
}
