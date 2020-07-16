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

use Octris\App\Request\AbstractRequestHandler;
use Octris\App\Request\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Core class for web applications.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class AbstractApp implements RequestHandlerInterface
{
    /**
     * Router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * State instance.
     *
     * @var App\State|null
     */
    private ?App\State $state = null;

    /**
     * @var MiddlewareDispatcher
     */
    protected MiddlewareDispatcher $middleware_dispatcher;

    /**
     * @var ContainerInterface
     */
    protected ?ContainerInterface $container;

    /**
     * Constructor.
     *
     * @param   Router                          $router
     * @param   ContainerInterface              $container
     */
    public function __construct(Router $router, ?ContainerInterface $container = null)
    {
        $this->router = $router;
        $this->container = $container;
        $this->middleware_dispatcher = new MiddlewareDispatcher($this, $container);
    }

    /**
     * Return application state.
     *
     * @return  \Octris\App\State          State of application.
     */
    public function getState(): App\State
    {
        if (is_null($this->state)) {
            if (!is_null($tmp = $this->request->get('state'))) {
                $this->state = App\State::thaw($tmp);
            } else {
                $this->state = new App\State();
            }
        }

        return $this->state;
    }

    /**
     * Initialize application.
     */
    abstract protected function initialize(): void;

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
     * Handle request.
     *
     * @param   Request     $request
     * @return  Response
     */
    public function handle(Request $request): Response
    {
        return $this->router->handle($request);
    }

    /**
     * Application main loop.
     *
     * @param   ?Request        $request
     */
    final public function run(?Request $request = null): void
    {
        if (is_null($request)) {
            $request = Request::createFromGlobals();
        }

        $this->initialize();

        $response = $this->middleware_dispatcher->handle($request);
        $response->prepare($request);
        $response->send();
    }
}
