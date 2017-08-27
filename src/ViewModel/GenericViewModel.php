<?php namespace Dreamlands\ViewModel;

use Dreamlands\DContainer;
use Lit\Bolt\BoltContainerStub;

class GenericViewModel extends AbstractViewModel
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

    public function toDataObject()
    {
        return self::convert($this->data, $this->container);
    }

    protected static function convert($data, DContainer $container)
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
            return self::convert($data->expose(), $container);
        }
        if ($data instanceof BoltContainerStub) {
            return self::convert($data->instantiateFrom($container), $container);
        }

        if ($data instanceof \stdClass) {
            return $data;
        }
        if (is_array($data) && (empty($data) || array_keys($data) === range(0, count($data) - 1))) {
            return $data;
        }
        if (is_array($data) || $data instanceof \Traversable) {
            $result = new \stdClass();
            foreach ($data as $key => $value) {
                $result->{$key} = self::convert($value, $container);
            }
            return $result;
        }

        if ($data instanceof \JsonSerializable) {
            return json_decode(json_encode($data));
        }

        throw new \Exception('cannot convert this object');
    }
}
