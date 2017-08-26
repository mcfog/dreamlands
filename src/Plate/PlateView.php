<?php namespace Dreamlands\Plate;

use Dreamlands\DView;
use Dreamlands\ViewModel\ViewModelFactory;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;

class PlateView extends DView
{
    private const JSDATA = '__jsdata__';

    /**
     * @var Engine
     */
    private $plate;
    /**
     * @var string
     */
    private $name;

    private $jsData = [];

    /**
     * @var ViewModelFactory
     */
    protected $wrapper;


    /**
     * PlateView constructor.
     * @param Engine $plate
     * @param string $name
     */
    public function __construct(Engine $plate, string $name, array $data = [])
    {
        parent::__construct($data);

        $this->plate = $plate;
        $this->name = $name;
    }

    public static function getInjectedProperties()
    {
        return parent::getInjectedProperties() + [
                'wrapper' => ViewModelFactory::class
            ];
    }

    /**
     * @param array|string $key
     * @param mixed $value
     * @return $this
     */
    public function setJsData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setJsData($k, $v);
            }
        } elseif (is_string($key)) {
            $this->jsData[$key] = $value;
        } else {
            throw new \InvalidArgumentException();
        }

        return $this;
    }

    /**
     * @param array $data
     * @return ResponseInterface
     */
    public function render(array $data = [])
    {
        $this->getEmptyBody()->write($this->plate->render($this->name, [
                self::JSDATA => $this->wrapper->convertToJson($this->jsData)
            ] + $data + $this->data));

        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

}
