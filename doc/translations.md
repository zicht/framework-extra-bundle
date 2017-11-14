# Translation

Sometimes you want to translate something from javascript, this can be achieved
in many different ways.  The `TranslationController` provides a simple `JsonResponse` to
requested translations.

Furthermore, the `Translator` class provides an alternative translator that listens to the
special `zz` locale, returning the translation keys and its parameters instead of actually 
translating the key.  This is useful when you want to change existing translations.

The `Translator` class is enabled by adding the following to your `config.yml`:

```yaml
parameters:
    translator.class: 'Zicht\Bundle\FrameworkExtraBundle\Translation\Translator'
```
