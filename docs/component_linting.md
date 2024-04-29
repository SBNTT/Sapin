Under the hoods, the Sapin compiler merges the php and html part of a component into a single php class.
The template part is transformed into a `render` method that belong to this class.

Given the Greeter Sapin component:

```html+php title="The Greeter component"
<?php

namespace App\Component;

final readonly class Greeter
{
    public function __construct(
        public string $name,
    ) {}
} ?>

<template>
    <span>Hello, {{ $this->name }}!</span>
</template>
```

Here is its compiled version (simplified):
```php
<?php

class _Greeter
{
	public function __construct(
		public string $name,
	) {
	}
    
	public function render(): void
	{
		?><span>Hello, <?php echo $this->name;?></span><?php
	}
}
```

Sapin does **not** use any opaque array to carry over data from php to templates rendering (like some other templates 
engines like twig do). It consumes the data it needs directly.

Thus, compiled Sapin components are **fully lintable** ! This give the opportunity to validate all the php expression
you write inside the template part. If you use a variable, method, or anything else that is not defined, your linter 
will be able to detect that :tada:

## Linter configuration

WIP