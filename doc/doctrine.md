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


