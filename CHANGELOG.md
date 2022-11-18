# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 10.0.2 - 2022-11-16
### Added
- Forward compatibility for `TokenParserInterface`.

## 10.0.1 - 2022-11-14
### Removed
- Removed several console commands due to inactive use

## 10.0.0 - 2022-11-11
### Added
- Support for Symfony ^5.4
### Removed
- Support for Symfony 4
- Support for PHP 7.2 & 7.3

## 9.4.3 - 2022-10-14
### Changed
- Swapped the zicht/standards-php (PHPCS) linter for PHP CS Fixer.
### Added
- Introduced Vimeo Psalm and fixed codebase
### Removed
- Removed serveral unused elements

## 9.4.2 - 2022-09-26
### Fixed
- Added `'choice_translation_domain' => false` to ParentChoiceType. Choice labels do not need translation.

## 9.4.1 - 2022-05-16
### Fixed
- Fixed Pageable interface types

## 9.4.0 - 2021-11-16
### Added
- Support for PHP 8.
### Removed
- Previously deprecated `Util/SortedList`.

## 9.3.4 - 2021-11-17
### Changed
- Upgraded to phpunit 7.

## 9.3.3 - 2021-10-25
### Fixed
- Passing a `null` to `method_exists` is deprecated and will break in PHP 8.

## 9.3.2 - 2021-10-11
### Added
- Added login logo in SVG format. To use this new logo, change the path for `title_logo`
 in the file `config/packages/sonata_admin.yml`:
```yaml
sonata_admin:
    title_logo: 'bundles/zichtframeworkextra/images/logo_login.svg'
```
### Fixed
- Fixed \Zicht\Bundle\FrameworkExtraBundle\Helper\EmbedHelper::handleForm() Docblock types

## 9.3.1 - 2021-08-27
### Added
- Added FQCN aliases in service configuration for services that are typically injected

## 9.3.0 - 2021-04-08
### Added
- Merge from 8.6.0 and 7.3.0.

## 9.2.1 - 2021-03-30
### Fixed
- Removed usage of deprecated Doctrine classes to be compatible with newer versions

## 9.2.0 - 2020-11-16
### Added
- Forward merge from 8.4.0, 8.4.1, 8.4.2, and 8.5.0.
  + `JsonSchemaType`
  + `SchemaService`

## 9.1.0 - 2020-11-03
### Added
- Add test `instanceof` in Twig extensions.

## 9.0.4 - 2020-10-21
### Changed
- Allow `Zicht\Bundle\FrameworkExtraBundle\Url\UrlCheckerService` to be a public service.

## 9.0.3 - 2020-10-20
### Changed
- Allow `zicht_embed_helper` to be a public service.

## 9.0.2 - 2020-08-12
### Fixed
- Forward merge from 8.2.2 and 8.3.0.

## 9.0.1 - 2020-06-22
### Fixed
- `Builder::create` allows for both `string` and `array` values.

## 9.0.0 - 2020-05-15
### Added
- Support for Symfony 4.x and Twig 3.x
### Removed
- Support for Symfony 3.x
- RequireJs and Uglify JS toolings
### Changed
- Removed Zicht(Test)/Bundle/FrameworkExtraBundle/ directory depth: moved all code up directly into src/ and test/

## 8.6.0 - 2021-04-08
### Added
- Merge from 7.3.0

## 8.5.0 - 2020-11-05
### Added
- `SchemaService` was added.  This service can...
  + create `Schema` instance (using a special loader to resolve `"$ref"` on disk)
  + validate data
  + migrate data
- `JsonSchemaAutoCompleteType` was added.
- `json-editor-view.ts` will now disable any `<input type="submit">` buttons that
  are in the same `<form>` as the schema while the data is invalid.
### Changed
- `JsonSchemaType` now takes `SchemaService` as the first argument instead of the webdir.
  While this is not backwards compatible, the only place where this type is currently used,
  is inside this library, hence the major version was not incremented.
### Fixed
- `json-editor.scss` fixes styling issue for editors inside a popup.

## 8.4.2 - 2020-10-09
### Added
- Merge from 7.2.0.
  7.2.0 contains the backport for the `CreateSAtdLoggerTrait` already included in 8.x,
  hence this is patch release.

## 8.4.1 - 2020-10-01
### Fixed
- `autocomplete.ts` now allows for non-selectable `{"info":"Lorem"}` items to be
  included in the results.  This provides flexibility for feeds that want to enhance
  the user experience.

## 8.4.0 - 2020-09-17
### Added
- `JsonSchemaType` form type that renders a form based on a json-schema.
- Autocompletion for the json-schema editor.
- javascript and css code to include on the admin site.

## 8.3.0 - 2020-08-11
### Added
- Added `CreateSAtdLoggerTrait` to help console commands output to either stdout or stderr depending on log level.

## 8.2.2 - 2020-06-30
### Changed
- Fix bug in parsing requirejs config

## 8.2.1 - 2020-04-29
### Changed
- Use FQCN for form types

## 8.2.0 - 2020-04-24
### Fixed
- Fixed composer.json (psr-4 autoloader, dependencies), fixed linter errors, fixed maintainers

## 8.1.7 - 2020-01-29
### Fixed
- Fix a bug inside the `EmbedHelper::getFormState` where the variable `$state` is `null` and not an array.
  But is used as an array.

## 8.1.4 - 2019-09-20
### Fixed
- `UrlCheckerService` now supports subdomains with `-`

## 8.1.3 - 2019-08-05
### Fixed
- No longer using non existing `ContainerAware` class.  This no longer exists in
  symfony 3.4.  Using `AbstractController` instead.

## 8.1.2 - 2019-07-18
### Added
- Merge from 7.1.2.

## 8.1.1 - 2019-07-12
### Added
- Merge from 7.1.1.

## 8.1.0 - 2019-07-11
### Added
- Merge from 7.1.0.

## 8.0.6 - 2019-02-01
### Changed
- Changed injection of own Translator because of deprecated translator.class parameter
- Drop false positives in deprecations check and fix depr warning about doctrinedumper
- Fixed wrong inheritdoc syntax and replace unnecessary doc blocks with inheritdoc

## 8.0.5 - 2019-01-04
### Changed
- Get Twig extension by FQCN in other Twig Node classes

## 8.0.4 - 2018-12-21
### Changed
- Get Twig extension by FQCN
### Removed
- Removed deprecated PHPDoc author tag

## 8.0.3 - 2018-11-05
### Changed
- Changed the MarkupType option 'virtual' to 'inherit_data' because 'virtual' is deprecated

## 8.0.2 - 2018-09-19
### Changed
- Fix bug in parsing uglify config

## 8.0.0 - 2018-06-21
### Added
- Support for Symfony 3.x and Twig 2.x
### Removed
- Support for Symfony 2.x and Twig 1.x

## 7.3.0 - 2021-04-08
### Added
- Support for Itertools v2.15.0 and higher, where (1) its name in Twig can be
  configured and (2) the legacy Twig filters and functions can be disabled.

  ```yaml
  zicht_framework_extra:
    itertools:
        twig_name: it                   # use `it` (default)
        twig_enable_legacy_api: true    # enable legacy api (default)
  ```

## 7.2.0 - 2020-10-09
### Added
- Merge from 6.2.0.

## 7.1.2 - 2019-07-18
### Added
- Merge from 6.1.1.

## 7.1.1 - 2019-07-12
### Fixed
- Fixed but introduced by the previous merge.  This caused the
  `EmbedHelper` to fail, as it was using undefined parameters.

## 7.1.0 - 2019-07-11
### Added
- Merge from 6.1.0.

## 7.0.0 - 2018-06-21
- removed (unesesery) dependencie, fixed constraints and global namespaces
- removed LiipImagine because logic was not working and a changed version
  is moved to zicht/liip-imagine-bundle.
- the twig filter prefix_multiple and trans_multiple returns now a Generator instead of MapIterator.
- removed unnecessary parameters of the embed helper and hard dependency of service container. The second
  request argument is removed of the handleForm method and $handlerCallback will only get the form.

<<<<<<< HEAD
=======
## 6.2.0 - 2020-10-09
### Added
- Merge from 5.5.0.

>>>>>>> release/7.x
## 6.1.1 - 2019-07-18
### Added
- Merge from 5.4.1.

## 6.1.0 - 2019-07-11
### Added
- Merge from 5.4.0.

## 6.0.1 - 2018-04-03
- Added an extra callback to `EmbedHelper::handleForm` to control the determination of the form-id.
When having multiple versions of the same form (with different data, but data of the same type) on one page, the handling fails because the Handler cannot reliably distinguish between forms anymore.
This results in errors and data always being handled on the first form.

## 5.5.0 - 2020-10-09
### Added
- Added CreateSAtdLoggerTrait to help console commands output to either stdout or stderr depending on log level.
  Note that this trait was backported from 8.3.0 to be used in old projects that still rely on 5.x.

## 5.4.1 - 2019-07-18
### Fixed
- Fixed issue in `UrlCheckerService` where it would crash when no master request exists.

## 5.4.0 - 2019-07-11
### Added
- Adds the `UrlCheckerService`.
- The `EmbedHelper::getEmbedParams` and `EmbedHelper::handleForm` will not
  accept a return_url or success_url when the `UrlCheckerService` considers
  it to be unsafe.

## 5.3.0 - 2017-02-15
### Changed
- Adds the twig function `embedded_image`.  This function takes a
  filename and will return an embed string with the file's contents.
  Used for, i.e. images in e-tickets.

## 5.2.0 - 2017-01-19
### Changed
- Add more options to retrieve translations:
  `/translate/{locale}` -> returns all translations for that locale
  `/translate/{locale}/{domain}` -> returns all translations for that locale and domain
  `/translate/{locale}/{domain}?id[]=abc&id[]=xyz` -> returns translations for only the provided ids (within the locale and domain)

## 5.1.0 - 2016-12-29
### Added
- Adds a translation route which can service translations to the front-end based on message id.

## 5.0.0 - 2016-11-14
### Changed
- UpdateSchema command-listener enabled by default.

## 4.3.0 - 2016-08-19
### Added
- Add a new utility command `zicht:content:list-images` which searches for `<img ...>` and lists all files with their file size

## 4.2.0 - 2016-08-17
### Added
- Add a parent validator for self-referencing relations
- Restored support for `truncate_html` filter

## 4.1.10 - 2016-08-11
### Added
- Adds a compiler pass looking for FilesystemCache instances for doctrine, and forces their umask to be the system umask()
