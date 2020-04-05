# Callable / Closure testing fake

![CI](https://github.com/timacdonald/callable-fake/workflows/CI/badge.svg) [![Latest Stable Version](https://poser.pugx.org/timacdonald/callable-fake/v/stable)](https://packagist.org/packages/timacdonald/callable-fake) [![Total Downloads](https://poser.pugx.org/timacdonald/callable-fake/downloads)](https://packagist.org/packages/timacdonald/callable-fake) [![License](https://poser.pugx.org/timacdonald/callable-fake/license)](https://packagist.org/packages/timacdonald/callable-fake)

If you have an interface who's public API allows a developer to pass a Closure / callable, but causes no internal or external side-effects, as these are left up to the developer using the interface, testing it can create some duplicated boilerplate. This class wraps up that boilerplate and adds some named assertions to better express what the test is doing.

## Installation

You can install using [composer](https://getcomposer.org/) from [Packagist](https://packagist.org/packages/timacdonald/callable-fake).

```
$ composer require timacdonald/callable-fake --dev
```

## Basic usage

This packge requires you to be testing a pretty specfic type of API / interaction to be useful. Imagine you are developing a package that ships with the following interface...

```php
interface DependencyRepository
{
    public function each(callable $callback): void;
}
```

This interface accepts a callback, and under the hood loops through all "dependecies" and passes each one to the callback for the developer to work with.

### Before

Let's see what the a test for this method might look like...

```php
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

There is a bit of boilerplate in this test that can get repetitive when testing this kind of closure / callable API. This package aims to eliminate that boilerplate and also add some sytactic sugar over the assertions.

### After

```php
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
$callable->assertCalled(function (Dependency $dependency): bool {
    return Str::startsWith($dependency->name, 'spatie/');
});
```

### assertNotCalled(callable $callback): self

```php
$callable->assertNotCalled(function (Dependency $dependency): bool {
    return Str::startsWith($dependency->name, 'timacdonald/');
});
```

### assertCalledTimes(callable $callback, int $times): self

```php
$callable->assertCalledTimes(function ($dependency): bool {
    return Str::startsWith($dependency, 'spatie/');
}, 999);
```

### assertTimesInvoked(int $times): self

```php
$callable->assertTimesInvoked(2);
```

### assertInvoked(): self

```php
$callable->assertInvoked();
```

### assertNotInvoked(): self

```php
$callable->assertNotInvoked();
```

## Non-assertion API

### asClosure(): Closure

If the method is type-hinted with `\Closure` instead of callable, you can use this method to transform the callable to an instance of `\Closure`.

```php
$callable = new CallableFake;

$thing->closureTypeHintedMethod($callable->asClosure());

$callable->assertInvoked();
```

### wasInvoked(): bool

```php
if ($callable->wasInvoked()) {
    //
}
```

### wasNotInvoked(): bool

```php
if ($callable->wasNotInvoked()) {
    //
}
```

### called(callable $callback): array

```php
$invocationArguments = $callable->called(function (Dependency $dependency): bool {
    return Str::startsWith($dependency->name, 'spatie/')
});
```

## Specifying return values

If you need to specify return values, this _could_ be an indicator that this is not the right tool for the job. But there are some cases where return values determine control flow, so it can be handy, in which case you can pass the contructor a "return resolver" closure.

```php
$callable = new CallableFake(function (Dependency $dependency): bool {
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

## Thanksware

You are free to use this package, but I ask that you reach out to someone (not me) who has previously, or is currently, maintaining or contributing to an open source library you are using in your project and thank them for their work. Consider your entire tech stack: packages, frameworks, languages, databases, operating systems, frontend, backend, etc.
