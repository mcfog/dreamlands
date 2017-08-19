<?php namespace Dreamlands\ViewModel;

use Dreamlands\DContainer;
use Lit\Bolt\BoltContainerStub;

class ViewModel extends AbstractViewModel
{
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var DContainer
     */
    private $container;

    /**
     * ViewModel constructor.
     */
    public function __construct($data, DContainer $container)
    {
        $this->data = $data;
        $this->container = $container;
    }

    public function toArray()
    {
        return self::mapToArray($this->data, $this->container);
    }

    protected static function mapToArray($data, DContainer $container)
    {
        if (is_scalar($data)) {
            return $data;
        }
        if (is_null($data)) {
            return null;
        }
        if (is_resource($data)) {
            throw new \InvalidArgumentException();
        }

        if ($data instanceof IExposed) {
            return self::mapToArray($data->expose(), $container);
        }
        if ($data instanceof BoltContainerStub) {
            return self::mapToArray($data->instantiateFrom($container), $container);
        }

        if ($data instanceof \stdClass) {
            $data = (array)$data;
        }
        if (is_array($data) || $data instanceof \Traversable) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[$key] = self::mapToArray($value, $container);
            }
            return $result;
        }

        if ($data instanceof \JsonSerializable) {
            return json_decode(json_encode($data), true);
        }

        throw new \Exception('cannot convert this object');
    }
}
