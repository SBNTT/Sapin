The interpolation syntax allow you to inject some data from php inside html tags. It consists of a php
expression between two curly braces. The php expression must be convertable into a string (implement Stringable
or be a string).

```html title="Basic Interpolation"
<span>Hello, {{ $this->name }}!</span>
```

```html title="Interpolation within an html attribute"
<div id="item-{{ $this->id }}"></div>
```

!!! note
    You have access to `#!php $this`. The same applies to any other php expression belonging to the
    template section of a component.

    This is because the template part of a Sapin component is compiled to a method that belong to a copy of your 
    class, giving it access to `#!php $this`.
