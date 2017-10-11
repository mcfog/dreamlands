<?php namespace Dreamlands\Middleware;

use Doctrine\DBAL\Logging\DebugStack;
use Lit\Air\Injection\SetterInjector;
use Lit\Core\AbstractMiddleware;
use Lit\Middlewares\Traits\MiddlewareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class DebugLoggerMiddleware extends AbstractMiddleware
{
    use MiddlewareTrait;
    const SETTER_INJECTOR = SetterInjector::class;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var DebugStack
     */
    protected $debugStack;

    /**
     *
     * @param LoggerInterface $logger
     */
    public function injectLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function injectDebugStack(DebugStack $debugStack)
    {
        $this->debugStack = $debugStack;
        return $this;
    }

    protected function main(): ResponseInterface
    {
        $request = $this->request;
        $this->logger->info('request', [
            'Method' => $request->getMethod(),
            'ProtocolVersion' => $request->getProtocolVersion(),
            'Uri' => $request->getUri(),
            'RequestTarget' => $request->getRequestTarget(),
            'Headers' => $request->getHeaders(),
            'Body' => $request->getParsedBody(),
        ]);
        $response = $this->next();
        $queries = $this->debugStack->queries;
        $this->logger->info(count($queries) . ' queries executed', $queries);

        return $response;
    }
}
