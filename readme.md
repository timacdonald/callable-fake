<p align="center"><img src="/art/header.png" alt="Callable Fake: a PHP package by Tim MacDonald"></p>

# Callable / Closure testing fake

![CI](https://github.com/timacdonald/callable-fake/workflows/CI/badge.svg) [![codecov](https://codecov.io/gh/timacdonald/callable-fake/branch/master/graph/badge.svg)](https://codecov.io/gh/timacdonald/callable-fake) [![Mutation testing](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Ftimacdonald%2Fcallable-fake%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/timacdonald/callable-fake/master) ![Type coverage](https://shepherd.dev/github/timacdonald/callable-fake/coverage.svg)

If you have an interface who's public API allows a developer to pass a Closure / callable, but causes no internal or external side-effects, as these are left up to the developer using the interface, this package may assist in testing. This class adds some named assertions which gives you an API that is very much inspired by Laravel's service fakes. It may be a little more verbose, but it changes the language of the tests to better reflect what is going on.

It also makes it easy to assert the order of invocations, and how many times a callable has been invoked.

## Version support

- **PHP**: 7.1, 7.2, 7.3, 7.4
- **PHPUnit**: 6.0, 7.0, 8.0, 9.0

## Installation

You can install using [composer](https://getcomposer.org/) from [Packagist](https://packagist.org/packages/timacdonald/callable-fake).

```
$ composer require timacdonald/callable-fake --dev
```

## Basic usage

This packge requires you to be testing a pretty specfic type of API / interaction to be useful. Imagine you are developing a package that ships with the following interface...

```php
<?php

interface DependencyRepository
{
    public function each(callable $callback): void;
}
```

This interface accepts a callback, and under the hood loops through all "dependecies" and passes each one to the callback for the developer to work with.

### Before

Let's see what the a test for this method might look like...

```php
<?php

public function testEachLoopsOverAllDependencies(): void
{
    // arrange
    $received = [];
    $expected = factory(Dependency::class)->times(2)->create();
    $repo = $this->app[DependencyRepository::class];

    // act
    $repo->each(function (Dependency $dependency) use (&$received): void {
        $received[] = $dependency;
    });

    // assert
    $this->assertCount(2, $received);
    $this->assertTrue($expected[0]->is($received[0]));
    $this->assertTrue($expected[1]->is($received[1]));
}
```

### After

```php
<?php

public function testEachLoopsOverAllDependencies(): void
{
    // arrange
    $callable = new CallableFake();
    $expected = factory(Dependency::class)->times(2)->create();
    $repo = $this->app[DependencyRepository::class];

    // act
    $repo->each($callable);

    // assert
    $callable->assertTimesInvoked(2);
    $callable->assertCalled(function (Depedency $dependency) use ($expected): bool {
        return $dependency->is($expected[0]);
    });
    $callable->assertCalled(function (Dependency $dependency) use ($expected): bool {
        return $dependency->is($expected[1]);
    });
}
```

## Available assertions

All assertions are chainable.

### assertCalled(callable $callback): self

```php
<?php

$callable->assertCalled(function (Dependency $dependency): bool {
    return Str::startsWith($dependency->name, 'spatie/');
});
```

### assertNotCalled(callable $callback): self

```php
<?php

$callable->assertNotCalled(function (Dependency $dependency): bool {
    return Str::startsWith($dependency->name, 'timacdonald/');
});
```

### assertCalledIndex(callable $callback, int|array $index): self

Ensure the callable was called in an explicit order, i.e. it was called as the 0th and 5th invocation.
```php
<?php

$callable->assertCalledIndex(function (Dependency $dependency): bool {
    return Str::startsWith($dependency, 'spatie/');
}, [0, 5]);
```

### assertCalledTimes(callable $callback, int $times): self

```php
<?php

$callable->assertCalledTimes(function (Dependency $dependency): bool {
    return Str::startsWith($dependency, 'spatie/');
}, 999);
```

### assertTimesInvoked(int $times): self

```php
<?php

$callable->assertTimesInvoked(2);
```

### assertInvoked(): self

```php
<?php

$callable->assertInvoked();
```

### assertNotInvoked(): self

```php
<?php

$callable->assertNotInvoked();
```

## Non-assertion API

### asClosure(): Closure

If the method is type-hinted with `\Closure` instead of callable, you can use this method to transform the callable to an instance of `\Closure`.

```php
<?php

$callable = new CallableFake;

$thing->closureTypeHintedMethod($callable->asClosure());

$callable->assertInvoked();
```

### wasInvoked(): bool

```php
<?php

if ($callable->wasInvoked()) {
    //
}
```

### wasNotInvoked(): bool

```php
<?php

if ($callable->wasNotInvoked()) {
    //
}
```

### called(callable $callback): array

```php
<?php

$invocationArguments = $callable->called(function (Dependency $dependency): bool {
    return Str::startsWith($dependency->name, 'spatie/')
});
```

## Specifying return values

If you need to specify return values, this _could_ be an indicator that this is not the right tool for the job. But there are some cases where return values determine control flow, so it can be handy, in which case you can pass a "return resolver" to the named constructor `withReturnResolver`.

```php
<?php

$callable = CallableFake::withReturnResolver(function (Dependency $dependency): bool {
    if ($dependency->version === '*') {
        return '🤠';
    }

    return '😀';
});

// You would not generally be calling this yourself, this is simply to demonstate
// what will happen under the hood...

$emoji = $callable(new Dependecy(['version' => '*']));

// $emoji === '🤠';
```

## Developing and testing

Although this package requires `"PHP": "^7.1"`, in order to install and develop locally, you should be running a recent version of PHP to ensure compatibility with the development tools.

## Credits

- [Tim MacDonald](https://github.com/timacdonald)
- [All Contributors](../../contributors)

And a special (vegi) thanks to [Caneco](https://twitter.com/caneco) for the logo ✨

## Thanksware

You are free to use this package, but I ask that you reach out to someone (not me) who has previously, or is currently, maintaining or contributing to an open source library you are using in your project and thank them for their work. Consider your entire tech stack: packages, frameworks, languages, databases, operating systems, frontend, backend, etc.
