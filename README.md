# :skull: :dancers: SkeletonDancer - PHP Project bootstrapping

SkeletonDancer is a skeleton generator - to help you safe time
and get started faster.

> A skeleton is directory structure with providing only the most basic content 
> to get started. This can include a README with standard instructions, 
> the src and tests directories, and anything you wish to be generated.

*SkeletonDancer is still in alpha quality, some things maybe broken.
Please try it and report any problem you encounter.*

**Note:** For front-end you properly want to use something like Yeoman,
as SkeletonDancer is mainly focused on PHP.

## How it works

SkeletonDancer works with predefined dances, each dance describes
how a skeleton must be generated (*how it should dance*).

A dance contains questions like the projects name, author,
license, etc; or anything that your generators need. And a 
list generators.

Both the Questioners and Generators are fully-fledged PHP classes
to provide complete flexility for any use-case.

**Tip:** Some answers can be pre-filled using previous answers 
or local configuration like Git `author.email` for example.

### Special services

For generators SkeletonDancer comes pre-bundled support for 
Twig templating, File operations, Git repository generation, 
and Console operations (using the Symfony Process Component).

Questioners and Generators support autoloading and service autowiring.
And, ready to use integration for PHPUnit (with fully isolated testing).

## Installation

*SkeletonDancer is not something you install as an dependency, 
SkeletonDancer operates outside of your project.*

Assuming you know how to use Git and Composer.

**Note:** Windows users are encouraged to use the Git shell,
all these examples expect you are using a Unix-based Shell.

Go to a location where you keep all your files, eg. `~/Sites/`.

```bash
$ git clone https://github.com/SkeletonDancer/SkeletonDancer.git
$ cd SkeletonDancer
$ php composer.phar install
```

Now create an alias for the command, you can get the current directory
with the `pwd` command.

Set the alias in `~/.bashrc` to make sure it's always
set when you logon (or open the git shell).

```bash
alias skeleton-dancer="php ~/Sites/SkeletonDancer/src/skel-dancer.php"
```

## Basic usage

*More advanced documentation is planned.*

### Install a dance

First you need to install a dance, a dance is kept in a GitHub repository.
But you can also use local dances if you plan to use SkeletonDancer for eg.
a monolith development repository.

```bash
$ skeleton-dancer install SkeletonDancer/php-pds
```

This will install (git clone) the https://github.com/SkeletonDancer/php-pds.dance repository.

### Creating a new project

To create a new project, first create an empty directory
somewhere on your computer (like your home directory (`~/`)).

```bash
$ cd ~/
$ mkdir my-project
$ cd my-project
$ skeleton-dancer dance SkeletonDancer/php-pds
```

Now SkeletonDancer will ask a number of questions and get the skeleton
dancing; Once done your skeleton is ready for usage!

**Note:** SkeletonDancer doesn't overwrite existing files by default, 
use the `--force-overwrite` option if want to discard existing files.

Run `skeleton-dancer help` for a complete overview of all commands and options.
Use the `list` command to get a complete list of all the installed dances.

## Versioning

For transparency and insight into the release cycle, and for striving
to maintain backward compatibility, this package is maintained under
the Semantic Versioning guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

For more information on SemVer, please visit <http://semver.org/>.

## Who is behind SkeletonDancer?

SkeletonDancer is brought to you by [Sebastiaan Stok](https://github.com/sstok).
SkeletonDancer is released under the [MIT license](LICENSE).

The name of this project was inspired on the song Skeleton Dance by 
heavy metal band Running Wild, album: Rogues en Vogue.
