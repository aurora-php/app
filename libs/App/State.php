<?php

/*
 * This file is part of the 'octris/app' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\App;

use Base64Url\Base64Url;
use Octris\PropertyCollection;

/**
 * The state class is used to transfer page/action specific data between two
 * or more requests. The state is essencial for transfering for example the
 * last visited page to determine the next valid page. It can also be used
 * to transfer additional abitrary data for example search query parameters,
 * parameters that should not be visible and or not be modified by a user
 * between two requests. The state helps to bring stateful requests to a web
 * application, too.
 *
 * @copyright   copyright (c) 2011-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class State
{
    /**
     * Algorithm to use for checksum.
     *
     * @var string
     */
    protected static string $algo = 'sha256';

    /**
     * Secret token.
     *
     * @var string
     */
    protected static string $secret = '';

    /**
     * Constructor.
     *
     * @param   array       $data           Optional data to initialize state with.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Set hash for checksum.
     *
     * @param   string      $hash
     */
    public static function setAlgo(string $algo): void
    {
        self::$algo = $algo;
    }

    /**
     * Set secret token.
     *
     * @param   string      $secret
     */
    public static function setSecret(string $secret): void
    {
        self::$secret = $secret;
    }

    /**
     * Freeze state object.
     *
     * @param   array           $data               Optional data to inject into state before freezing. The original
     *                                              state will not be altered.
     * @return  string                              Frozen state.
     */
    public function freeze(array $data = []): string
    {
        $data = array_merge($this->data, $data);

        $frozen = gzcompress(serialize($data));
        $hash = hash_hmac(self::$algo, $frozen, self::$secret, true);
        $return = base64Url::encode($hash . '|' . $frozen);

        return $return;
    }

    /**
     * Validate frozen state object.
     *
     * @param   string          $state              Frozen state to validate.
     * @param   string          $decoded            Returns array with checksum and compressed state, ready to thaw.
     * @return  bool                                Returns true if state is valid, otherwise returns false.
     */
    public static function validate(string $state, array &$decoded = null): bool
    {
        $tmp = Base64Url::decode($state);

        if (($pos = strpos($tmp, '|')) === false) {
            $should = substr($tmp, 0, $pos);
            $frozen = substr($tmp, $pos + 1);

            unset($tmp);

            $is = hash_hmac(self::$algo, $frozen, self::$secret, true);

            $decoded = array(
                'should_hash' => $should,
                'is_hash' => $is,
                'state' => $frozen
            );

            $is_valid = ($should === $is);
        }

        return $is_valid;
    }

    /**
     * Thaw frozen state object.
     *
     * @param   string          $frozen         Frozen state.
     * @return  State                           Instance of state object.
     */
    public static function thaw(string $frozen): State
    {
        if (!self::validate($frozen, $decoded)) {
            // hash did not match
            throw new Exception\StateHashMismatchException(sprintf(
                'Hash does not match - should: %s / is: %s. State: ',
                $decoded['should_hash'],
                $decoded['is_hash'],
                $decoded['state']
            ));
        } else {
            return new static(unserialize(gzuncompress($frozen['state'])));
        }
    }
}
