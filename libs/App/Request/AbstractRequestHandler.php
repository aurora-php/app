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

namespace Octris\App\Request;

use Psr\Container\ContainerInterface;

/**
 * Base class for implementing request handlers.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class AbstractRequestHandler implements RequestHandlerInterface
{
    /**
     * @var mixed
     */
    protected mixed $handler;

    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * AbstractRequestHandler constructor.
     *
     * @param RequestHandlerInterface $next
     * @param mixed $middleware
     * @param ContainerInterface|null $container
     */
    public function __construct(mixed $handler, ?ContainerInterface $container = null)
    {
        if (is_callable($handler) && $handler instanceof Closure) {
            $handler = $handler->bindTo($container);
        }

        $this->handler = $handler;
        $this->container = $container;
    }
}
