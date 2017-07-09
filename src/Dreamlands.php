<?php namespace Dreamlands;

use Doctrine\DBAL\Logging\DebugStack;
use Dreamlands\Action\Etc\UnicornAction;
use Dreamlands\Middleware\CurrentUserMiddleware;
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
            $this->pipe($this->sqlLogger());
        }

        /** @noinspection PhpParamsInspection */
        $this
            ->pipe($this->errorHandler())
            ->pipe($this->container->produce(IpAddress::class))
            ->pipe($this->container->produce(FigCookiesMiddleware::class))
            ->pipe($this->container->produce(CurrentUserMiddleware::class));

        parent::pipeMiddlewares();
    }

    /**
     * @return MiddlewareInterface
     */
    protected function sqlLogger()
    {
        return new class($this->container) implements MiddlewareInterface
        {
            use DContainerAwareTrait;

            public function process(ServerRequestInterface $request, DelegateInterface $delegate)
            {
                /**
                 * @var DContainer $this ->container
                 */
                $response = $delegate->process($request);
                $queries = $this->container->produce(DebugStack::class)->queries;
                $this->container->logger->info(count($queries) . ' queries executed', $queries);

                return $response;
            }

        };
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
