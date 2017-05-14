<?php namespace Dreamlands\Action\Auth;

use Dreamlands\DAction;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Github;
use Lit\Nexus\Interfaces\IPropertyInjection;
use Psr\Http\Message\ResponseInterface;

class GithubCallbackAction extends DAction implements IPropertyInjection
{
    const PATH = '/auth/github/callback';

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

    protected function run(): ResponseInterface
    {
        $code = $this->getQueryParam('[code]');
        $state = $this->getQueryParam('[state]');

        if ($this->cookie->getRequestCookie('state', false) !== $state) {
            return $this->message('登录失败……')
                ->mayBack(true)
                ->render();
        }
        try {
            $accessToken = $this->github->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
            $resourceOwner = $this->github->getResourceOwner($accessToken);
            //DELETEME
            echo '<xmp>' . PHP_EOL;
            var_dump($accessToken, $resourceOwner->getId(), $resourceOwner->toArray());
            die;
            //DELETEME END
        } catch (IdentityProviderException $e) {
            return $this->message('登录失败……')
                ->mayBack(true)
                ->render();
        }
    }
}
