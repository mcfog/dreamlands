<?php namespace Dreamlands\Middleware;

use Dreamlands\Entity\UserEntity;
use Dreamlands\Repository\Repository;
use Dreamlands\Repository\UnitOfWork;
use Lit\Core\AbstractMiddleware;
use Lit\Middlewares\FigCookiesMiddleware;
use Lit\Middlewares\Traits\MiddlewareTrait;
use Psr\Http\Message\ResponseInterface;

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

    /**
     * @param $nickname
     * @return UserEntity
     */
    public function spawnUser($nickname)
    {
        if (false !== strpos($nickname, ':')) {
            $user = $this->login($nickname);
            if ($user) {
                return $user;
            } else {
                throw new \RuntimeException('??……');
            }
        }

        if (!isset($this->user)) {
            $count = 0;

            do {
                if (++$count > 3) {
                    throw new \RuntimeException('自古枪兵……');
                }

                $this->user = $user = UserEntity::spawn($nickname);
            } while ($this->repository->getUserByDisplayname($user->getDisplayName()));

            $user->last_ip = $this->request->getAttribute('ip_address');

            $this->repository->runUnitOfWork(function (UnitOfWork $unitOfWork) use ($user) {
                $unitOfWork->persist($user);
                $unitOfWork->commit();
            });
            $this->writeCookie($user);
        }

        return $this->user;
    }

    protected function login($login)
    {
        list($name, $hash) = explode(':', $login);
        $user = $this->getAuthedUser($hash, $name);
        if ($user) {
            $this->user = $user;
            $this->writeCookie($user);
        }

        return $user;
    }

    protected function writeCookie(UserEntity $userEntity)
    {
        $this->cookie->setResponseCookie('hash', $userEntity->hash
            , null, time() + 365 * 86400, true, null, '/');

        $this->cookie->setResponseCookie('name', $userEntity->getDisplayName()
            , null, time() + 365 * 86400, true, null, '/');
    }

    protected function main(): ResponseInterface
    {
        $this->cookie = FigCookiesMiddleware::fromRequest($this->request);
        $this->attachToRequest($this->request);

        $this->checkLogin();

        return $this->next();
    }

    protected function checkLogin()
    {
        $hash = $this->cookie->getRequestCookie('hash');
        if (empty($hash)) {
            return;
        }

        $this->user = $this->getAuthedUser($hash, $this->cookie->getRequestCookie('name'));
    }

    protected function getAuthedUser($hash, $name)
    {
        $user = $this->repository->getUserByHash($hash);
        if ($user->getDisplayName() === $name) {
            return $user;
        }

        return null;
    }

}
