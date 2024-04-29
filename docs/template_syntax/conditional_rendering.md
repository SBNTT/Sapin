## If
The special `:if` attribute should be used to allow a block to render only under certain circumstances.
Its value must be a valid php expression that resolves to a boolean value.

In the following example, the entire `#!html <span>` tag will **not** be rendered if `#!php $this->a > $this->b`
resolves to `#!php false`
```html
<span :if="$this->a > $this->b">
    a is greater than b!
</span>
```

## Else
Use `:else` attribute to render a block following another whose `:if` expression has been resolved to `#!php false`
```html
<span :if="$this->a > $this->b">
    a is greater than b!
</span>
<span :else>
    a is smaller than b!
</span>
```

## Else if
```html
<span :if="$this->a > $this->b">
    a is greater than b !
</span>
<span :else-if="$this->a < $this->b">
    a is smaller than b !
</span>
<span :else>
    a is the same as b !
</span>
```