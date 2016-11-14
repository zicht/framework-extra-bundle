# 4.1.10 #
* Adds a compiler pass looking for FilesystemCache instances for doctrine, and forces their umask to be the system umask()


# 4.2.0 #
* Add a parent validator for self-referencing relations
* Restored support for `truncate_html` filter

# 4.3.0 #
* Add a new utility command `zicht:content:list-images` which searches for `<img ...>` and lists all files with their file size

# 5.0.0 #
* UpdateSchema command-listener enabled by default.