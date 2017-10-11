<?php namespace Dreamlands\Action\Auth;

use Dreamlands\DAction;
use Dreamlands\Entity\ModeratorEntity;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Github;
use Psr\Http\Message\ResponseInterface;

class GithubCallbackAction extends DAction
{
    const PATH = '/auth/github/callback';
    protected const MSG_LOGIN_FAIL = '登录失败……';

    /**
     * @var Github
     */
    protected $github;

    /**
     *
     * @param Github $github
     * @return $this
     */
    public function injectGithub(Github $github)
    {
        $this->github = $github;

        return $this;
    }

    protected function run(): ResponseInterface
    {
        $code = $this->getQueryParam('[code]');
        $state = $this->getQueryParam('[state]');

        if ($this->cookie->getRequestCookie('state', false) !== $state) {
            return $this->message(self::MSG_LOGIN_FAIL)
                ->mayBack(true)
                ->render();
        }

        $accessToken = $this->github->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
        $resourceOwner = $this->github->getResourceOwner($accessToken);

        $githubUser = $resourceOwner->toArray();
        $moderator = $this->repo->getModerator(ModeratorEntity::PROVIDER_GITHUB, $githubUser['login']);
        if (!$moderator) {
            return $this->message(self::MSG_LOGIN_FAIL)
                ->mayBack(true)
                ->render();
        }

        $this->currentUser->setModerator($moderator);

        return $this->message('おかえり')
            ->mayJump('/', '首页', true)
            ->render();

    }

    protected function handleException(\Throwable $e): ResponseInterface
    {
        if ($e instanceof IdentityProviderException) {
            return $this->message(self::MSG_LOGIN_FAIL)
                ->mayBack(true)
                ->render();
        }

        throw $e;
    }
}
