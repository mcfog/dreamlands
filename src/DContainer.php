<?php namespace Dreamlands;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Dreamlands\Action\Etc\AkarinAction;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Middleware\SessionMiddelware;
use Dreamlands\Plate\DPlateEngine;
use Dreamlands\Repository\Repository;
use Dreamlands\Utility\Inspector;
use Dreamlands\Utility\RedisKeyValue;
use FastRoute\Dispatcher\GroupCountBased;
use Hashids\Hashids;
use League\Plates\Engine;
use Lit\Air\Configurator;
use Lit\Air\Factory;
use Lit\Bolt\BoltContainer;
use Lit\Bolt\BoltRouter;
use Lit\Nexus\Cache\CacheKeyValue;
use Lit\Nexus\Utilities\KeyValueUtility;
use Monolog\Logger;
use PhpConsole\Connector;
use Predis\Client;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use Spot\Config;
use Spot\Locator;
use Stash\Driver\BlackHole;
use Stash\Driver\FileSystem;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

/**
 * Class DContainer
 * @package Dreamlands
 *
 * @property CacheKeyValue $localCache
 * @property Locator $db
 * @property Repository $repo
 * @property PostEntity[] $boards
 * @property LoggerInterface $logger
 */
class DContainer extends BoltContainer
{
    const ENV_DEV = 'dev';
    const ENV_PROD = 'prod';

    protected $env;

    public function __construct(?array $config = null)
    {
        set_error_handler([Inspector::class, 'errorHandler']);
        set_exception_handler([Inspector::class, 'exceptionHandler']);

        $this->set('config', require(__DIR__ . '/../config.php'));

        parent::__construct(($config ?: []) + [
                BoltRouter::class => (object)[
                    'autowire',
                    null,
                    [
                        'cache' => function () {
                            return $this->localCache->slice('route');
                        },
                        'routeDefinition' => (object)['autowire', DRouteDefinition::class],
                        'dispatcherClass' => GroupCountBased::class,
                        'notFound' => (object)['autowire', AkarinAction::class],
                    ]
                ],

                DriverInterface::class => (object)['autowire', FileSystem::class],
                ClientInterface::class => (object)['autowire', Client::class],
                LoggerInterface::class => (object)[
                    'autowire',
                    Logger::class,
                    [
                        'name' => 'dreamland',
                        'handlers' => function () {
                            return $this->makeLoggerHandlers();
                        },
                    ]
                ],
                Engine::class => (object)['autowire', DPlateEngine::class, [__DIR__ . '/../templates', 'phtml']],


                'localCache' => (object)[
                    'instance',
                    'decorator' => ['cache' => null],
                    CacheKeyValue::class,
                    [
                        function () {
                            /**
                             * @var Pool $pool
                             */
                            $pool = $this->pool;
                            $this->events->addListener(Dreamlands::EVENT_AFTER_LOGIC, [$pool, 'commit']);

                            return $pool;
                        },
                        86400
                    ],
                ],

                'db' => (object)['autowire', Locator::class],
                'pool' => (object)['autowire', Pool::class],
                'repo' => (object)['autowire', Repository::class],
                'logger' => (object)['alias', LoggerInterface::class],

                SessionMiddelware::class => (object)[
                    'autowire',
                    null,
                    [
                        'storage' => (object)[
                            'instance',
                            RedisKeyValue::class,
                            [
                                'prefix' => 'session',
                                'expire' => 86400 * 3000,
                            ]
                        ],
                    ]
                ],
                Hashids::class => (object)[
                    'autowire',
                    null,
                    [
                        'minHashLength' => 4
                    ]
                ],

                Config::class => function () {
                    $config = new Config();
                    /**
                     * @var Connection $connection
                     */
                    $connection = call_user_func_array([$config, 'addConnection'], $this->config('[db]'));

                    if (!$this->envIsProd()) {
                        $connection->getConfiguration()->setSQLLogger(Factory::of($this)->produce(DebugStack::class));
                    }

                    $this->events->addListener(Dreamlands::EVENT_AFTER_LOGIC, function () {
                        $this->repo->getUnitOfWork()->commit();
                    });

                    return $config;
                },
                'boards' => function () {
                    return KeyValueUtility::getOrSet($this->localCache->sliceExpire('boards', 600), function () {
                        $boards = $this->repo->getBoardsArray();

                        return array_combine(array_map(function (PostEntity $board) {
                            return $board->id;
                        }, $boards), $boards);
                    });
                },

            ]
        );


        if (!$this->envIsProd()) {
            $this
                ->define(Connector::class, self::multiton([Connector::class, 'getInstance']))
                ->define(DriverInterface::class, self::autowire(BlackHole::class));
        }

        Configurator::config($this, $this->config('[container]', []));
    }

    public function config($key, $default = null)
    {
        return $this->access($this->config, $key, $default);
    }

    public function envIsProd()
    {
        return $this->getEnv() === self::ENV_PROD;
    }

    public function getOrProduce($classname)
    {
        if ($this->has($classname)) {
            return $this->get($classname);
        }
        return Factory::of($this)->produce($classname);
    }

    public function instantiate(string $className, array $extraParameters = [])
    {
        return Factory::of($this)->instantiate($className, $extraParameters);
    }

    /**
     * @return string
     */
    protected function getEnv()
    {
        if (!isset($this->env)) {
            $this->env = $this->config('[env]', self::ENV_PROD);
        }

        return $this->env;
    }

    /**
     * @return array
     */
    private function makeLoggerHandlers()
    {
        return array_map(function (array $handler) {
            $param = $handler[1] ?? [];
            return Factory::of($this)->instantiate($handler[0], $param);
        },
            $this->config('[log]', []));
    }
}
