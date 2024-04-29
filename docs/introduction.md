# Introduction

A Sapin component consists of a plain php class, associated with an html 
template contained in a `#!html <template>` tag.

The template part of a Sapin component uses an HTML based syntax and  must takes place outside php tag, and 
contained in a `#!html <template>` tag.

The component compiler provides support for special attributes, and an interpolation syntax which should contains
php expressions. There is no "language in the language", just php in html.

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

As you can see, these two parts belongs to the same file somewhat like Vue.js.
Since it is legal to mix HTML and PHP in the same file, your favorite editor 
will not complains about that.

!!! tip "phtml for the win !!"
    The idea of mixing HTML and PHP may sounds terrible. I mean, in most case, it is :upside_down:.
    Sapin takes advantage of this possibility and provide an elegant and convenient way to represent 
    a part of an user interface.
