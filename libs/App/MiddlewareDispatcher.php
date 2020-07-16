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

use Octris\App\Middleware\AbstractMiddlewareHandler;
use Octris\App\Exception\InvalidArgumentException;
use Octris\App\Middleware\MiddlewareInterface;
use Octris\App\Request\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Container\ContainerInterface;

class MiddlewareDispatcher implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $tip;

    /**
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container;

    /**
     * Constructor.
     */
    public function __construct(RequestHandlerInterface $handler, ?ContainerInterface $container = null)
    {
        $this->setRequestHandler($handler);
        $this->container = $container;
    }

    public function handle(Request $request): Response
    {
        return $this->tip->handle($request);
    }

    public function setRequestHandler(RequestHandlerInterface $handler)
    {
        $this->tip = $handler;
    }

    public function addMiddleware(mixed $middleware): self
    {
        $next = $this->tip;

        if (is_string($middleware)) {
            $this->tip = new class($next, $middleware, $this->container) extends AbstractMiddlewareHandler {
                public function handle(Request $request): Response
                {
                    return (new ($this->middleware)($this->container))->handle($request, $this->next);
                }
            };
        } elseif (is_callable($middleware)) {
            $this->tip = new class($next, $middleware, $this->container) extends AbstractMiddlewareHandler {
                public function handle(Request $request): Response
                {
                    return ($this->middleware)($request, $this->next);
                }
            };
        } elseif ($middleware instanceof MiddlewareInterface) {
            $this->tip = new class($next, $middleware, $this->container) extends AbstractMiddlewareHandler {
                public function handle(Request $request): Response
                {
                    return $this->middleware->handle($request, $this->next);
                }
            };
        } else {
            throw new InvalidArgumentException('Middleware must bei either a classname, a callable or a object instance implementing MiddlewareInterface.');
        }

        return $this;
    }
}
