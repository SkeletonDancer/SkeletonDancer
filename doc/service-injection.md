# Service injection

Questions and Generators by themselves allow for great flexibility, but using
PHP classes alone would be pretty boring.

Fortunately comes with a powerful service-injection autowiring system.
If you already know the concept of [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection)
this should be pretty straightforward.

If not please see [Pimple - Dependency Injection Container](http://pimple.sensiolabs.org/) to get an idea of 
how it all works.

## Working with services

Say that your Generator needs to write to a file, you could use the PHP native functions (fopen, fwrite)
but SkeletonDancer provides something better, a `Filesystem` service.

A service in SkeletonDancer is nothing more then a class registered in the service-container, you can inject 
these services using the class constructor specifying the service-name as argument name (eg. `filesystem`).

**Note:** A service-name with under_scores is transformed to camelCase for the constructor.

Take the `ReadMeGenerator` as an example, the class constructor defines two arguments `twig` and `filesystem`. 
When initializing the `ReadMeGenerator` SkeletonDancer will automatically look-up the argument names in 
service-container and pass them to the class-constructor.

```php
<?php

namespace Acme\Generator;

use SkeletonDancer\Generator;
use SkeletonDancer\Service\Filesystem;

final class ReadMeGenerator implements Generator
{
    private $filesystem;
    private $twig;

    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    public function generate(array $answers)
    {
        // The Filesystem operates relative to the project's directory
        $this->filesystem->dumpFile(
            'README.md',
            // Templates are looked-up from the `templates/` directory for the selected dance
            $this->twig->render(
                'readme.md.twig',
                [
                    'name' => $answers['name'],
                    'packageName' => $answers['package_name'],
                    'authorName' => $answers['author_name'],
                ]
            )
        );
    }
}
```

That's it, you now know how to use service-injection!
But you are not limited to these two services, SkeletonDancer comes pre-bundled with many
useful services.

### Registering services

You can register your own using the file-based [autoloading](autoloading.md) system.

**.dance.json**:

```json
{
    "autoloading": {
        "files": "services.php"
    }
}
```

**services.php:**

```php
<?php

// A service-name must be usable as a PHP variable name,
// underscores are are converted to camelCase in the constructor.
//
// Existing services cannot be overwritten.
$this->container['service_name'] = function ($container) {};
```

See also: http://pimple.sensiolabs.org/

## Provided Services

SkeletonDancer defines the following list of services for usage in Questioners and Generators.

The container actually defines more services then listed here, but those should not
be used as they are considered private/internal.

### style

The `style` service allows to display information messages and interact with the user.

**Caution:** Use this service only when it's absolutely necessary to display information,
or communicate outside of the conventional Questioners.

Class: `Symfony\Component\Console\Style\SymfonyStyle`

### class_initializer

The `class_initializer` allows to create a new class instances with service-injection
applied on the new class (this service is also used for initializing Questioners and Generators).

Class: `SkeletonDancer\ClassInitializer`

### twig

The `twig` service allows to render twig templates.

Class: `\Twig_Environment`

### process

The `process` service allows to perform system operations
using the Symfony Process component.

Class: `SkeletonDancer\Service\CliProcess`

**Note:** Some conventions are applied for ease of use
(current working directory is already set and output is not required).

### git

The `git` service allows to retrieve information from the Git repository,
and set configuration (local only).

Class: `SkeletonDancer\Service\Git`

### filesystem

The `filesystem` service allows to safely write (dump) files and create directory structures.

By default all operations that don't use an absolute path are performed in the current working directory.

Class: `SkeletonDancer\Service\Filesystem`

### container

The `container` is not actually a service, it provides access to the service-container
for when you need access to Container parameters or need to configure custom services.
