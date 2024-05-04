Given this component:
```html+php
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

In order to use it in another component template, you have to import it using the `:uses` attribute on the
`#!html <template>` tag first.

All the required `Greeter` component constructor arguments must be set using computed attributes. The order does
not matter given that Sapin will invoke the constructor with the named argument syntax.
```html
<template :uses="App\Component\Greeter">
    <h1>My app</h1>
    <Greeter :name="'John'" />
</template>
```

the `:uses` attribute may contains multiple imports. They must be coma separated. These following examples are valid:
```html
<template :uses="App\Component\ComponentA, App\Component\ComponentB">
   ...
</template>
```

```html
<template :uses="
    App\Component\ComponentA, 
    App\Component\ComponentB"
>
   ...
</template>
```

!!! info
    You can use conditional and/or looping attributes on component invocation
    ```html title="Repeat the component rendering for each element of an array"
    <template :uses="App\Component\Fruit">
        <Fruit
            :foreach="$this->fruits as $fruit"
            :fruit="$fruit"
        />
    </template>
    ```
    ```html title="Conditionaly render a component"
    <template :uses="App\Component\Greeter">
        <Greeter
            :if="$this->shouldGreet"
            :name="'John'"
        />
    </template>
    ```
