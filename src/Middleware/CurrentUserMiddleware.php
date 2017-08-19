<?php namespace Dreamlands\Middleware;

use Dreamlands\Entity\ModeratorEntity;
use Dreamlands\Entity\UserEntity;
use Dreamlands\Exceptions\DException;
use Dreamlands\Repository\Repository;
use Dreamlands\Repository\UnitOfWork;
use Lit\Core\AbstractMiddleware;
use Lit\Middlewares\FigCookiesMiddleware;
use Lit\Middlewares\Traits\MiddlewareTrait;
use Psr\Http\Message\ResponseInterface;

class CurrentUserMiddleware extends AbstractMiddleware
{
    use MiddlewareTrait;

    const SESSION_MOD_ID = 'moderator_id';
    const COOKIE_NAME = 'name';
    const COOKIE_HASH = 'hash';

    /**
     * @var FigCookiesMiddleware
     */
    protected $cookie;
    /**
     * @var SessionMiddelware
     */
    protected $session;
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var UserEntity
     */
    protected $user;
    /**
     * @var ModeratorEntity
     */
    protected $moderator;

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
                throw new DException('但是可耻地失败了');
            }
        }

        if (!isset($this->user)) {
            $count = 0;

            do {
                if (++$count > 3) {
                    throw new DException('自古枪兵幸运E');
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

    protected function getAuthedUser($hash, $name)
    {
        $user = $this->repository->getUserByHash($hash);
        if ($user && $user->getDisplayName() === $name) {
            return $user;
        }

        return null;
    }

    protected function writeCookie(UserEntity $userEntity)
    {
        $this->cookie
            ->setResponseCookies([
                self::COOKIE_HASH => $userEntity->hash,
                self::COOKIE_NAME => $userEntity->getDisplayName(),
            ], null, '/', time() + 365 * 86400, null, true);
    }

    public function logout()
    {
        $this->cookie->setResponseCookies([
            self::COOKIE_HASH => '',
            self::COOKIE_NAME => '',
        ], null, '/', 0, null, true);
    }

    /**
     * @return ModeratorEntity|null
     */
    public function getModerator(): ?ModeratorEntity
    {
        return $this->moderator;
    }

    /**
     *
     * @param ModeratorEntity $moderator
     * @return $this
     */
    public function setModerator(
        ModeratorEntity $moderator
    ) {
        $this->moderator = $moderator;
        $this->session->set(self::SESSION_MOD_ID, $moderator->id);

        return $this;
    }

    protected function main(): ResponseInterface
    {
        $this->cookie = FigCookiesMiddleware::fromRequest($this->request);
        $this->session = SessionMiddelware::fromRequest($this->request);
        $this->attachToRequest($this->request);

        $this->checkLogin();

        return $this->next();
    }

    protected function checkLogin()
    {
        $hash = $this->cookie->getRequestCookie(self::COOKIE_HASH);
        if (!empty($hash)) {
            $this->user = $this->getAuthedUser($hash, $this->cookie->getRequestCookie(self::COOKIE_NAME));
        }

        $id = $this->session->get(self::SESSION_MOD_ID);
        if ($id) {
            $this->moderator = $this->repository->byId(ModeratorEntity::class, $id);
        }
    }

}
