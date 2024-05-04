A fragment is meant to group multiple sibling tags. Useful if you want to apply a conditional / array rendering to
multiple elements at once without introducing a pointless `#!html <div></div>`.
```html
<fragment :if="count($this->fruits) > 0">
    <span>Available fruits:</span>
    <ul>
        <li :foreach="$this->fruits as $fruit">
            {{ $fruit }}
        </li>
    </ul>
</fragment>
<span :else>
    No fruits available...
</span>
```
!!! note
    Using a fragment without using any special attribute (like conditional or array rendering) literally does nothing.
