## Doctrine Schema Update Listener ##

Disables the `doctrine:schema:update` command.
To disable the listener (and to enable the command) add this in your config yml:

    zicht_framework_extra:
        disable_schema-update: false
        
        
To enable the listener (and to disable the command) set `disable_schema-update` to `true`