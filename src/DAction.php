<?php namespace Dreamlands;

use Doctrine\Common\Inflector\Inflector;
use Dreamlands\Action\Etc\AkarinAction;
use Dreamlands\Action\Etc\UnicornAction;
use Dreamlands\Exceptions\DException;
use Dreamlands\Exceptions\ThrowableResult;
use Dreamlands\Middleware\CurrentUserMiddleware;
use Dreamlands\Middleware\SessionMiddelware;
use Dreamlands\Plate\AjaxMessageView;
use Dreamlands\Plate\AjaxView;
use Dreamlands\Plate\IMessageView;
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

    /**
     * @var FigCookiesMiddleware
     */
    protected $cookie;

    /**
     * @var SessionMiddelware
     */
    protected $session;

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
        /**
         * @var PlateView $view
         */
        $view = $this->container->instantiate(PlateView::class, [
            'name' => $name,
            'data' => $this->getGlobalViewData(),
        ]);

        return $this->attachView($view->setJsData([
            'currentUser' => $this->currentUser
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

    protected function ajax(): AjaxView
    {
        /**
         * @var AjaxView $ajaxView
         */
        /** @noinspection PhpParamsInspection */
        $ajaxView = $this->attachView($this->container->instantiate(AjaxView::class));

        return $ajaxView;
    }

    /**
     * @param string $message
     * @return IMessageView
     */
    protected function message(string $message): IMessageView
    {
        /**
         * @var IMessageView $messageView
         */
        $className = $this->isAjax() ? AjaxMessageView::class : MessageView::class;
        /** @noinspection PhpParamsInspection */
        $messageView = $this->attachView($this->container->instantiate($className, [
            'message' => $message,
            'data' => $this->getGlobalViewData() + [
                    'title' => '提示信息'
                ],
        ]));

        return $messageView;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function akarin(): ResponseInterface
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
            $this->session = SessionMiddelware::fromRequest($this->request);
            $this->currentUser = CurrentUserMiddleware::fromRequest($this->request);
        }

        try {
            try {
                return $this->run();
            } catch (\Throwable $e) {
                return $this->handleException($e);
            }
        } catch (DException $exception) {
            return $this->message($exception->getMessage())
                ->mayJump('/', '首页')
                ->mayBack(true)
                ->render();
        } catch (ThrowableResult $result) {
            return $result->getResponse();
        } catch (\Throwable $e) {
            $this->logger->error('action_fail', [
                'e' => $e
            ]);
            if ($this->isAjax()) {
                return $this->message('发生了未知的异常')->render();
            }

            throw $e;
        }
    }

    protected function handleException(\Throwable $e): ResponseInterface
    {
        throw $e;
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

    protected function isAjax()
    {
        return $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * @return ResponseInterface
     */
    abstract protected function run(): ResponseInterface;
}
