<?php namespace Dreamlands;

use Dreamlands\Action\Etc\UnicornAction;
use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Middleware\DebugLoggerMiddleware;
use Dreamlands\Middleware\SessionMiddelware;
use Dreamlands\Utility\DContainerAwareTrait;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Lit\Bolt\BoltApp;
use Lit\Middlewares\FigCookiesMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use RKA\Middleware\IpAddress;

/**
 * Class Dreamlands
 * @package Dreamlands
 *
 * @property DContainer $container
 */
class Dreamlands extends BoltApp
{
    protected function pipeMiddlewares()
    {
        if (!$this->container->envIsProd()) {
            $this->pipe($this->container->getOrProduce(DebugLoggerMiddleware::class));
        }

        /** @noinspection PhpParamsInspection */
        $this
            ->pipe($this->errorHandler())
            ->pipe($this->container->getOrProduce(IpAddress::class))
            ->pipe($this->container->getOrProduce(FigCookiesMiddleware::class))
            ->pipe($this->container->getOrProduce(SessionMiddelware::class))
            ->pipe($this->container->getOrProduce(CurrentUserMiddleware::class));

        parent::pipeMiddlewares();
    }

    /**
     * @return MiddlewareInterface
     */
    protected function errorHandler()
    {
        return new class($this->container) implements MiddlewareInterface
        {
            use DContainerAwareTrait;

            public function process(ServerRequestInterface $request, DelegateInterface $delegate)
            {
                /**
                 * @var DContainer $this ->container
                 */
                try {
                    return $delegate->process($request);
                } catch (\Throwable $e) {
                    /**
                     * @var UnicornAction $unicornAction
                     */
                    $unicornAction = $this->container->instantiate(UnicornAction::class, [
                        'error' => $e
                    ]);
                    return $unicornAction->process($request, $delegate);
                }
            }
        };
    }
}
