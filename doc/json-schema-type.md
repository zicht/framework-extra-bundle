## JsonSchema tools ##

### `JsonSchemaType` form type
Used to render a form based on a json-schema.

Include the provided js and css file, or build your own based on the source filed.

```
<!-- Include css -->
<link rel="stylesheet" href="{{ asset('bundles/zichtframeworkextra/json-editor.css') }}" type="text/css" media="all" />

<!-- Include js -->
<script src="{{ asset('bundles/zichtframeworkextra/json-editor+.js') }}"></script>
```

### autocompletion
Autocompletion from [@trevoreyre/autocomplete-js](https://github.com/trevoreyre/autocomplete) is provided by the
`json-editor.css` and `json-editor+.js` files.  To use it, configure your json-schema as follows:

```json
{
    "$schema": "http://json-schema.org/draft-07/schema#",
    "title": "Autocomplete example",
    "type": "string",
    "format": "autocomplete",
    "options": {
        "url": "/api/your-autocomplete-feed.json?search=",
        "autocomplete": {
            "search": "json_feed_search",
            "getResultValue": "json_feed_result",
            "renderResult": "json_feed_render",
        }
    }
}
```

You will need to provide a json feed that provides the selectable values and
optionally labels, image and usage information.  Note that the labels and usage information
may contain HTML.

For example, the api call
`/api/your-autocomplete-feed.json?search=Hello%20World` could return the one of the following:

Simple feed where the label shown is the value stored:
```json
["Hello everyone", "Hello Boudewijn", "Nice world you have there"]
```

Alternatively:
```json
{"result": ["Hello everyone", "Hello Boudewijn", "Nice world you have there"]}
```

However, when you want to show label that is different from the stored value:
```json
[
    {"label": "hello everyone", "value": "123"},
    {"label": "hello Boudewijn", "value": "42"},
    {"label": "Nice world you have there", "value": "321"}
]
```

Alternatively:
```json
{
    "result": [
        {"label": "hello everyone", "value": "123"},
        {"label": "hello Boudewijn", "value": "42"},
        {"label": "Nice world you have there", "value": "321"}
    ]
}
```

But wait, there is more!  You can also specify `"info": "Lorem ipsum"` to add a non-selectable
item in the results list.  This could, for example, be used to explain how the search works,
i.e. if your feed supports special structures such as: `id:123`.  While the `"image"` fields
are used add an image to the result.  For example:
```json
{
    "result": [
        {"info": "Remember that you can also search for dates using: 'date:2020-07-24'"},
        {"image": "/image/123.jpg", "label": "hello everyone", "value": "123"},
        {"image": "/image/042.jpg", "label": "hello Boudewijn", "value": "42"},
        {"image": "/image/321.jpg", "label": "Nice world you have there", "value": "321"}
    ]
}
```
