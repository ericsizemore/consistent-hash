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

namespace Esi\ConsistentHash;

use Esi\ConsistentHash\Exception\TargetException;
use Esi\ConsistentHash\Hasher\Crc32Hasher;
use Esi\ConsistentHash\Hasher\HasherInterface;

use function array_key_first;
use function array_keys;
use function array_unique;
use function array_values;
use function ksort;
use function round;

use const SORT_REGULAR;

/**
 * A simple consistent hashing implementation with pluggable hash algorithms.
 */
class ConsistentHash
{
    /**
     * The hash algorithm, encapsulated in a HasherInterface implementation.
     */
    private HasherInterface $hasher;

    /**
     * Internal counter for current number of positions.
     */
    private int $positionCount = 0;

    /**
     * Internal map of positions (hash outputs) to targets.
     *
     * @var array<int, string>
     */
    private array $positionToTarget = [];

    /**
     * Whether the internal map of positions to targets is already sorted.
     */
    private bool $positionToTargetSorted = false;

    /**
     * The number of positions to hash each target to.
     */
    private int $replicas = 64;

    /** @var list<int> */
    private array $sortedPositions = [];

    /**
     * Internal counter for current number of targets.
     */
    private int $targetCount = 0;

    /**
     * Internal map of targets to lists of positions that target is hashed to.
     *
     * @var array<string, list<int>>
     */
    private array $targetToPositions = [];

    /**
     * @param null|HasherInterface $hasher
     * @param null|int             $replicas Amount of positions to hash each target to.
     */
    public function __construct(null|HasherInterface $hasher = null, null|int $replicas = null)
    {
        $this->hasher = $hasher ?? new Crc32Hasher();

        if ($replicas === null) {
            return;
        }

        $this->replicas = $replicas;
    }

    /**
     * @throws TargetException if the $target already exists.
     */
    public function addTarget(string $target, float $weight = 1): void
    {
        if (\array_key_exists($target, $this->targetToPositions)) {
            throw TargetException::alreadyExists($target);
        }

        $this->targetToPositions[$target] = [];

        // Hash the target into multiple positions.
        $partitionCount = round($this->replicas * $weight);

        for ($i = 0; $i < $partitionCount; ++$i) {
            $position                           = $this->hasher->hash($target . $i);
            $this->positionToTarget[$position]  = $target; // Lookup.
            $this->targetToPositions[$target][] = $position; // Target removal.
        }

        $this->positionToTargetSorted = false;
        ++$this->targetCount;
    }

    /**
     * Add a list of targets.
     *
     * @param array<string> $targets
     *
     * @throws TargetException if any of $targets already exists.
     */
    public function addTargets(array $targets, float $weight = 1): void
    {
        foreach ($targets as $target) {
            $this->addTarget($target, $weight);
        }
    }

    /**
     * A list of all potential targets.
     *
     * @return list<string>
     */
    public function getAllTargets(): array
    {
        return array_keys($this->targetToPositions);
    }

    /**
     * Looks up the target for the given resource.
     *
     * @throws TargetException when no targets are defined.
     */
    public function lookup(string $resource): string
    {
        $targets = $this->lookupList($resource, 1);

        if (\count($targets) === 0) {
            throw TargetException::noneExist();
        }

        return $targets[0];
    }

    /**
     * Get a list of targets for the resource, in order of precedence.
     * Up to $requestedCount targets are returned, less if there are fewer in total.
     *
     * @param positive-int $requestedCount The length of the list to return
     *
     * @return list<string> List of targets
     */
    public function lookupList(string $resource, int $requestedCount): array
    {
        // handle no targets
        if ($this->positionToTarget === []) {
            return [];
        }

        // optimize single target
        if ($this->targetCount === 1) {
            return [$this->positionToTarget[array_key_first($this->positionToTarget)]];
        }

        $this->sortPositionTargets();
        $offset = self::bisectLeft(
            $this->sortedPositions,
            $this->hasher->hash($resource),
            $this->positionCount,
        );

        $resCount = 1;
        $results  = [];

        do {
            $offset %= $this->positionCount;
            $results[] = $this->positionToTarget[$this->sortedPositions[$offset]];
            $offset++;
        } while ($resCount++ < $requestedCount);

        return array_values(array_unique($results));
    }

    /**
     * @throws TargetException when target does not exist.
     */
    public function removeTarget(string $target): void
    {
        if (!isset($this->targetToPositions[$target])) {
            throw TargetException::doesNotExist($target);
        }

        foreach ($this->targetToPositions[$target] as $position) {
            unset($this->positionToTarget[$position]);
        }

        unset($this->targetToPositions[$target]);

        $this->positionToTargetSorted = false;
        --$this->targetCount;
    }

    /**
     * Locate the insertion point for $value in $sortedArray to maintain sorted order.
     *
     * @param list<int> $sortedArray
     */
    public static function bisectLeft(array $sortedArray, int $value, int $arraySize): int
    {
        $low  = 0;
        $high = $arraySize - 1;

        if ($value < $sortedArray[$low]) {
            return 0;
        }

        if ($value >= $sortedArray[$high]) {
            return $arraySize; // Out of bounds.
        }

        while ($low < $high) {
            $middle = (int) (($low + $high) / 2);

            if ($sortedArray[$middle] < $value) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        return $high;
    }

    /**
     * Sorts the internal mapping (positions to targets) by position.
     */
    private function sortPositionTargets(): void
    {
        // Sort by key (position), if not already.
        if ($this->positionToTargetSorted) {
            return;
        }

        ksort($this->positionToTarget, SORT_REGULAR);

        $this->positionToTargetSorted = true;
        $this->sortedPositions        = array_keys($this->positionToTarget);
        $this->positionCount          = \count($this->sortedPositions);
    }
}
