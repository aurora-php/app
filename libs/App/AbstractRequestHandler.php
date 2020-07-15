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
 * Abstract request handler class.
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class AbstractRequestHandler
{
    /**
     * @var AbstractApp
     */
    protected AbstractApp $app;

    /**
     * @var AbstractRequestHandler[]
     */
    protected array $next = [];

    /**
     * Constructor.
     *
     * @param   AbstractApp         $app        Application instance.
     */
    public function __construct(AbstractApp $app)
    {
        $this->app = $app;
    }

    /**
     * Prepare page.
     *
     * @param   AbstractRequestHandler          $last_page  Previous page instance.
     * @param   string                          $action
     * @return  AbstractRequestHandler|null
     */
    abstract public function prepare(AbstractRequestHandler $last_page, string $action): ?AbstractRequestHandler;

    /**
     * Get response of page.
     *
     * @return  Response
     */
    abstract public function getResponse(Response $response): Response;
}
