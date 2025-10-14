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

use InvalidArgumentException as BaseInvalidArgumentException;

final class InvalidArgumentException extends BaseInvalidArgumentException
{
    protected function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function invalidReplicaAmount(int $replicas): self
    {
        return new self(\sprintf("\$replicas expects a value greater than 0, '%d' provided.", $replicas));
    }

    public static function invalidWeight(float $weight): self
    {
        return new self(\sprintf("\$weight expects a value greater than 0.0, '%.2f' provided.", $weight));
    }
}
