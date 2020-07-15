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
use Octris\App\Middleware\MiddlewareCollectorTrait;
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
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var Route|RouteCollector[]
     */
    protected array $items = [];

    /**
     * Constructor.
     */
    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * This implements the \RecursiveIterator interface, so it's possible to iterate all routes and subgroups of
     * routes.
     *
     * @return \IteratorIterator
     */
    public function getIterator(): \IteratorIterator
    {
        $iterator = (function () {
            foreach ($this->items as $key => $item) {
                yield $key => $item;
            }
        })();

        return new class($iterator) extends \IteratorIterator implements \RecursiveIterator {
            public function getChildren()
            {
                return $this->current()->getIterator();
            }
            public function hasChildren()
            {
                return ($this->current() instanceof \IteratorAggregate);
            }
        };
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
     */
    public function addGroup(string $prefix): RouteCollector
    {
        $group = new RouteCollector($this->prefix . $prefix);

        $this->items[] = $group;

        return $group;
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
        $route = new Route(
            $methods,
            $name,
            $this->prefix . $pattern,
            $controller
        );

        $this->items[] = $route;

        return $route;
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
