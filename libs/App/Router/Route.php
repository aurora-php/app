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

use Octris\App\Middleware\MiddlewareDispatcherTrait;
use Octris\App\MiddlewareCollector;
use Octris\App\MiddlewareDispatcher;
use Octris\App\Request\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Route implements RequestHandlerInterface
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
     * @var RouteCollector
     */
    protected RouteCollector $group;

    /**
     * @var MiddlewareDispatcher
     */
    protected MiddlewareDispatcher $middleware_dispatcher;

    /**
     * @var bool
     */
    protected bool $group_middleware_added = false;

    /**
     * Constructor.
     *
     * @param   string[]        $methods
     * @param   string          $name
     * @param   string          $pattern
     * @param   callable        $controller
     * @param   string          $identifier
     * @param   RouteCollector  $group
     */
    public function __construct(array $methods, string $name, string $pattern, callable $controller, RouteCollector $group)
    {
        $this->methods = $methods;
        $this->name = $name;
        $this->pattern = $pattern;
        $this->controller = $controller;
        $this->group = $group;

        $handler = new class($controller) implements RequestHandlerInterface {
            private $controller;

            public function __construct(callable $controller)
            {
                $this->controller = $controller;
            }

            public function handle(Request $request): Response
            {
                return ($this->controller)($request);
            }
        };

        $this->middleware_dispatcher = new MiddlewareDispatcher($handler);
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
     * @param   mixed           $middleware
     * @return  mixed
     */
    public function addMiddleware(mixed $middleware): self
    {
        $this->middleware_dispatcher->addMiddleware($middleware);

        return $this;
    }

    /**
     * Handle route.
     *
     * @param   Request         $request
     * @return  Response
     */
    public function handle(Request $request): Response
    {
        if (!$this->group_middleware_added)  {
            $inner = $this->middleware_dispatcher;
            $this->middleware_dispatcher = new MiddlewareDispatcher($inner);

            foreach (array_reverse($this->group->getAllMiddleware()) as $middleware) {
                $this->middleware_dispatcher->addMiddleware($middleware);
            }

            $this->group_middleware_added = true;
        }


        return $this->middleware_dispatcher->handle($request);
    }
}
