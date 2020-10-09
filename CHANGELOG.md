# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 6.2.0 - 2020-10-09
### Added
- Merge from 5.5.0.

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
