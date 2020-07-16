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

namespace Octris\App\Middleware;

use Octris\App\Request\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

/**
 * Base class for implementing middleware handlers.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class AbstractMiddlewareHandler implements RequestHandlerInterface
{
    /**
     * @var mixed
     */
    protected mixed $middleware;

    /**
     * @var RequestHandlerInterface
     */
    protected RequestHandlerInterface $next;

    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * AbstractMiddlewareHandler constructor.
     *
     * @param RequestHandlerInterface $next
     * @param mixed $middleware
     * @param ContainerInterface|null $container
     */
    public function __construct(RequestHandlerInterface $next, mixed $middleware, ?ContainerInterface $container = null)
    {
        if (is_callable($middleware) && $middleware instanceof Closure) {
            $middleware = $middleware->bindTo($container);
        }

        $this->next = $next;
        $this->middleware = $middleware;
        $this->container = $container;
    }
}
