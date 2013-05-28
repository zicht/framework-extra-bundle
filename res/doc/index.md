# ZichtFrameworkExtraBundle #

Bundle with utility classes.

* EmbedHelper - Embed helper for forms
* [Twig extensions](twig.html)
  * dump filter
  * truncate(length, ellipsis = '...') filter
  * 'switch' and 'with' control structures
* Doctrine extensions
  * RAND() function

## EmbedHelper ##

## Twig Extensions ##

## Doctrine Extensions ##

Usage of the doctrine RAND() function is as follows. First register the RAND()
function in your config.yml:

    doctrine:
        orm:
            # ....
            dql:
                numeric_functions:
                    RAND: "Zicht\Bundle\FrameworkExtraBundle\Doctrine\FunctionNode\Rand"
            # ....


