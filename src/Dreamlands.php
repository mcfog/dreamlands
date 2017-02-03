<?php namespace Dreamlands;

use Doctrine\DBAL\Logging\DebugStack;
use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Middleware\ErrorHandler;
use Lit\Bolt\BoltApp;
use Lit\Middlewares\FigCookiesMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Dreamlands
 * @package Dreamlands
 *
 * @property DContainer $container
 */
class Dreamlands extends BoltApp
{
    public function __construct(DContainer $container)
    {
        parent::__construct($container);
        $this
            ->prepend($this->container->produce(CurrentUserMiddleware::class))
            ->prepend($this->container->produce(FigCookiesMiddleware::class))
            ->append($container->produce(ErrorHandler::class));

        if (!$container->envIsProd()) {
            $this->prepend([$this, 'dumpQuery']);
        }
    }

    public function dumpQuery(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $next($request, $response);

        $queries = $this->container->produce(DebugStack::class)->queries;
        $this->container->logger->info(count($queries) . ' queries executed', $queries);

        return $response;
    }
}
