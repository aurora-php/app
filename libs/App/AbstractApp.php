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

use Octris\App\Middleware\MiddlewareDispatcherInterface;
use Octris\App\Request\RequestHandlerInterface;
use RouterInterface;
use Octris\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Core class for web applications.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class AbstractApp
{
    /**
     * Router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * Application configuration.
     *
     * @var Config
     */
    protected Config $config;

    /**
     * Request instance.
     *
     * @var Request
     */
    private Request $request;

    /**
     * Response instance.
     *
     * @var Response|null
     */
    private ?Response $response = null;

    /**
     * State instance.
     *
     * @var App\State|null
     */
    private ?App\State $state = null;

    /**
     * @var MiddlewareDispatcher
     */
    private MiddlewareDispatcher $middleware_dispatcher;

    /**
     * Constructor.
     *
     * @param   Config                          $config
     * @param   Router                          $router
     */
    public function __construct(/*Config $config,*/ Router $router, ?MiddlewareDispatcherInterface $middleware_dispatcher = null)
    {
        $this->router = $router;

        $handler = new class($this) implements RequestHandlerInterface {
            public function __construct(private AbstractApp $app)
            {
            }

            public function __invoke(Request $request, Response $response): Response
            {
                return $this->app->handle($request, $response);
            }
        };

        if (is_null($middleware_dispatcher)) {
            $middleware_dispatcher = new MiddlewareDispatcher($handler);
        } else {
            $middleware_dispatcher->setRequestHandler($handler);
        }

        $this->middleware_dispatcher = $middleware_dispatcher;
    }

    /**
     * Return request instance
     *
     * @return  Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Return response instance, create one on the first call.
     *
     * @return  Response
     */
    public function getResponse(): Response
    {
        if (is_null($this->response)) {
            $this->response = new Response();
        }

        return $this->response;
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
    protected function initialize(): void
    {
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
    }

    /**
     * Handle request.
     *
     * @param   Request     $request
     * @param   Response    $response
     * @return  Response    $response
     */
    public function handle(Request $request, Response $response): Response
    {
        return $this->router->route($this, $request, $response);
    }

    /**
     * Application main loop.
     */
    final public function run(): void
    {
        $this->initialize();

        $response = $this->middleware_dispatcher->handle($this->request, $this->response);
        $response->send();
    }
}
