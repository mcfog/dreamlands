<?php namespace Dreamlands;

use Doctrine\DBAL\Logging\DebugStack;
use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Middleware\ErrorHandler;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Lit\Bolt\BoltApp;
use Lit\Middlewares\FigCookiesMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Middleware\CallableInteropMiddlewareWrapper;

/**
 * Class Dreamlands
 * @package Dreamlands
 *
 * @property DContainer $container
 */
class Dreamlands extends BoltApp
{
    public function __construct(DContainer $container, ResponseInterface $responsePrototype = null)
    {
        parent::__construct($container, $this->responsePrototype);
        /** @noinspection PhpParamsInspection */
        $this
            ->prepend($this->container->produce(CurrentUserMiddleware::class))
            ->prepend($this->container->produce(FigCookiesMiddleware::class))
            ->pipe($container->produce(ErrorHandler::class));

        if (!$container->envIsProd()) {
            $this->prepend(new CallableInteropMiddlewareWrapper([$this, 'dumpQuery']));
        }
    }

    public function dumpQuery(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);

        $queries = $this->container->produce(DebugStack::class)->queries;
        $this->container->logger->info(count($queries) . ' queries executed', $queries);

        return $response;
    }
}
