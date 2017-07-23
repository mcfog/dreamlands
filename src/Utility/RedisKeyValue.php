<?php namespace Dreamlands\Utility;

use Lit\Nexus\Interfaces\IKeyValue;
use Lit\Nexus\Traits\KeyValueTrait;
use Predis\ClientInterface;

class RedisKeyValue implements IKeyValue
{
    use KeyValueTrait;
    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var int
     */
    protected $expire;
    /**
     * @var string
     */
    protected $prefix;

    public function __construct(ClientInterface $client, $prefix, $expire)
    {
        $this->client = $client;
        $this->expire = $expire;
        $this->prefix = $prefix;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        if ($this->expire !== null) {
            $this->client->psetex($this->key($key), $this->expire, $value);
        } else {
            $this->client->set($this->key($key), $value);
        }
    }

    protected function key($key)
    {
        $prefix = empty($this->prefix) ? __CLASS__ : $this->prefix;
        return $prefix . ':' . $key;
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete($key)
    {
        $this->client->del([$this->key($key)]);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->client->get($this->key($key));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->client->exists($this->key($key));
    }
}
