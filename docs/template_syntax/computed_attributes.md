When any of your attributes is prefixed with a colon, the output of the given expression will be used as the
attribute value (the colon character will be dropped).

The attribute value must be a php expression which resolve to a stringable data.
```html title="Computed attribute example"
<div :id="$this->myId"></div>
```
In this case, if your component class has a property `myId` that has `42` as a value, the div above will render as
`#!html <div id="42"></div>`