# :skull: :dancers: SkeletonDancer - PHP Project bootstrapping

SkeletonDancer is a (project) skeleton generator.

> A skeleton is a directory structure with only the most basic content 
> to get started. This can include a README file with standard instructions, 
> the src and tests directories, or anything you wish to provided.

*SkeletonDancer is still in alpha quality, some things maybe broken.
Please try it and report any problem you encounter.*

## How it works

SkeletonDancer works with Questions and Dances, each dance describes how
the skeleton structure must be generated (*or how it should dance*) based on
the questions.

In practice both the Questioners and Generators are plain PHP classes,
and can use some of the provided services for template rendering or
guessing answers based on the local Git author configuration.

Plus, SkeletonDancer provides integration testing for PHPUnit (with fully isolated testing).

## Installation

*SkeletonDancer is not something you install as a dependency, 
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

First you need to install a dance. Which can be either a GitHub repository
or Git providing repository. Also, you might use project-local dances.

```bash
$ skeleton-dancer install https://github.com/SkeletonDancer/php-pds.dance
```

This will install (git clone) the https://github.com/SkeletonDancer/php-pds.dance repository.

### Creating a new project skeleton

To create a new project, first create an empty directory
somewhere on your computer (like your home directory (`~/`)).

```bash
$ cd ~/
$ mkdir my-project
$ cd my-project
$ skeleton-dancer dance SkeletonDancer/php-pds
```

Now SkeletonDancer will ask a number of questions and get the skeleton
dancing; Once done your project skeleton is ready for usage!

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
