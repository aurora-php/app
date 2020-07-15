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

use Octris\App\Exception;
use Octris\App\MiddlewareDispatcher;

/**
 * Router class.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class RouteCollector implements \IteratorAggregate
{
    /**
     * @var callable[]
     */
    protected array $current_middleware = [];

    /**
     * @var string
     */
    protected string $current_group_prefix = '';

    /**
     * @var mixed[]
     */
    protected array $routes = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    public function getIterator(): \Generator
    {
        return (function () {
            foreach ($this->routes as $name => $pattern) {
                yield $name => $pattern;
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
     * Create a route group with a common prefix. All routes created in the passed callback
     * will have the given group prefix prepended.
     *
     * @param   string          $prefix
     * @param   callable        $group          Group configuration
     * @param   callable        ...$middleware  Optional middleware
     */
    public function addGroup(string $prefix, callable $group, callable ...$middleware): void
    {
        $previous_middleware = $this->current_middleware;
        $previous_group_prefix = $this->current_group_prefix;

        $this->current_middleware = array_merge($this->current_middleware, $middleware);
        $this->current_group_prefix .= $prefix;

        $group($this);

        $this->current_middleware = $previous_middleware;
        $this->current_group_prefix = $previous_group_prefix;
    }

    /**
     * Add a route to the collection.
     *
     * @param   string[]        $methods
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     */
    public function addRoute(array $methods, string $name, string $pattern, callable $controller): Route
    {
        $this->routes[$name] = new Route(
            $methods,
            $name,
            $this->current_group_prefix . $pattern,
            $controller
        );

        return $this->routes[$name];
    }

    /**
     * Add a GET route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware  Optional middleware
     */
    public function get(string $name, string $pattern, callable $controller, callable ...$middleware): void
    {
        $this->addRoute([ 'GET' ], $name, $pattern, $controller, ...$middleware);
    }

    /**
     * Add a POST route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware  Optional middleware
     */
    public function post(string $name, string $pattern, callable $controller, callable ...$middleware): void
    {
        $this->addRoute([ 'POST' ], $name, $pattern, $controller, ...$middleware);
    }

    /**
     * Add a PUT route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware  Optional middleware
     */
    public function put(string $name, string $pattern, callable $controller, callable ...$middleware): void
    {
        $this->addRoute([ 'PUT' ], $name, $pattern, $controller, ...$middleware);
    }

    /**
     * Add a DELETE route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware  Optional middleware
     */
    public function delete(string $name, string $pattern, callable $controller, callable ...$middleware): void
    {
        $this->addRoute([ 'DELETE' ], $name, $pattern, $controller, ...$middleware);
    }

    /**
     * Add a PATCH route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware  Optional middleware
     */
    public function patch(string $name, string $pattern, callable $controller, callable ...$middleware): void
    {
        $this->addRoute([ 'PATCH' ], $name, $pattern, $controller, ...$middleware);
    }

    /**
     * Add a HEAD route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware  Optional middleware
     */
    public function head(string $name, string $pattern, callable $controller, callable ...$middleware): void
    {
        $this->addRoute([ 'HEAD' ], $name, $pattern, $controller, ...$middleware);
    }

    /**
     * Add a OPTIONS route to the collection.
     *
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware  Optional middleware
     */
    public function options(string $name, string $pattern, callable $controller, callable ...$middleware): void
    {
        $this->addRoute([ 'OPTIONS' ], $name, $pattern, $controller, ...$middleware);
    }
}
