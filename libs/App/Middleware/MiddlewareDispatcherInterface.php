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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface MiddlewareDispatcherInterface extends MiddlewareInterface
{
    /**
     * Set request handler.
     *
     * @param   RequestHandlerInterface                 $handler
     */
    public function setRequestHandler(RequestHandlerInterface $handler);

    /**
     * Add middleware.
     *
     * @param   MiddlewareInterface|callable|string     $middleware
     */
    public function addMiddleware(MiddlewareInterface|callable|string $middleware): self;
}