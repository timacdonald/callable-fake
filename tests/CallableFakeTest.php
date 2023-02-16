<?php

declare(strict_types=1);

namespace Tests;

use Closure;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use TiMacDonald\CallableFake\CallableFake;

use function in_array;

/**
 * @small
 */
class CallableFakeTest extends TestCase
{
    public function testAssertCalledWithFalseBeforeBeingCalled(): void
    {
        $fake = new CallableFake();

        try {
            $fake->assertCalled(static function (string $arg) {
                return $arg === 'x';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was never called.', $e->getMessage());
        }
    }

    public function testAssertCalledWithTrueBeforeBeingCalled(): void
    {
        $fake = new CallableFake();

        try {
            $fake->assertCalled(static function (string $arg) {
                return $arg === 'a';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was never called.', $e->getMessage());
        }
    }

    public function testAssertCalledWithTrueAfterBeingCalled(): void
    {
        $fake = new callablefake();
        $fake('a');

        $fake->assertCalled(static function (string $arg) {
            return 'a' === $arg;
        });
    }

    public function testAssertCalledWithFalseAfterBeingCalled(): void
    {
        $fake = new CallableFake();
        $fake('a');

        try {
            $fake->assertCalled(static function (string $arg) {
                return $arg === 'x';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The expected callable was not called.', $e->getMessage());
        }
    }

    public function testAssertNotCalledWithTrueBeforeBeingCalled(): void
    {
        $fake = new CallableFake();

        $fake->assertNotCalled(static function (string $arg) {
            return $arg === 'a';
        });
    }

    public function testAssertNotCalledWithFalseBeforeBeingCalled(): void
    {
        $fake = new CallableFake();

        $fake->assertNotCalled(static function (string $arg) {
            return $arg === 'x';
        });
    }

    public function testAssertNotCalledWithTrueAfterBeingCalled(): void
    {
        $fake = new CallableFake();
        $fake('a');

        try {
            $fake->assertNotCalled(static function (string $arg) {
                return $arg === 'a';
            });
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('An unexpected callable was called.', $e->getMessage());
        }
    }

    public function testAssertNotCalledWithFalseAfterBeingCalled(): void
    {
        $fake = new CallableFake();
        $fake('a');

        $fake->assertNotCalled(static function (string $arg) {
            return $arg === 'x';
        });
    }

    public function testAssertCalledTimesWithTrueAndExpectedCount(): void
    {
        $fake = new CallableFake();
        $fake('a');

        $fake->assertCalledTimes(static function (string $arg) {
            return $arg === 'a';
        }, 1);
    }

    public function testAssertCalledTimesWithTrueAndUnexpectedCount(): void
    {
        $fake = new CallableFake();
        $fake('a');

        try {
            $fake->assertCalledTimes(static function (string $arg) {
                return $arg === 'a';
            }, 2);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The expected callable was called 1 times instead of the expected 2 times.', $e->getMessage());
        }
    }

    public function testAssertCalledTimesWithFalseAndExpectedCount(): void
    {
        $fake = new CallableFake();
        $fake('a');

        $fake->assertCalledTimes(static function (string $arg) {
            return $arg === 'x';
        }, 0);
    }

    public function testAssertCalledTimesWithFalseAndUnexpectedCount(): void
    {
        $fake = new CallableFake();
        $fake('a');

        try {
            $fake->assertCalledTimes(static function (string $arg) {
                return $arg === 'x';
            }, 2);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The expected callable was called 0 times instead of the expected 2 times.', $e->getMessage());
        }
    }

    public function testAssertTimesInvokedWithUnexpectedCount(): void
    {
        $fake = new CallableFake();
        $fake();

        try {
            $fake->assertTimesInvoked(2);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was invoked 1 times instead of the expected 2 times.', $e->getMessage());
        }
    }

    public function testAssertTimesInvokedWithExpectedCount(): void
    {
        $fake = new CallableFake();
        $fake();
        $fake();

        $fake->assertTimesInvoked(2);
    }

    public function testAssertInvokedWhenInvoked(): void
    {
        $fake = new CallableFake();
        $fake();

        $fake->assertInvoked();
    }

    public function testAssertInvokedWhenNotInvoked(): void
    {
        $fake = new CallableFake();

        try {
            $fake->assertInvoked();
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was not invoked.', $e->getMessage());
        }
    }

    public function testAssertNotInvokedWhenInvoked(): void
    {
        $fake = new CallableFake();
        $fake();

        try {
            $fake->assertNotInvoked();
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was invoked.', $e->getMessage());
        }
    }

    public function testAssertNotInvokedWhenNotInvoked(): void
    {
        $fake = new CallableFake();

        $fake->assertNotInvoked();
    }

    public function testCanUseAsAClosure(): void
    {
        $fake = new CallableFake();

        (static function (Closure $callback): void {
            $callback('a');
        })($fake->asClosure());

        $fake->assertCalled(static function (string $arg) {
            return $arg === 'a';
        });
    }

    public function testReturnValuesCanBeTruthy(): void
    {
        $fake = new CallableFake();
        $fake('a');

        $fake->assertCalled(static function () {
            return 1;
        });
    }

    public function testReturnValuesCanBeFalsy(): void
    {
        $fake = new CallableFake();

        $fake->assertNotCalled(static function () {
            return 0;
        });
    }

    public function testCanSpecifyInvocationReturnTypes(): void
    {
        $fake = CallableFake::withReturnResolver(static function (int $index): string {
            return [
                0 => 'a',
                1 => 'b',
            ][$index];
        });

        $this->assertSame('a', $fake(0));
        $this->assertSame('b', $fake(1));
    }

    public function testNullDefaultInvocationReturnType(): void
    {
        $fake = new CallableFake();

        $this->assertNull($fake(0));
    }

    public function testWasInvokedWhenInvoked(): void
    {
        $callable = new CallableFake();

        $this->assertFalse($callable->wasInvoked());
        $this->assertTrue($callable->wasNotInvoked());

        $callable();

        $this->assertTrue($callable->wasInvoked());
        $this->assertFalse($callable->wasNotInvoked());
    }

    public function testCanGetInvocationArguments(): void
    {
        $callable = new CallableFake();
        $callable('a', 'b');
        $callable('c', 'd');
        $callable('x', 'y');

        $invocationArguments = $callable->called(static function (string $arg) {
            return in_array($arg, ['a', 'c'], true);
        });

        $this->assertSame([['a', 'b'], ['c', 'd']], $invocationArguments);
    }

    public function testAssertCalledIndexWithExpectedSingleIndex(): void
    {
        $callable = new CallableFake();
        $callable('b');
        $callable('a');
        $callable('b');

        $callable->assertCalledIndex(static function (string $arg): bool {
            return $arg === 'a';
        }, 1);
    }

    public function testAssertCalledIndexWithExpectedMutlipleIndex(): void
    {
        $callable = new CallableFake();
        $callable('b');
        $callable('a');
        $callable('b');

        $callable->assertCalledIndex(static function (string $arg): bool {
            return $arg === 'b';
        }, [0, 2]);
    }

    public function testAssertCalledIndexWithUnexpectedSingleIndex(): void
    {
        $callable = new CallableFake();
        $callable('b');
        $callable('a');
        $callable('b');

        try {
            $callable->assertCalledIndex(static function (string $arg): bool {
                return $arg === 'b';
            }, 1);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was not called in the expected order. Found at index: 0, 2. Expected to be found at index: 1', $e->getMessage());
        }
    }

    public function testAssertCalledIndexWithUnexpectedMultipleIndex(): void
    {
        $callable = new CallableFake();
        $callable('b');
        $callable('a');
        $callable('b');

        try {
            $callable->assertCalledIndex(static function (string $arg): bool {
                return $arg === 'b';
            }, [1, 3]);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was not called in the expected order. Found at index: 0, 2. Expected to be found at index: 1, 3', $e->getMessage());
        }
    }

    public function testAssertCalledIndexWhenNeverCalledWithExpectedCallable(): void
    {
        $callable = new CallableFake();

        try {
            $callable->assertCalledIndex(static function (string $arg): bool {
                return $arg === 'b';
            }, 1);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringStartsWith('The callable was never called', $e->getMessage());
        }
    }
}
