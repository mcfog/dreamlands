<?php namespace Dreamlands;

use Doctrine\Common\Inflector\Inflector;
use Dreamlands\Action\Etc\AkarinAction;
use Dreamlands\Action\Etc\UnicornAction;
use Dreamlands\Exceptions\ThrowableResult;
use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Plate\MessageView;
use Dreamlands\Plate\PlateView;
use Dreamlands\Repository\Repository;
use Lit\Bolt\BoltAction;
use Lit\Bolt\BoltContainer;
use Lit\Middlewares\FigCookiesMiddleware;
use Lit\Middlewares\Traits\MiddlewareTrait;
use Psr\Http\Message\ResponseInterface;
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
         * @var DAction $akarin
         */
        $akarin = $this->container->produce(AkarinAction::class);
        return $akarin->process($this->request, $this->delegate);
    }

    protected function main(): ResponseInterface
    {
        if (!$this instanceof UnicornAction) {
            $this->cookie = FigCookiesMiddleware::fromRequest($this->request);
            $this->currentUser = CurrentUserMiddleware::fromRequest($this->request);
        }

        try {
            return $this->run();
        } catch (ThrowableResult $result) {
            return $result->getResponse();
        }
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
