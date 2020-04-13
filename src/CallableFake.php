<?php

declare(strict_types=1);

namespace TiMacDonald\CallableFake;

use Closure;
use PHPUnit\Framework\Assert;

class CallableFake
{
    /**
     * @var array
     */
    private $invocations = [];

    /**
     * @var callable
     */
    private $returnResolver;

    public function __construct(callable $callback = null)
    {
        $this->returnResolver = $callback ?? static function (): void {
        };
    }

    public static function withReturnResolver(callable $callback): self
    {
        return new self($callback);
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        $args = \func_get_args();

        $this->invocations[] = $args;

        return \call_user_func_array($this->returnResolver, $args);
    }

    public function asClosure(): Closure
    {
        return Closure::fromCallable($this);
    }

    public function assertCalled(callable $callback): self
    {
        Assert::assertNotCount(0, $this->invocations, 'The callable was never called.');

        Assert::assertNotCount(0, $this->called($callback), 'The expected callable was not called.');

        return $this;
    }

    public function assertNotCalled(callable $callback): self
    {
        Assert::assertCount(0, $this->called($callback), 'An unexpected callable was called.');

        return $this;
    }

    public function assertCalledTimes(callable $callback, int $times): self
    {
        $timesCalled = \count($this->called($callback));

        Assert::assertSame($times, $timesCalled, "The expected callable was called {$timesCalled} times instead of the expected {$times} times.");

        return $this;
    }

    /**
     * @param array|int $indexes
     */
    public function assertCalledIndex(callable $callback, $indexes): self
    {
        $this->assertCalled($callback);

        $expectedIndexes = (array) $indexes;

        $actualIndexes = \array_keys($this->called($callback));

        $matches = \array_intersect($expectedIndexes, $actualIndexes);

        Assert::assertSame(\count($matches), \count($expectedIndexes), 'The callable was not called in the expected order. Found at index: '.\implode(', ', $actualIndexes).'. Expected to be found at index: '.\implode(', ', $expectedIndexes));

        return $this;
    }

    public function assertTimesInvoked(int $count): self
    {
        $timesInvoked = \count($this->invocations);

        Assert::assertSame($count, $timesInvoked, "The callable was invoked {$timesInvoked} times instead of the expected {$count} times.");

        return $this;
    }

    public function assertInvoked(): self
    {
        Assert::assertTrue($this->wasInvoked(), 'The callable was not invoked.');

        return $this;
    }

    public function assertNotInvoked(): self
    {
        Assert::assertTrue($this->wasNotInvoked(), 'The callable was invoked.');

        return $this;
    }

    public function wasInvoked(): bool
    {
        return \count($this->invocations) > 0;
    }

    public function wasNotInvoked(): bool
    {
        return ! $this->wasInvoked();
    }

    public function called(callable $callback): array
    {
        return \array_filter($this->invocations, static function (array $arguments) use ($callback): bool {
            return (bool) $callback(...$arguments);
        });
    }
}
