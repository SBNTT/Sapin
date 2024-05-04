# Introduction

A Sapin component consists of a php class associated with an html template contained in a `#!html <template>` tag.

The template section of a Sapin component is written in HTML based syntax and  must take place outside the php tags.

The component compiler provides support for special attributes, and an interpolation syntax which can contains
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

As you can see, these two parts belong to the same file somewhat like Vue.js. Since it is legal to mix HTML 
and PHP in the same file, your favorite editor will not complain about it.

!!! tip "phtml for the win !!"
    The idea of mixing HTML and PHP may sounds terrible. I mean, in most case, it is :upside_down:.
    Sapin takes advantage of this possibility and provide an elegant and convenient way to represent 
    a part of an user interface.
