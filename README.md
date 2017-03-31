# Render
> Lightweight PHP Renderer

## Installation
```shell
composer require liquidsoft/render
```

## Setup
**render()->setOptions(array $options)**

```php
render()->setOptions([
    // Path to the view sources
    'source' => 'resources/views',
    
    // View namespace (for extending view classes)
    'namespace' => 'App\Views'
]);
```

## Render a view
**render(string $view, array $arguments = [])**

The views can be renderer anywhere in the response and can be nested.
```php
// The partials.header will translate to including
// the file partials/header.php relative to the source set at setup
render('partials.header', ['title' => 'My site']);
```

## Retrieving an argument
**argument(string $query, mixed $default = null)**

Arguments are scoped hierarchically by the service and returns the closest defined to the current view.

```php
// index.php
render('partials.header', ['title' => 'My site', 'tagline' => 'Welcome']);
echo argument('title'); // Output 'My site'

// partials/header.php
render('partials.header.brand', ['title' => 'My awesome site']);

// partials/header/brand.php
echo argument('title'); // Output 'My awesome site'
echo argument('tagline'); // Output 'Welcome'
```

## Extending a view
The views class can be extended for each of the views in the source folder.
The classes must be located under the defined namespace and each folder must constitute a namespace node.

There are 2 hooks which can be extended:
- beforeRender()
- afterRender()

```php
// app/Views
namespace App\Views\Partials;

use LiquidSoft\Render\View;

// will map to 'resources/views/partials/header.php'
class HeaderView extends View {
    beforeRender() {
        //...
    }
}

// app/Views/Partials/Header;
namespace App\Views\Partials\Header;

// will map to 'resources/views/partials/header/brand.php'
class BrandView extends View {
    afterRender() {
        //...
    }
}
```