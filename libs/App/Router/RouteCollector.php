<?php

declare(strict_types=1);

/*
 * This file is part of the 'octris/app' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\App\Router;

use Octris\App\Middleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;

/**
 * Class for collecting and grouping routes.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class RouteCollector implements \IteratorAggregate
{
    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * @var MiddlewareInterface[]
     */
    protected array $middleware = [];

    /**
     * @var ?RouteCollector
     */
    protected ?RouteCollector $group = null;

    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * Constructor.
     */
    public function __construct(?ContainerInterface $container = null, string $prefix = '')
    {
        $this->container = $container;
        $this->prefix = $prefix;
    }

    /**
     * Iteratre and return routes.
     *
     * @return \Generator
     */
    public function getIterator(): \Generator
    {
        return (function () {
            foreach ($this->routes as $name => $route) {
                yield $name => $route;
            }
        })();
    }

    /**
     * Return route for specified name.
     *
     * @param   string      $name
     * @return  Route
     */
    public function getRoute(string $name): Route
    {
        return $this->routes[$name];
    }

    /**
     * Create a route group with a common prefix.
     *
     * @param   string          $prefix
     * @return  RouteCollector
     */
    public function addGroup(string $prefix): RouteCollector
    {
        $group = new class($this->container, $this->prefix . $prefix, $this->routes, $this) extends RouteCollector {
            public function __construct(?ContainerInterface $container, string $prefix, array &$routes, RouteCollector $group)
            {
                parent::__construct($container, $prefix);

                $this->routes =& $routes;
                $this->group = $group;
            }
        };

        return $group;
    }

    /**
     * Add a route to the collection.
     *
     * @param   string[]        $methods
     * @param   string          $name
     * @param   string          $pattern
     * @param   mixed           $handler
     */
    public function addRoute(array $methods, string $name, string $pattern, mixed $handler): Route
    {
        $route = new Route(
            $methods,
            $name,
            $this->prefix . $pattern,
            $handler,
            $this,
            $this->container
        );

        $this->routes[$name] = $route;

        return $route;
    }

    /**
     * Add middleware to collection.
     *
     * @param   MiddlewareInterface     $middleware
     * @return  self
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Return middleware of current and parent group(s).
     *
     * @return  MiddlewareInterface[]
     */
    public function getAllMiddleware(): array
    {
        $parent = (is_null($this->group) ? [] : $this->group->getAllMiddleware());

        return array_merge($parent, $this->middleware);
    }

    /**
     * Add a GET route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @return  Route
     */
    public function get(string $name, string $pattern, callable $controller): Route
    {
        return $this->addRoute([ 'GET' ], $name, $pattern, $controller);
    }

    /**
     * Add a POST route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @return  Route
     */
    public function post(string $name, string $pattern, callable $controller): Route
    {
        return $this->addRoute([ 'POST' ], $name, $pattern, $controller);
    }

    /**
     * Add a PUT route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @return  Route
     */
    public function put(string $name, string $pattern, callable $controller): Route
    {
        return $this->addRoute([ 'PUT' ], $name, $pattern, $controller);
    }

    /**
     * Add a DELETE route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @return  Route
     */
    public function delete(string $name, string $pattern, callable $controller): Route
    {
        return $this->addRoute([ 'DELETE' ], $name, $pattern, $controller);
    }

    /**
     * Add a PATCH route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @return  Route
     */
    public function patch(string $name, string $pattern, callable $controller): Route
    {
        return $this->addRoute([ 'PATCH' ], $name, $pattern, $controller);
    }

    /**
     * Add a HEAD route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @return  Route
     */
    public function head(string $name, string $pattern, callable $controller): Route
    {
        return $this->addRoute([ 'HEAD' ], $name, $pattern, $controller);
    }

    /**
     * Add a OPTIONS route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @return  Route
     */
    public function options(string $name, string $pattern, callable $controller): Route
    {
        return $this->addRoute([ 'OPTIONS' ], $name, $pattern, $controller);
    }
}
