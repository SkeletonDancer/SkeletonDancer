# Service injection

Generators, Configurators and ExpressionLanguage FunctionProviders all support
service-injection for dependency management.
But what is exactly is service-injection and how do you use it?

Simple, if you already know the concept of [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection)
this should be pretty straightforward, if not please see [Pimple - Dependency Injection Container](http://pimple.sensiolabs.org/)
to get an idea of how it all works.

## Working with services

Say that your Generator needs to write content to a file, you could use the PHP native file-functions (fopen, fwrite)
but SkeletonDancer provides something better, a `Filesystem` service.

A service in SkeletonDancer is nothing more then a class registered in the Pimple service-container with a specific name.

Take the `ReadMeGenerator` as an example, the class constructor defines two
arguments `twig` and `filesystem`. When initializing the `ReadMeGenerator` SkeletonDancer will automatically
look for the argument names in service-container and pass them to the class-constructor.

```php
namespace Rollerworks\Tools\SkeletonDancer\Generator;

use Rollerworks\Tools\SkeletonDancer\Configurator\GeneralConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class ReadMeGenerator implements Generator
{
    private $filesystem;
    private $twig;

    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    public function generate(array $configuration)
    {
        $this->filesystem->dumpFile(
            'README.md',
            $this->twig->render(
                'readme.md.twig',
                [
                    'name' => $configuration['name'],
                    'packageName' => $configuration['package_name'],
                    'phpMin' => $configuration['php_min'],
                    'authorName' => $configuration['author_name'],
                ]
            )
        );
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class];
    }
}
```

That's it, you now know how to use service-injection!
But you are not limited to these two services, SkeletonDancer comes with a ridge list
of services to use.

## Services

SkeletonDancer defines the following list of services for usage in Generators, Configurators and
ExpressionLanguage FunctionProviders.

The container actually defines more services then listed here, but they should not
be used as they are considered private/internal.

**Note:** A service-name with under_scores is transformed to camelCase.

### config

The `config` service provides access to the loaded configuration.

Class: `Rollerworks\Tools\SkeletonDancer\Configuration\Config`

### style

The `style` service allows to display information messages and interact with the user.

**Caution:** Use this service only when it's absolutely necessary to display information,
or communicate outside of the conventional Configurators.

Class: `Symfony\Component\Console\Style\SymfonyStyle`

### class_initializer

The `class_initializer` allows to create a new class instances with service-injection
applied on the new class (this service is also used for initializing configurators, generators, etc)

Class: `Rollerworks\Tools\SkeletonDancer\ClassInitializer`

### twig

The `twig` service allows to render twig templates.

Class: `\Twig_Environment`

### process

The `process` service allows to perform system operations
using the Symfony Process component.

Class: `Rollerworks\Tools\SkeletonDancer\Service\CliProcess`

**Note:** Some conventions are applied for ease of use
(current working directory is already set and output is not required).

### composer

The composer service allows to manipulate the Composer listed-packages.

Class: `Rollerworks\Tools\SkeletonDancer\Service\Composer`

### git

The `git` service allows to retrieve information from the Git repository,
and set configuration (remote information only at the moment).

Class: `Rollerworks\Tools\SkeletonDancer\Service\Git`

### filesystem

The `filesystem` service allows to safely write (dump) files and create
directory structures.

By default all operations that don't use an absolute path are performed in the current working directory.
But it's possible to use a so-called directory pointer (eg. `@projectDir/`) to point another directory.

The `filesystem` service provides the following directory pointer:

* `@currentDir`: The current working directory (where you are running the `generate` command)
* `@projectDir`: The project root-directory (same as the current-directory when no root-directory was found)
* `@dancerDir`:  The `.dancer` where you configuration files are kept, this location may very per project and can be empty.
 When the value is empty and used by a generator an exception is throw.

*Caution:** Directory pointers only work in the beginning of the path,
and forbids the usage of `../` for parent directories!

Example: `@projectDir/projects.txt` will be transformed to eg. `/home/username/my-project/projects.txt`

Class: `Rollerworks\Tools\SkeletonDancer\Service\Filesystem`

### expression_language

The `expression_language` service provides the Symfony ExpressionLanguage as service.
You should not need to use this service as answers can already by dynamic and are resolved,
before being passed to a generator.

**Note:** Do not use this service for registering FunctionProviders,
use the `expression_language.function_providers` option instead.

Class: `Symfony\Component\ExpressionLanguage\ExpressionLanguage`

### container

The `container` is not actually a service, it provides access to the service-container
for when you need access to Container parameters or need to pass it to custom services.
