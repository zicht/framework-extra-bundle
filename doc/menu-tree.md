# Menu tree

We use KNP to build and edit tree structures.  These are mainly used for menu items and taxonomy
trees.

The `TreeAdmin` class is provided in the `AdminBundle`.

Sometimes this tree becomes corrupted.  The command `zicht:repair:nested-tree` provided by 
`RepairNestedTreeCommand` can fix corrupted tree structures.

The `NestedTreeValidatorSubscriber` is a tool to determine the cause of tree corruptions.  But it
should only be used in testing and development mode.  Enable it by adding the following to your
`config_development.yml`:

```yaml
 services:
    tree_validation_subscriber:
        class: Zicht\Bundle\FrameworkExtraBundle\Doctrine\NestedTreeValidationSubscriber
            arguments: ['ZichtMenuBundle:MenuItem']
            tags:
                - { name: doctrine.event_subscriber }
```
