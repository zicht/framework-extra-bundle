# Twig extensions #

## Filters ##

------------------  -------------------------------------------------------------------------------------------
`date_format`       Format the date using strftime()
`dump`              Dumps the contents of the variable
`truncate`          Truncate a string and add an ellipsis (...) if the original size was more than the truncated size
`regex_replace`     Do a regular expression replace
`re_replace`        Alias for regex_replace
`relative_date`     Calculate the relative date and output it, e.g. "4 hours ago"
`str_uscore`        Convert the string to an underscore-delimited string
`str_dash`          Convert the string to a dash-delimited string
`str_camel`         Convert the string to a camelcased format
`ga_trackevent`     Render an onclick="_gaq.push([])"
`with`              Add a key/value pair to an array and return the result
`without`           Remove a key from an array and return the result
`round`             Round the value
`ceil`              Ceil the value
`floor`             Floor the value
------------------  -------------------------------------------------------------------------------------------

## Language constructs ##

### `with` ###

The 'with' constructs drops into a lower scope of values. If the value evaluates to empty, the entire block is skipped.

For instance:

    {% with page.items as items %}
        <div class="items">
            {% for i in items %}

            {% endfor %}
        </div>
    {% endwith

If there are no items in the page.items list, the entire block is skipped.

The 'with' tag allows a scope-shift into a defined array. The format is as follows:

    {% with expr [as localName] [, expr2 [as localName2], [....]]  {sandboxed|merged} %}
        content
    {% endwith %}

The with construct sets the argument as the current context. If an 'as name' is defined,
the variable is defined as that local name. The two flags sandboxed or merged define an
additional behaviour, allowing to sandbox the contents of the construct from the current
scope (which results in having only the defined variables in the current scope), or merging
them with the current scope respectively. If none of the flags is defined, the current
context is stacked in the context as _parent. After the end of the with construct, the
context is restored.

Example:

Assume the following context:

    array(
       'foo' => array(
          'name' => 'Foo',
          'id'   => 1
       ),
       'bar' => array(
          'name' => 'Bar',
          'id'   => 2
       )
    )

    {% with foo %}
       {{ id }}: {{ name }} {# would output: "1: Foo" #}
    {% endwith %}

    {% with foo as baz %}
       {{ baz.id }}: {{ baz.name }} {# would output: "1: Foo" #}
    {% endwith %}

    {% with foo as bar, bar as foo %}
       {{ bar.id }}: {{ bar.name }} {# would output: "1: Foo" #}
       {{ foo.id }}: {{ foo.name }} {# would output: "2: Bar" #}
    {% endwith %}

    {% with foo merged %}
       {{ foo.id }}: {{ foo.name }} {# would output: "1: Foo" #}
       {{ id }}: {{ name }} {# would output: "1: Foo" #}
    {% endwith %}

### `switch` ###

The switch node implements a switch statement in twig. The main differences with a regular PHP switch statement are

* You can not explicitly break a case
* Fallthrough is not the default
* Fallthrough is annotated on the node you want to fallthrough into
* You can pass multiple expressions to one case

Example:

    {% switch value %}
        {% case "a", "b" %}
            value is 'a' or 'b'
        {% case "c" fallthrough %}
            value is 'a' or 'b' or 'c'
        {% default %}
            value is something else
    {% endswitch %}

This would roughly compile to the following PHP code:

    switch ($value) {
        case "a":
        case "b":
            echo 'value is 'a' or 'b'";
            // no break here, because 'fallthrough' is on the next node
        case "c":
            echo 'value is 'a', 'b' or 'c'";
            break;
        default:
            echo 'value is something else';
            break;
    }

---------------------------
[index.html](index)
