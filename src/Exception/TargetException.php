<?php

declare(strict_types=1);

/**
 * This file is part of Esi\ConsistentHash.
 *
 * (c) Eric Sizemore <admin@secondversion.com>
 * (c) Paul Annesley <paul@annesley.cc>
 *
 * This source file is subject to the MIT license. For the full copyright and
 * license information, please view the LICENSE file that was distributed with
 * this source code.
 */

namespace Esi\ConsistentHash\Exception;

/**
 * An exception thrown by ConsistentHash.
 */
class TargetException extends \RuntimeException
{
    protected function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function alreadyExists(string $target): self
    {
        return new self(\sprintf("Target '%s' already exists.", $target));
    }

    public static function doesNotExist(string $target): self
    {
        return new self(\sprintf("Target '%s' does not exist.", $target));
    }

    public static function noneExist(): self
    {
        return new self('No targets exist');
    }
}
