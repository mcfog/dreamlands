<?php namespace Dreamlands\Exceptions;


use Psr\Http\Message\ResponseInterface;

class ThrowableResult extends \Exception
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        parent::__construct('throwable result', 0, null);
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
