<?php

namespace Phwoolcon\Auth;

use Phalcon\Di;
use Phalcon\Security;
use Phwoolcon\Auth\Adapter\Exception;
use Phwoolcon\Auth\Adapter\Generic;
use Phwoolcon\Config;
use Phwoolcon\Router;

class Auth
{
    /**
     * @var Di
     */
    protected static $di;
    /**
     * @var AdapterInterface|Generic
     */
    protected static $instance;

    public static function getInstance()
    {
        static::$instance or static::$instance = static::$di->getShared('auth');
        return static::$instance;
    }

    public static function getOption($key)
    {
        static::$instance or static::$instance = static::$di->getShared('auth');
        return static::$instance->getOption($key);
    }

    public static function register(Di $di)
    {
        static::$di = $di;
        $di->setShared('auth', function () {
            $class = Config::get('auth.adapter');
            $options = Config::get('auth.options');
            strpos($class, '\\') === false and $class = 'Phwoolcon\\Auth\\Adapter\\' . $class;
            /* @var Security $hasher */
            $hasher = static::$di->getShared('security');
            $hasher->setDefaultHash($options['security']['default_hash']);
            $hasher->setWorkFactor($options['security']['work_factor']);
            $adapter = new $class($options, $hasher);
            if (!$adapter instanceof AdapterInterface) {
                throw new Exception('Auth adapter class should implement ' . AdapterInterface::class);
            }
            return $adapter;
        });
        $routes = Config::get('auth.routes');
        is_array($routes) and static::registerRoutes($routes);
    }

    public static function registerRoutes(array $routes = [])
    {
        /* @var Router $router */
        $router = static::$di->getShared('router');
        $router->addRoutes($routes);
    }
}
