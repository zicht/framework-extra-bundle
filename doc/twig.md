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
`ga_trackevent`     Render an `onclick="_gaq.push([])"`
`with`              Add a key/value pair to an array and return the result
`without`           Remove a key from an array and return the result
`round`             Round the value
`ceil`              Ceil the value
`floor`             Floor the value
------------------  -------------------------------------------------------------------------------------------

## Tests ##

------------------  -------------------------------------------------------------------------------------------
`numeric`       Test that checks if a given value is numeric  
------------------  -------------------------------------------------------------------------------------------


## Language constructs ##

### `switch` ###

The switch node implements a switch statement in twig. The main differences with a regular PHP switch statement are

* You can not explicitly break a case
* Fallthrough is not the default
* Fallthrough is annotated on the node you want to fallthrough into
* You can pass multiple expressions to one case

#### Example

```twig
{% switch value %}
    {% case "a", "b" %}
        value is 'a' or 'b'
    {% case "c" fallthrough %}
        value is 'a' or 'b' or 'c'
    {% default %}
        value is something else
{% endswitch %}
```

This would roughly compile to the following PHP code:

```php
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
```
