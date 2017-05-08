# :skull: :dancers: SkeletonDancer - PHP Project bootstrapping

SkeletonDancer is a powerful project generator, helping you to get ready
for programming rather then wasting time with copying everything by hand.

*SkeletonDancer is still in alpha quality, some things maybe broken.
Please try it and report any problem you encounter.*

## How it works

SkeletonDancer works with predefined dances, each dance describes
how a skeleton must be generated (*how it should dance*).

A dance can ask questions like the projects name, special options,
and things that are needed for the generation process.

**Tip:** Default answers can be based on previous answers or services (Git author.email for example).

Instead of always using templates SkeletonDancer allows you to
create fully-fledged generator classes in PHP, perfect for any use-case.
 
**Out of the box SkeletonDancer supports Twig templating, File operations 
Git repository generation, and Console operations.**

Questioners and Generators both support autoloading and service autowiring.
And, ready to use integration for PHPUnit (with fully isolated testing).

## Installation

*This is not something you install as an dependency, it operates
outside of your projects.*

Assuming you know how to use Git and Composer.

**Note:** Windows users are encouraged to use the Git shell,
all these examples assume you are using a Unix-based Shell.
*Mac users are advised to use iTerm2.*

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
$ skeleton-dancer dance
```

Now SkeletonDancer will ask a number of questions (including which dance), after this your project 
is created and ready for usage!

**Note:** SkeletonDancer doesn't overwrite existing files by default, use the `--force-overwrite`
option if want to discard existing files.

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
