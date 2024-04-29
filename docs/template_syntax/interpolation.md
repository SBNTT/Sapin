The interpolation syntax allow you to inject some data from php inside html tags. it consists of a php
expression contained between double curly braces. The php expression must resolve to a stringable data.

```html title="Basic Interpolation"
<span>Hello, {{ $this->name }}!</span>
```

!!! info
    Note that you have access to `#!php $this`. The same applies to any other php expression belonging to the
    template part of a component template.

    This is because the template part of a Sapin component is compiled to a method that belong to a copy of your 
    class, giving it access to `#!php $this`.

It can also be used inside static attributes, like so:

```html title="Interpolation in static attribute"
<div id="item-{{ $this->id }}"></div>

```
