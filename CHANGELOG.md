# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

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

## 7.0.0 - 2018-06-21
- removed (unesesery) dependencie, fixed constraints and global namespaces
- removed LiipImagine because logic was not working and a changed version
  is moved to zicht/liip-imagine-bundle.
- the twig filter prefix_multiple and trans_multiple returns now a Generator instead of MapIterator.
- removed unnecessary parameters of the embed helper and hard dependency of service container. The second
  request argument is removed of the handleForm method and $handlerCallback will only get the form.
  
## 6.0.1 - 2018-04-03
- Added an extra callback to `EmbedHelper::handleForm` to control the determination of the form-id. 
When having multiple versions of the same form (with different data, but data of the same type) on one page, the handling fails because the Handler cannot reliably distinguish between forms anymore.
This results in errors and data always being handled on the first form.

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
