<?php namespace Dreamlands\Plate;

use Identicon\Identicon;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class IdenticonExt implements ExtensionInterface
{
    /**
     * @var Identicon
     */
    private $identicon;

    public function __construct(Identicon $identicon)
    {
        $this->identicon = $identicon;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('identicon', [$this, 'identicon']);
    }

    public function identicon($content)
    {
        return $this->identicon->getImageDataUri($content);
    }
}
