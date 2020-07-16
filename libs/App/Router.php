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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Router.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */

class Router
{
    /**
     * @var \FastRoute\Dispatcher
     */
    protected \FastRoute\Dispatcher $dispatcher;

    /**
     * @var Router\RouteCollector
     */
    protected Router\RouteCollector $route_collector;

    /**
     * Constructor.
     *
     * @param   Router\RouteCollector   $route_collector
     * @param   mixed[]                 $options
     */
    public function __construct(Router\RouteCollector $route_collector, array $options = [])
    {
        $options += [
            'routeParser' => \FastRoute\RouteParser\Std::class,
            'dataGenerator' => \FastRoute\DataGenerator\GroupCountBased::class,
            'dispatcher' => \FastRoute\Dispatcher\GroupCountBased::class,
            'routeCollector' => \FastRoute\RouteCollector::class,
            'cacheDisabled' => false
        ];

        $this->route_collector = $route_collector;

        $setup = function (\FastRoute\RouteCollector $r) {
            foreach ($this->route_collector as $route) {
                $r->addRoute(
                    $route->getMethods(),
                    $route->getPattern(),
                    $route->getName()
                );
            }
        };

        if (!$options['cacheDisabled']) {
            if (!isset($options['cacheFile'])) {
                throw new LogicException('Must specify "cacheFile" option.');
            }

            $this->dispatcher = \FastRoute\cachedDispatcher($setup, $options);
        } else {
            $this->dispatcher = \FastRoute\simpleDispatcher($setup, $options);
        }
    }

    /**
     * Routing.
     *
     * @param   Request     $request
     * @return  Response
     */
    protected function routing(Request $request): Response
    {
        $result = $this->dispatcher->dispatch(
            $request->getMethod(),
            parse_url(  // https://github.com/nikic/FastRoute/issues/19
                $request->getRequestUri(),
                PHP_URL_PATH
            )
        );

        switch ($result[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
/*                $next_page = new lima_page_error($app, 404); */
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
/*                $next_page = new lima_page_error($app, 405); */
                break;
            case \FastRoute\Dispatcher::FOUND:
                $request->request->add($result[2]);

                $route = $this->route_collector->getRoute($result[1]);
                $response = $route->handle($request);
        }

        return $response;
    }

    /**
     * Initiate routing.
     *
     * @param   AbstractApp         $app            Instance of application.
     */
    public function handle(Request $request)
    {
        // determine last page
//        $last_page = $app->getLastPage();

        // routing
        $next_page = $this->routing($request); //, $last_page);

/*        $next_page = $this->rerouting($app, $last_page, $next_page);

        $next_page = $app->redirectPage($next_page);

        // process with page
        $app->setLastPage($next_page);

        $next_page->prepareMessages($app);
        $next_page->render($app);*/
    }
}
