<?php namespace Dreamlands\Plate;

use Dreamlands\DContainer;
use League\Plates\Engine;

class DPlateEngine extends Engine
{
    /**
     * @var DContainer
     */
    private $container;

    public function __construct($directory = null, $fileExtension = 'php', DContainer $container)
    {
        parent::__construct($directory, $fileExtension);
        $this->container = $container;
    }

    public function make($name)
    {
        return $this->container->instantiate(DTemplate::class, [
            Engine::class => $this,
            'name' => $name,
        ]);
    }
}
