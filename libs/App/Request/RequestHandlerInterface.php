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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface RequestHandlerInterface
 *
 * @copyright   copyright (c) 2020-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
interface RequestHandlerInterface
{
    /**
     * Handle request.
     *
     * @param   Request     $request
     * @return  Response
     */
    public function handle(Request $request): Response;
}
