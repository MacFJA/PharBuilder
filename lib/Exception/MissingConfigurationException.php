<?php
/* Copyright (C) 2019 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Exception;

use Throwable;

/** @codeCoverageIgnore */
class MissingConfigurationException extends \RuntimeException
{
    public function __construct(string $configurationName, int $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            '%s configuration is missing. This configuration is mandatory.',
            $configurationName
        );
        parent::__construct($message, $code, $previous);
    }
}
