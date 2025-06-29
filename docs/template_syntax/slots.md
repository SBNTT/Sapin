The slot system allows components to receive blocks of HTML content, providing flexibility and 
reusability in component design.

## Defining Slots

To define slots within a component, encapsulate the desired content areas within <slot> tags and specify a 
unique name for each slot using the :name attribute.

```html+php
<?php
namespace App\Component;

final readonly class Card 
{
} ?>

<template>
    <div class="card">
        <div class="card-title">
            <slot :name="title">
                Default Title
            </slot>
        </div>

        <div class="card-content">
            <slot :name="content"></slot>
        </div>

        <div class="card-footer">
            <slot :name="footer">
                Default Footer
            </slot>
        </div>
    </div>
</template>
```
In this example, the component Card defines three slots: title, content, and footer.

## Using slots
```html+php
<?php
namespace App\Component;

final readonly class HomePage 
{
} ?>
<template :uses="App\Component\Card">
    <Card>
        <fragment :slot="title">My Card Title</fragment>
    
        <p :slot="content">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus assumenda aut debitis distinctio, 
            doloremque dolores impedit iste iusto, magni nam numquam optio quo recusandae sint ullam ut voluptas 
            voluptatem voluptatum.
        </p>
    
        <fragment :slot="footer">Custom Footer</fragment>
    </Card>
</template>
```
When defining a slot content, you do not have access to any additional scope. Inside `#!html <p :slot="content">`,
`#!php $this` corresponds to an `HomePage` instance, somewhat if there was no `:slot` attribute 

## Fallback content
```html
<template>
    <Card>
        <p :slot="content">
            Only content provided. No title or footer.
        </p>
    </Card>
</template>
```

In this case, since the title and footer slots are not filled, the default content specified in the 
Card component template is used.

## Default slot
When a child element of a component does not explicitly declare a `:slot` attribute, it is automatically assigned to a
reserved slot named `children`. 

This implicit slot behaves like any named slot and can be referenced in the component template using:

```html
<slot :name="children" />
```

This provides a convenient shorthand for content that does not require an explicit slot declaration, while preserving 
full control in the component layout.

```html+php
<?php
namespace App\Component;

final readonly class Card 
{
} ?>

<template>
    <div class="card">
        <slot :name="children" />
    </div>
</template>
```

```html
<template>
    <Card>
        <p>This paragraph is implicitly assigned to the "children" slot.</p>
        <p>This one too :)</p>
    </Card>
</template>
```