# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 5.4.0 - 2019-07-11
### Added
- Adds the `UrlCheckerService`.

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
