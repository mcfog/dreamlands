<?php namespace Dreamlands\Middleware;

use Dreamlands\Action\Etc\UnicornAction;
use Dreamlands\DAction;
use Dreamlands\DContainer;
use Nimo\IErrorMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorHandler implements IErrorMiddleware
{
    /**
     * @var DContainer
     */
    private $container;

    public function __construct(DContainer $container)
    {
        $this->container = $container;
    }

    public function __invoke(
        $error,
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        /**
         * @var DAction $action
         */
        $action = $this->container->instantiate(UnicornAction::class, [
            'error' => $error
        ]);

        return $action($request, $response, $next);
    }
}
