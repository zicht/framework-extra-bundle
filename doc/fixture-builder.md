# Fixture builder

The fixture builder is a utility class that you can use to easily create more 
complex fixtures without having to write too much code.

The general approach is that you can call methods (usually setters) directly, 
but when a method does not exist, it is assumed that you want to create a new 
object of that type, and "add" it to the 'current' object. The type is built
from the name of the method you're calling and is appended to the namespaces
you have identified at construction time.

## Example: 

Let's say you have the following classes:

```php
namespace FooBundle\Entity;

class Category
{
    private $products = [];
    private $title;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function addProduct(Product $product)
    {
        $this->products[]= $product;
    }
}

class Product
{
    private $title;
    private $price;

    public function __construct($title)
    {
        $this->title = $title;
    }
    
    
    public function setPrice($price)
    {
        $this->price = $price;
    }
}
```

You can now create categories and products like this:

```php
// all class names refer to FooBundle\\Entity\\...
Builder::create('FooBundle\\Entity\\')
   // creates new FooBundle\Entity\Category('Tools') and put it on the stack
  ->Category('Tools') 
      // - create product and put it on the stack
      ->Product('Hammer')
          // call the setPrice on the item on the stack
          ->setPrice(10)
          
          // pop the item from the stack and add it to 
          // the item on top of the stack 
          ->end()
          
      // another product in the same manner.
      ->Product('Wrench')->setPrice(15)->end()
   
   // pop the stack and call the `always` function
  ->end()
  ->Category('Hardware')
      ->Product('Plywood')->setPrice(1.5)->end()
      ->Product('Steel')->setPrice(3.5)->end()
  ->end();
```

To persist and save these objects to the database, you will need to tell doctrine.
Because often you will want to do something for all objects, you can add an `always`
call with a callback which will be executed whenever an object is "ended" (i.e., 
you will make no more changes within the builder):

```php
Builder::create('FooBundle\\Entity')
  ->always(function ($entity) use ($manager) {
       $manager->persist($entity);
  });
  ->Category('Tools')
      ->Product('Hammer')->setPrice(10)->end()
      ->Product('Wrench')->setPrice(15)->end()
  ->end()
  ->Category('Hardware')
      ->Product('Plywood')->setPrice(1.5)->end()
      ->Product('Steel')->setPrice(3.5)->end()
  ->end();
```

A very useful feature is that the builder deduces that you will want to add children
if the class it tries to create is of the same type. Modifying the Category class
as such:

```php
class Category
{
    private $title;
    private $parent = null;
    private $products = [];
    private $children = [];
    
    public function __construct($title)
    {
        $this->title = $title;
    }

    public function addProduct(Product $product)
    {
        $this->products[]= $product;
    }
    
    public function addChild(Category $category)
    {
        $this->children[]= $category;
    }
    
    public function setParent(Category $category)
    {
        $this->parent = $category;
    }
}
```

Now we can add tree-like structures in the following manner:

```php
Builder::create('FooBundle\\Entity')
  ->always(function ($entity) use ($manager) {
       $manager->persist($entity);
  });
  ->Category('Tools')
      ->Category('Woodwork')
          ->Product('Hammer')->setPrice(10)->end()
          ->Product('Wrench')->setPrice(15)->end()
      ->end()
      ->Category('Web development')
          ->Product('zicht/z')->setPrice(0)->end()
          ->Product('zicht/framework-extra-bundle')->setPrice(0)->end()
      ->end()
  ->end();
```

See the [zicht/cms-tutorial](https://github.com/zicht/cms-tutorial) for working
examples.
