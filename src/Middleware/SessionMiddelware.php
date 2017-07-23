<?php namespace Dreamlands\Middleware;

use Lit\Core\AbstractMiddleware;
use Lit\Middlewares\FigCookiesMiddleware;
use Lit\Middlewares\Traits\MiddlewareTrait;
use Lit\Nexus\Derived\PrefixKeyValue;
use Lit\Nexus\Interfaces\IKeyValue;
use Lit\Nexus\Traits\EmbedKeyValueTrait;
use Lit\Nexus\Traits\KeyValueTrait;
use Psr\Http\Message\ResponseInterface;

class SessionMiddelware extends AbstractMiddleware implements IKeyValue
{
    const COOKIE_SID = 'sid';
    use MiddlewareTrait;
    use EmbedKeyValueTrait;
    use KeyValueTrait;

    const ATTR_KEY = self::class;

    protected $cookie;
    /**
     * @var IKeyValue
     */
    private $storage;

    public function __construct(IKeyValue $storage)
    {
        $this->storage = $storage;
    }


    protected function main(): ResponseInterface
    {
        $this->attachToRequest($this->request);
        $this->cookie = FigCookiesMiddleware::fromRequest($this->request);


        if (empty($sid = $this->cookie->getRequestCookie(self::COOKIE_SID))) {
            $sid = sha1(uniqid() . mt_rand());
            $this->cookie->setResponseCookie(self::COOKIE_SID, $sid, null, '/', time() + 86400 * 30, null, true, null);
        }
        $this->innerKeyValue = PrefixKeyValue::wrap($this->storage, $sid . ':');

        return $this->next();
    }
}
