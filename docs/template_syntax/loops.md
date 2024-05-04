## Foreach
Use the special `:foreach` attribute on a block that needs to be rendered as many times as there are elements in a list.

Its value must be a valid plain php `foreach` expression.
```html
<ul>
    <li :foreach="$this->fruits as $fruit">
        {{ $fruit }}
    </li>
</ul>
```

## For
Same, but this time, the `:for` attribute value must be a valid `for` loop expression
```html
<ul>
    <li :for="$i = 0; $i < 10; $i++">
        {{ $i }}
    </li>
</ul>
```

!!! info
    Any local variable introduced by the `:for` or `:foreach` expression is available inside its corresponding
    block


## Conditional and array rendering attributes combination
`:if` `:else-if` `:else` (conditional rendering attributes) and looping attributes can be used on the same html 
element but behaves differently depending on their declaration order.

### Conditional rendering attribute first
When using one of `:if` `:else-if` `:else` attributes **first**, the entire loop rendering will depend on the
output of the conditional expression.

This template will or will not render the **entire** array (imagine a for[*each*] loop inside an if statement):

```html title="Conditionaly render the entire array"
<span 
    :if="$this->shouldDisplayNumbers" 
    :foreach="$this->numbers as $number"
>
    {{ $number }}
</span>
```

### Looping attribute first
When using one of the looping attributes first, the only conditional attribute allowed is `:if`. In this situation,
the conditional expression acts as a **filter** on the list (imagine an if statement inside a for[*each*] loop).

This template only render even numbers:
```html title="Filter array's elements to render"
<span
    :foreach="$this->numbers as $number"
    :if="$number % 2 == 0" 
>
    {{ $number }}
</span>
```