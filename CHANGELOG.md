# 4.1.10 #
## Features
* Adds a compiler pass looking for FilesystemCache instances for doctrine, and forces their umask to be the system umask()

# 4.2.0 #
## Features
* Add a parent validator for self-referencing relations
* Restored support for `truncate_html` filter

# 4.3.0 #
## Features
* Add a new utility command `zicht:content:list-images` which searches for `<img ...>` and lists all files with their file size

# 5.0.0 #
## Breaking changes
* UpdateSchema command-listener enabled by default.

# 5.1.0 #
## Features
* Adds a translation route which can service translations to the front-end based on message id.

# 5.2.0 #
## Changes:
 * Add more options to retrieve translations:
    `/translate/{locale}` -> returns all translations for that locale
    `/translate/{locale}/{domain}` -> returns all translations for that locale and domain
    `/translate/{locale}/{domain}?id[]=abc&id[]=xyz` -> returns translations for only the provided ids (within the locale and domain)