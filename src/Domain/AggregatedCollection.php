<?php

declare(strict_types=1);

namespace MetricsAPI\Domain;

/**
 * Class AggregatedCollection
 * @package MetricsAPI\Domain
 */
class AggregatedCollection implements \JsonSerializable, \ArrayAccess, \Countable
{
    private $data;

    public function __construct($data)
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        $this->data = $data;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function groupBy(callable $callback): self
    {
        $grouped = [];

        foreach ($this->data as $entry) {
            $key = $callback($entry);

            $grouped[$key][] = $entry;
        }

        $this->data = $grouped;

        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function map(callable $callback): self
    {
        $this->data = array_map(function ($entry) use ($callback) {
            if (is_array($entry)) {
                $entry = new AggregatedCollection($entry);
            }
            return $callback($entry);
        }, $this->data);

        return $this;
    }

    /**
     * @return $this
     */
    public function unique(): self
    {
        $this->data = array_unique($this->data);

        return $this;
    }

    /**
     * @param AggregatedCollection $statsToMerge
     * @return $this
     */
    public function updateAvg(AggregatedCollection $statsToMerge): self
    {
        $this->merge($statsToMerge, function ($currentValue, $valueToMerge) {
            if (!$currentValue instanceof RunningAvg || !$valueToMerge instanceof RunningAvg) {
                throw new \InvalidArgumentException('Trying to update a non-RunningAvg value');
            }
            $currentValue->update(
                $valueToMerge->getSum(),
                $valueToMerge->getTotal()
            );

            return $currentValue;
        });

        return $this;
    }

    /**
     * @param AggregatedCollection $statsToMerge
     * @return $this
     */
    public function updateMax(AggregatedCollection $statsToMerge): self
    {
       $this->merge($statsToMerge, function ($currentValue, $valueToMerge) {
            return max($currentValue, $valueToMerge);
        });

        return $this;
    }

    /**
     * @param AggregatedCollection $statsToMerge
     * @return $this
     */
    public function updateTotal(AggregatedCollection $statsToMerge): self
    {
        $this->merge($statsToMerge, function ($currentValue, $valueToMerge) {
            return $currentValue + $valueToMerge;
        });

        return $this;
    }

    /**
     * @param AggregatedCollection $statsToMerge
     * @param callable $callback
     * @return $this
     */
    public function merge(AggregatedCollection $statsToMerge, callable $callback): self
    {
        foreach ($statsToMerge->getValues() as $updateKey => $updateValue) {
            if (isset($this->data[$updateKey])) {
                $this->data[$updateKey] = $callback($this->data[$updateKey], $updateValue);
            } else {
                $this->data[$updateKey] = $updateValue;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCalculatedValues(): array
    {
        return array_map(function ($entry) {
            return $entry instanceof CalculatedValue ? $entry->calculate() : $entry;
        }, $this->data);
    }

    /**
     * @return RunningAvg
     */
    public function runningAvg(): RunningAvg
    {
        return new RunningAvg(
            array_sum($this->data),
            count($this->data)
        );
    }

    /**
     * @return mixed
     */
    public function max()
    {
        return max($this->data);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->data;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize(): array
    {
        return $this->getValues();
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @param mixed $key
     * @return mixed|null
     */
    public function offsetGet($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}