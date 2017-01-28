<?php namespace Dreamlands\Plate;

use League\Plates\Engine;
use Lit\Core\Interfaces\IView;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;

class PlateView implements IView
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
    public function __construct(Engine $plate, string $name)
    {
        $this->plate = $plate;
        $this->name = $name;
    }

    public function render(array $data, ResponseInterface $resp)
    {
        return new HtmlResponse($this->plate->render($this->name, $data), $resp->getStatusCode(), $resp->getHeaders());
    }
}
