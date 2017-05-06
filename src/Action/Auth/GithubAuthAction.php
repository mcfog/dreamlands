<?php namespace Dreamlands\Action\Auth;

use Dflydev\FigCookies\SetCookie;
use Dreamlands\DAction;
use League\OAuth2\Client\Provider\Github;
use Lit\Nexus\Interfaces\IPropertyInjection;

class GithubAuthAction extends DAction implements IPropertyInjection
{
    const PATH = '/auth/github';

    /**
     * @var Github
     */
    protected $github;

    public static function getInjectedProperties()
    {
        return [
            'github' => Github::class,
        ];
    }

    protected function run()
    {
        $url = $this->github->getAuthorizationUrl();
        $state = $this->github->getState();
        $this->cookie->setResponseCookie('state',
            SetCookie::create('state', $state)
                ->withPath('/')
        );

        return $this->redirect($url);
    }
}
