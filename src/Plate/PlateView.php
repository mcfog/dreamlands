<?php namespace Dreamlands\Plate;

use Dreamlands\DView;
use League\Plates\Engine;

class PlateView extends DView
{
    /**
     * @var Engine
     */
    private $plate;
    /**
     * @var string
     */
    private $name;


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

    public function render(array $data = [])
    {
        $this->getEmptyBody()->write($this->plate->render($this->name, $data + $this->data));

        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

}
