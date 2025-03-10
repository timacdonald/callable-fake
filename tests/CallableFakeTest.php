<?php

namespace Tests;

use Closure;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use TiMacDonald\CallableFake\CallableFake;

class CallableFakeTest extends TestCase
{
    public function test_assert_called_with_false_before_being_called(): void
    {
        $fake = new CallableFake;

        try {
            $fake->assertCalled(function (string $arg) {
                return $arg === 'x';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was never called.', $e->getMessage());
        }
    }

    public function test_assert_called_with_true_before_being_called(): void
    {
        $fake = new CallableFake;

        try {
            $fake->assertCalled(function (string $arg) {
                return $arg === 'a';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was never called.', $e->getMessage());
        }
    }

    public function test_assert_called_with_true_after_being_called(): void
    {
        $fake = new callablefake;
        $fake('a');

        $fake->assertCalled(function (string $arg) {
            return $arg === 'a';
        });
    }

    public function test_assert_called_with_false_after_being_called(): void
    {
        $fake = new CallableFake;
        $fake('a');

        try {
            $fake->assertCalled(function (string $arg) {
                return $arg === 'x';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The expected callable was not called.', $e->getMessage());
        }
    }

    public function test_assert_not_called_with_true_before_being_called(): void
    {
        $fake = new CallableFake;

        $fake->assertNotCalled(function (string $arg) {
            return $arg === 'a';
        });
    }

    public function test_assert_not_called_with_false_before_being_called(): void
    {
        $fake = new CallableFake;

        $fake->assertNotCalled(function (string $arg) {
            return $arg === 'x';
        });
    }

    public function test_assert_not_called_with_true_after_being_called(): void
    {
        $fake = new CallableFake;
        $fake('a');

        try {
            $fake->assertNotCalled(function (string $arg) {
                return $arg === 'a';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('An unexpected callable was called.', $e->getMessage());
        }
    }

    public function test_assert_not_called_with_false_after_being_called(): void
    {
        $fake = new CallableFake;
        $fake('a');

        $fake->assertNotCalled(function (string $arg) {
            return $arg === 'x';
        });
    }

    public function test_assert_called_times_with_true_and_expected_count(): void
    {
        $fake = new CallableFake;
        $fake('a');

        $fake->assertCalledTimes(function (string $arg) {
            return $arg === 'a';
        }, 1);
    }

    public function test_assert_called_times_with_true_and_unexpected_count(): void
    {
        $fake = new CallableFake;
        $fake('a');

        try {
            $fake->assertCalledTimes(function (string $arg) {
                return $arg === 'a';
            }, 2);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The expected callable was called 1 times instead of the expected 2 times.', $e->getMessage());
        }
    }

    public function test_assert_called_times_with_false_and_expected_count(): void
    {
        $fake = new CallableFake;
        $fake('a');

        $fake->assertCalledTimes(function (string $arg) {
            return $arg === 'x';
        }, 0);
    }

    public function test_assert_called_times_with_false_and_unexpected_count(): void
    {
        $fake = new CallableFake;
        $fake('a');

        try {
            $fake->assertCalledTimes(function (string $arg) {
                return $arg === 'x';
            }, 2);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The expected callable was called 0 times instead of the expected 2 times.', $e->getMessage());
        }
    }

    public function test_assert_times_invoked_with_unexpected_count(): void
    {
        $fake = new CallableFake;
        $fake();

        try {
            $fake->assertTimesInvoked(2);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was invoked 1 times instead of the expected 2 times.', $e->getMessage());
        }
    }

    public function test_assert_times_invoked_with_expected_count(): void
    {
        $fake = new CallableFake;
        $fake();
        $fake();

        $fake->assertTimesInvoked(2);
    }

    public function test_assert_invoked_when_invoked(): void
    {
        $fake = new CallableFake;
        $fake();

        $fake->assertInvoked();
    }

    public function test_assert_invoked_when_not_invoked(): void
    {
        $fake = new CallableFake;

        try {
            $fake->assertInvoked();
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was not invoked.', $e->getMessage());
        }
    }

    public function test_assert_not_invoked_when_invoked(): void
    {
        $fake = new CallableFake;
        $fake();

        try {
            $fake->assertNotInvoked();
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was invoked.', $e->getMessage());
        }
    }

    public function test_assert_not_invoked_when_not_invoked(): void
    {
        $fake = new CallableFake;

        $fake->assertNotInvoked();
    }

    public function test_can_use_as_a_closure(): void
    {
        $fake = new CallableFake;

        (function (Closure $callback): void {
            $callback('a');
        })($fake->asClosure());

        $fake->assertCalled(function (string $arg) {
            return $arg === 'a';
        });
    }

    public function test_return_values_can_be_truthy(): void
    {
        $fake = new CallableFake;
        $fake('a');

        $fake->assertCalled(function () {
            return 1;
        });
    }

    public function test_return_values_can_be_falsy(): void
    {
        $fake = new CallableFake;

        $fake->assertNotCalled(function () {
            return 0;
        });
    }

    public function test_can_specify_invocation_return_types(): void
    {
        $fake = CallableFake::withReturnResolver(function (int $index): string {
            return [
                0 => 'a',
                1 => 'b',
            ][$index];
        });

        $this->assertSame('a', $fake(0));
        $this->assertSame('b', $fake(1));
    }

    public function test_null_default_invocation_return_type(): void
    {
        $fake = new CallableFake;

        $this->assertNull($fake(0));
    }

    public function test_was_invoked_when_invoked(): void
    {
        $callable = new CallableFake;

        $this->assertFalse($callable->wasInvoked());
        $this->assertTrue($callable->wasNotInvoked());

        $callable();

        $this->assertTrue($callable->wasInvoked());
        $this->assertFalse($callable->wasNotInvoked());
    }

    public function test_can_get_invocation_arguments(): void
    {
        $callable = new CallableFake;
        $callable('a', 'b');
        $callable('c', 'd');
        $callable('x', 'y');

        $invocationArguments = $callable->called(function (string $arg) {
            return in_array($arg, ['a', 'c'], true);
        });

        $this->assertSame([['a', 'b'], ['c', 'd']], $invocationArguments);
    }

    public function test_assert_called_index_with_expected_single_index(): void
    {
        $callable = new CallableFake;
        $callable('b');
        $callable('a');
        $callable('b');

        $callable->assertCalledIndex(function (string $arg): bool {
            return $arg === 'a';
        }, 1);
    }

    public function test_assert_called_index_with_expected_mutliple_index(): void
    {
        $callable = new CallableFake;
        $callable('b');
        $callable('a');
        $callable('b');

        $callable->assertCalledIndex(function (string $arg): bool {
            return $arg === 'b';
        }, [0, 2]);
    }

    public function test_assert_called_index_with_unexpected_single_index(): void
    {
        $callable = new CallableFake;
        $callable('b');
        $callable('a');
        $callable('b');

        try {
            $callable->assertCalledIndex(function (string $arg): bool {
                return $arg === 'b';
            }, 1);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was not called in the expected order. Found at index: 0, 2. Expected to be found at index: 1', $e->getMessage());
        }
    }

    public function test_assert_called_index_with_unexpected_multiple_index(): void
    {
        $callable = new CallableFake;
        $callable('b');
        $callable('a');
        $callable('b');

        try {
            $callable->assertCalledIndex(function (string $arg): bool {
                return $arg === 'b';
            }, [1, 3]);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was not called in the expected order. Found at index: 0, 2. Expected to be found at index: 1, 3', $e->getMessage());
        }
    }

    public function test_assert_called_index_when_never_called_with_expected_callable(): void
    {
        $callable = new CallableFake;

        try {
            $callable->assertCalledIndex(function (string $arg): bool {
                return $arg === 'b';
            }, 1);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was never called', $e->getMessage());
        }
    }
}
