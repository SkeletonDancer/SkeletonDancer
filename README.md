# :skull: :dancers: SkeletonDancer - PHP Project bootstrapping

Tired of copying pasting your template project folders?
Create (bootstrap) a new PHP project/library skeleton within no-time.

To use SkeletonDancer you need at least PHP 5.5,
and have your PATH-env set-up properly as some generators
require external applications.

*SkeletonDancer is still in alpha quality, some things maybe broken.
Please try it and report any problem you encounter.*

## What's it about?

SkeletonDancer is designed for the lazy developer who just
wants to get started instead of wasting time on copy-pasting,
and then fixing search-replace typo's.

Plus if you have a project that consist of many
sub-projects or libraries, you can pre-configure
the common answers for re-use.

Pre-configured answers support the Symfony ExpressionLanguage
for fully dynamic answers (Eg. *use the directory path as namespace
and the current-directory name as sub-project name.*).

## How it works

SkeletonDancer works with profiles that can have
one or more generators (which in turn have Configurators for asking
questions for the generators).

Generators and Configurators are re-useable PHP classes that are
shared between profiles.

SkeletonDancer comes bundles with generators for Symfony,
Composer, Travis, php-cs-fixer, and many more.

Plus you can easily add you own generators and/or templates.

<!--
### Example

Checkout the [Park-Manager](https://github.com/park-manager/park-manager)
SkeletonDancer configuration and specific generators for an advanced example.

Generating a Park-Manager module only requires a single command `dancer.phar generate`
in the directory Module's directory (eg. `src/Module/Webhosting`).
And every answer it auto-filled!
-->

## Installation

*This is not something you install as an dependency, it operates
outside of your project.*

Assuming you know how to use Git and Composer.

**Note:** Windows users are encouraged to use the Git shell,
all these examples assume you are using a Unix-based Shell.
*Mac users are advised to use iTerm.*

Go to a location where you keep all your files,
eg. `~/Sites/`.

```bash
$ git clone https://github.com/rollerworks/SkeletonDancer.git
$ cd SkeletonDancer
$ php composer.phar install
```

Now create an alias for the command, you can get the current directory
with the `pwd` command.

Set the alias in `~/.bashrc` to make sure it's always
set when you logon (or open the git shell).

```bash
alias dancer="php ~/Sites/SkeletonDancer/src/skel-dancer.php"
```

## Basic usage

*More advanced documentation is planned.*

To create a new project, first create an empty directory
somewhere on your computer (like the /home folder).

```bash
$ mkdir my-project
$ cd my-project
$ dancer generate
```

Now SkeletonDancer will ask a number of questions,
after this your project is created.

**Note:** Some values are guested on previous values
and may not suite your needs. Feel free to change them,
or suggest improvements (by opening an issue).

**Tip:** Don't worry about overwriting existing files,
they are automatically detected and you are asked what to do.

Use `dancer help to a complete overview of all commands and options`.

**Tip:** Some information is hidden, use the `-v` option to show
all details (config file used and defaults values in the profile command).

## Listing profiles

Use the `dancer profile` command to get a complete list
of all the profiles (in your current project).

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

Some code and templates were bowered from
[SensioGeneratorBundle](https://github.com/sensiolabs/SensioGeneratorBundle/).
