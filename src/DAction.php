<?php namespace Dreamlands;

use Doctrine\Common\Inflector\Inflector;
use Dreamlands\Action\Etc\AkarinAction;
use Dreamlands\Exceptions\ThrowableResult;
use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Plate\MessageView;
use Dreamlands\Plate\PlateView;
use Dreamlands\Repository\Repository;
use Lit\Bolt\BoltAction;
use Lit\Bolt\BoltContainer;
use Lit\Middlewares\FigCookiesMiddleware;
use Lit\Middlewares\Traits\MiddlewareTrait;
use Nimo\IMiddleware;
use Nimo\MiddlewareStack;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DAction
 * @package Dreamlands
 *
 * @property DContainer $container
 */
abstract class DAction extends BoltAction
{
    use MiddlewareTrait;

    const METHOD = 'GET';
    const PATH = '/';
    const ATTR_KEY = self::class;

    protected static $interceptors = [];
    /**
     * @var FigCookiesMiddleware
     */
    protected $cookie;

    /**
     * @var CurrentUserMiddleware
     */
    protected $currentUser;
    /**
     * @var Repository
     */
    protected $repo;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(BoltContainer $container, Repository $repo, LoggerInterface $logger)
    {
        parent::__construct($container);
        $this->repo = $repo;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    protected static function getInterceptors()
    {
        return self::$interceptors;
    }


    /**
     * @param string $name
     */
    protected function plate(string $name)
    {
        /** @noinspection PhpParamsInspection */
        return $this->attachView($this->container->instantiate(PlateView::class, [
            'name' => $name,
            'data' => $this->getGlobalViewData(),
        ]));
    }

    /**
     * @return array
     */
    protected function getGlobalViewData()
    {
        $action = substr(get_class($this), strlen('Dreamlands\\Action\\'), -strlen('Action'));
        $action = Inflector::tableize(strtr($action, ['\\' => '-']));
        return [
            'pageTitle' => 'Dreamlands',
            'boards' => $this->container->boards,
            'currentUser' => $this->currentUser,
            'action' => $action,
            'isProd' => $this->container->envIsProd(),
        ];
    }

    /**
     * @param string $message
     * @return MessageView
     */
    protected function message(string $message)
    {
        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->attachView($this->container->instantiate(MessageView::class, [
            'message' => $message,
            'data' => $this->getGlobalViewData(),
        ]));
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function akarin()
    {
        /**
         * @var IMiddleware $akarin
         */
        $akarin = $this->container->produce(AkarinAction::class);
        return $akarin($this->request, $this->response, $this->next);
    }

    protected function beforeMain()
    {
        parent::beforeMain();

        $this->cookie = FigCookiesMiddleware::fromRequest($this->request);
        $this->currentUser = CurrentUserMiddleware::fromRequest($this->request);
    }

    protected function main()
    {
        $this->attachToRequest();
        try {
            return $this->applyInterceptors();
        } catch (ThrowableResult $result) {
            return $result->getResponse();
        }
    }

    protected function applyInterceptors()
    {
        $stack = new MiddlewareStack();
        foreach (static::getInterceptors() as $interceptor) {
            $stack->append($this->container->stubResolver->resolve($interceptor));
        }
        $next = function (ServerRequestInterface $request, ResponseInterface $response, callable $next = null) {
            $this->request = $request;
            $this->response = $response;

            return $this->run();
        };

        return $stack($this->request, $this->response, $next);
    }

    protected function throw(ResponseInterface $response)
    {
        throw new ThrowableResult($response);
    }

    /**
     * @return Entity\UserEntity
     */
    protected function getAuthedUser()
    {
        $userEntity = $this->currentUser->getUser();
        if (!$userEntity) {
            $response = $this
                ->message('请先报道')
                ->mayBack(true)
                ->render();

            return $this->throw($response);
        }

        return $userEntity;
    }

    /**
     * @return ResponseInterface
     */
    abstract protected function run();
}
