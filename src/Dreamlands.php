<?php namespace Dreamlands;

use Doctrine\DBAL\Logging\DebugStack;
use Dreamlands\Action\Etc\UnicornAction;
use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Middleware\ErrorHandler;
use Dreamlands\Utility\DContainerAwareTrait;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
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
    public function __construct(DContainer $container, ResponseInterface $responsePrototype = null)
    {
        parent::__construct($container, $this->responsePrototype);
        /** @noinspection PhpParamsInspection */
        $this
            ->prepend($this->container->produce(CurrentUserMiddleware::class))
            ->prepend($this->container->produce(FigCookiesMiddleware::class))
            ->prepend(new class($container) implements MiddlewareInterface
            {
                use DContainerAwareTrait;

                public function process(ServerRequestInterface $request, DelegateInterface $delegate)
                {
                    try {
                        return $delegate->process($request);
                    } catch (\Exception $e) {
                        /**
                         * @var UnicornAction $unicornAction
                         */
                        $unicornAction = $this->container->instantiate(UnicornAction::class, [
                            'error' => $e
                        ]);
                        return $unicornAction->process($request, $delegate);
                    }
                }
            });

        if (!$container->envIsProd()) {
            $this->prepend(new class($container) implements MiddlewareInterface
            {
                use DContainerAwareTrait;

                public function process(ServerRequestInterface $request, DelegateInterface $delegate)
                {
                    $response = $delegate->process($request);
                    $queries = $this->container->produce(DebugStack::class)->queries;
                    $this->container->logger->info(count($queries) . ' queries executed', $queries);

                    return $response;
                }

            });
        }
    }

}
