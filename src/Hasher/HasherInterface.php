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

namespace Esi\ConsistentHash\Hasher;

/**
 * Hashes given values into a sortable fixed size address space.
 */
interface HasherInterface
{
    /**
     * Hashes the given string into a 32bit address space.
     *
     * The data must have 0xFFFFFFFF possible values, and be sortable by
     * PHP sort functions using SORT_REGULAR.
     *
     * @return int A sortable format with 0xFFFFFFFF possible values
     */
    public function hash(string $string): int;
}
