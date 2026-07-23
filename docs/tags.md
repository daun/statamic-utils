# Tags

[Tags](https://statamic.dev/tags) fetch, filter, and display data or add dynamic functionality in Antlers templates.

### Capture

Capture the output of a template section and assign it to a variable. Similar to assigning the output
of a partial view to a variable, but without the need for an actual partial file.

```antlers
{{ capture:contents }}
    Any output inside of this will land in the `contents` variable.
{{ /capture:contents }}
```

An optional `trim` parameter will trim the output of whitespace.

```antlers
{{ capture:contents trim="true" }}
    {{ title }}
{{ /capture:contents }}
```

An optional `when` parameter will only render and capture the output if the condition is met.

```antlers
{{ capture:contents :when="count >= 1" }}
    Found {{ count }} results
{{ /capture:contents }}
```

### Icon

Render an SVG icon from an existing sprite map.

```antlers
{{ icon:search }}
```

```html
<svg class="icon icon-search" preserveAspectRatio="xMinYMid" aria-hidden="true">
    <use xlink:href="#icon-search">
</svg>
```

### IfContent

Render a block of content only if it is not empty, i.e. if it contains actual text content. A block
of content containing only whitespace or empty tags will not be rendered.

```antlers
{{ if_content }}
    <ul>
        {{ categories }} <li>{{ title }}</li> {{ /categories }}
        {{ tags }} <li>{{ title }}</li> {{ /tags }}
    </ul>
{{ /if_content }}
```

### Repeat

Render the contained content a specified number of times.

```antlers
{{ repeat times="3" }}
    <p>This paragraph will be repeated 3 times.</p>
{{ /repeat }}
```

### Get Mount Root

Find the root entry mounted at the current URL. Useful for locating the top-level page of a mounted
collection. Pass an explicit URL via the `of` parameter to look it up somewhere other than the current page.

```antlers
{{ get_mount_root }}
    Root page: {{ title }}
{{ /get_mount_root }}

{{ get_mount_root of="/blog/my-post" }}
```

### Key

Inject a stable `key` attribute into the first element of a block so a morphing frontend (e.g. Alpine
/ Livewire) skips re-rendering it when its content is unchanged. Must be used as a pair.

```antlers
{{ key:tag }}
    <div class="card">{{ content }}</div>
{{ /key:tag }}
```

```html
<div key="<md5-of-rendered-content>" data-skip-morph-if-keys-equal class="card">...</div>
```

### Random

Generate a random value. Use `{{ random }}` for a 32-character hexadecimal hash, or `{{ random:int }}`
for a random integer. The integer variant accepts optional `min` (default `1`) and `max` (default
`PHP_INT_MAX`) parameters.

```antlers
{{ random }}                        {{# e.g. 9e107d9d372bb6826bd81d3542a419d6 #}}
{{ random:int }}                    {{# random integer #}}
{{ random:int min="1" max="10" }}   {{# random integer between 1 and 10 #}}
```
