<?php

declare(strict_types=1);

namespace Tests;

use Closure;
use PHPUnit\Framework\TestCase;
use TiMacDonald\CallableFake\CallableFake;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Constraint\ExceptionMessage;

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
            $this->assertThat($e, new ExceptionMessage('The callable was never called.'));
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
            $this->assertThat($e, new ExceptionMessage('The callable was never called.'));
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
            $this->assertThat($e, new ExceptionMessage('The expected callable was not called.'));
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
            $this->assertThat($e, new ExceptionMessage('An unexpected callable was called.'));
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
            $this->assertThat($e, new ExceptionMessage('The expected callable was called 1 times instead of the expected 2 times.'));
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
            $this->assertThat($e, new ExceptionMessage('The expected callable was called 0 times instead of the expected 2 times.'));
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
            $this->assertThat($e, new ExceptionMessage('The callable was invoked 1 times instead of the expected 2 times.'));
        }
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
            $this->assertThat($e, new ExceptionMessage('The callable was not invoked.'));
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
            $this->assertThat($e, new ExceptionMessage('The callable was invoked.'));
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
        $fake = new CallableFake(static function (int $index): string {
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
}
