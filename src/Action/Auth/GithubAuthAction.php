<?php namespace Dreamlands\Action\Auth;

use Dreamlands\DAction;
use League\OAuth2\Client\Provider\Github;
use Psr\Http\Message\ResponseInterface;

class GithubAuthAction extends DAction
{
    const PATH = '/auth/github';

    /**
     * @var Github
     */
    protected $github;

    public function injectGithub(Github $github)
    {
        $this->github = $github;
        return $this;
    }

    protected function run(): ResponseInterface
    {
        if ($this->currentUser->getModerator()) {
            return $this->message('已经登录')
                ->mayBack(true)
                ->render();
        }
        
        $url = $this->github->getAuthorizationUrl();
        $state = $this->github->getState();
        $this->cookie->setResponseCookie('state', $state, null, '/', null, null, true);

        return $this->redirect($url);
    }
}
