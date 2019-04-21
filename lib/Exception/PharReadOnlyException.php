<?php
/* Copyright (C) 2019 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Exception;

use Throwable;

/** @codeCoverageIgnore */
class PharReadOnlyException extends \RuntimeException
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        $message = 'The creation of the PHAR disabled by the php.ini setting "phar.readonly".';

        parent::__construct($message, $code, $previous);
    }
}
