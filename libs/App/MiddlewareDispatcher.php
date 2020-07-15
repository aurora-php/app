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

namespace Octris\App;

use RouterInterface;
use Octris\Config;
use Octris\App\Middleware\MiddlewareDispatcherInterface;
use Octris\App\Request\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $tip;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Constructor.
     */
    public function __construct(RequestHandlerInterface $handler, Container $container = null)
    {
        $this->setRequestHandler($handler);
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestHandler(RequestHandlerInterface $handler)
    {
        $this->tip = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function addMiddleware(MiddlewareInterface|callable|string $middleware): self
    {
        $next = $this->tip;

        $this->top = new class($next, $middleware, $this->container) implements RequestHandlerInterface {
            private RequestHandlerInterface $next;
            private mixed $middleware;
            private Container $container;

            public function __construct(RequestHandlerInterface $next, MiddlewareInterface|callable|string $middleware, Container $container)
            {
                $this->next = $next;
                $this->middleware = $middleware;
                $this->container = $container;
            }

            public function handle(Request $request, Response $response)
            {
                if (is_string($this->middleware)) {
                    if (!class_implements($this->middleware, MiddlewareInterface)) {
                        throw new \RuntimeException();
                    }

                    $this->middleware = new ($this->middleware)();
                }

                if ($this->middleware instanceof MiddlewareInterface) {
                    $response = $this->middleware->handle($request, $response, $this->next);
                } else {
                    $response = ($this->middleware)($request, $response, $this->next);
                }

                return $response;
            }
        };
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, Response $response): Response
    {
        return $this->tip->handle($request, $response);
    }
}
