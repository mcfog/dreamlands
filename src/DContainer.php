<?php namespace Dreamlands;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Dreamlands\Action\Etc\AkarinAction;
use Dreamlands\Entity\PostEntity;
use Dreamlands\Plate\PlateExtension;
use Dreamlands\Repository\Repository;
use Dreamlands\Utility\Inspector;
use League\Plates\Engine;
use Lit\Bolt\BoltContainer;
use Lit\Bolt\BoltRouteDefinition;
use Lit\Bolt\BoltRouter;
use Lit\Nexus\Cache\CacheKeyValue;
use Lit\Nexus\Utilities\KeyValueUtility;
use Monolog\Logger;
use PhpConsole\Connector;
use Psr\Log\LoggerInterface;
use Spot\Config;
use Spot\Locator;
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
    const ENV_DEV = 'DEV';
    const ENV_PROD = 'PROD';

    protected $env;

    public function __construct(array $values = [])
    {
        set_error_handler([Inspector::class, 'errorHandler']);
        set_exception_handler([Inspector::class, 'exceptionHandler']);

        $this[BoltRouter::class . '::'] = [
            'cache' => function () {
                return $this->localCache->slice('route');
            },
            'notFound' => AkarinAction::class,
        ];
        $this['config'] = require(__DIR__ . '/../config.php');

        parent::__construct($values);

        $this
            ->alias(DRouteDefinition::class, BoltRouteDefinition::class)
            ->alias(FileSystem::class, DriverInterface::class)
            ->alias(Logger::class, LoggerInterface::class, [
                'name' => 'dreamland',
                'handlers' => function () {
                    return $this->makeLoggerHandlers();
                },
            ])
            ->alias(CacheKeyValue::class, 'localCache',
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
                ])
            ->alias(Locator::class, 'db')
            ->alias(Pool::class, 'pool')
            ->alias(Repository::class, 'repo')
            ->alias(Logger::class, 'logger');

        $this[Config::class] = function () {
            $config = new Config();
            /**
             * @var Connection $connection
             */
            $connection = call_user_func_array([$config, 'addConnection'], $this->config('[db]'));

            if (!$this->envIsProd()) {
                $connection->getConfiguration()->setSQLLogger($this->produce(DebugStack::class));
            }

            $this->events->addListener(Dreamlands::EVENT_AFTER_LOGIC, function () {
                $this->repo->getUnitOfWork()->commit();
            });

            return $config;
        };

        $this[Engine::class] = function () {
            $engine = new Engine(__DIR__ . '/../templates', 'phtml');
            /** @noinspection PhpParamsInspection */
            $engine
                ->loadExtension($this->produce(PlateExtension::class));
            return $engine;
        };

        $this['boards'] = function () {
            return KeyValueUtility::getOrSet($this->localCache->sliceExpire('boards', 600), function () {
                $boards = $this->repo->getBoardsArray();
                return array_combine(array_map(function (PostEntity $board) {
                    return $board->id;
                }, $boards), $boards);
            });
        };

        if (!$this->envIsProd()) {
            $this[Connector::class] = function () {
                return Connector::getInstance();
            };
        }
    }

    /**
     * @return array
     */
    private function makeLoggerHandlers()
    {
        return array_map(function (array $handler) {
            $param = $handler[1]??[];
            return $this->instantiate($handler[0], $param);
        },
            $this->config('[log]', []));
    }

    public function config($key, $default = null)
    {
        return $this->get($this->offsetGet('config'), $key, $default);
    }

    public function envIsProd()
    {
        return $this->getEnv() === self::ENV_PROD;
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
}
