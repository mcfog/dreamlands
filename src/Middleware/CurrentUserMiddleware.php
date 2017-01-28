<?php namespace Dreamlands\Middleware;

use Dreamlands\Entity\UserEntity;
use Dreamlands\Repository\Repository;
use Lit\Middlewares\FigCookiesMiddleware;
use Lit\Middlewares\Traits\MiddlewareTrait;
use Nimo\AbstractMiddleware;

class CurrentUserMiddleware extends AbstractMiddleware
{
    use MiddlewareTrait;
    const ATTR_KEY = self::class;

    /**
     * @var FigCookiesMiddleware
     */
    protected $cookie;
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var UserEntity
     */
    protected $user;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }

    protected function main()
    {
        $this->attachToRequest();
        $this->cookie = FigCookiesMiddleware::fromRequest($this->request);

        $this->login();

        $response = $this->next();

        return $response;
    }

    public function spawnUser()
    {
        if(!isset($this->user)) {
            $this->user = $user = UserEntity::spawn();

            $this->repository->getUnitOfWork()->persist($user);
            $this->writeCookie($user);

        }

        return $this->user;
    }

    protected function writeCookie(UserEntity $userEntity)
    {
        $this->cookie->setResponseCookie('user', $userEntity->hash, null, time() + 365 * 86400, true);
    }

    protected function login()
    {
        $hash = $this->cookie->getRequestCookie('user');
        if(empty($hash)) {
            return;
        }

        $this->user = $this->repository->getUserByHash($hash);
    }
}
