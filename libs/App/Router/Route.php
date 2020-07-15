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

class Route
{
    /**
     * @var string[]
     */
    protected array $methods;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $pattern;

    /**
     * @var callable
     */
    protected $controller;

    /**
     * @var callable[]
     */
    protected array $middleware;

    /**
     * @var MiddlewareDispatcher
     */
    protected MiddlewareDispatcher $middleware_dispatcher;

    /**
     * Constructor.
     *
     * @param   string[]        $methods
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   callable        ...$middleware     Optional middleware
     */
    public function __construct(array $methods, string $name, string $pattern, callable $controller)
    {
        $this->methods = $methods;
        $this->name = $name;
        $this->pattern = $pattern;
        $this->controller = $controller;

        $this->middleware_dispatcher = new MiddlewareDispatcher($this);
    }

    /**
     * Return methods of route.
     *
     * @return  string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Return name of route.
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return route pattern.
     *
     * @return  string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Add middleware.
     *
     * @param   MiddlewareInterface|callable|string $middleware
     */
    public function addMiddleware(MiddlewareInterface|callable|string $middleware): self
    {
        $this->middleware_dispatcher->addMiddleware($middleware);
    }
}
