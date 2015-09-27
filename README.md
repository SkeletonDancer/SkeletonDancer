SkeletonDancer
==============

:skull: :dancers: SkeletonDancer - PHP Project bootstrapping

A really simple (not completed) PHP project scaffolding tool.

*I got tired of having to copy-past and mess-up my configuration for every project,
so I created something really simple cli tool using the Symfony Console and Twig.*

Whats with the name?
--------------------

Empty projects are sometimes revert to as 'skeletons', this little tools makes
them dance :smile: And it was the only name, I could came-up with on the weekend.

Requirements
------------

You need at least PHP 5.5 and Composer.

Installation
------------

*This is not something you install as an dependency.*

Assuming you know how to use Git and Composer.

**Note:** Windows users are encouraged to use the Git shell,
all these examples assume you are using a Unix-based Shell.

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
alias dancer="php /path/to/SkeletonDancer/src/skel-dancer.php"
```

Basic usage
-----------

To create a new project, first create an empty directory
somewhere on your computer (like the /home folder).

```bash
$ mkdir my-project
$ cd my-project
$ dancer create
```

Now Skeleton dancer will ask for a number of questions,
after this your project is created.

**Note:** Some values are guested on previous values
and may not suite your needs. Feel free to change them,
or suggest improvements (by open an issue).

Screenshot
----------

![skeleton-dancer-shell](https://cloud.githubusercontent.com/assets/904790/10122427/eb25069c-6519-11e5-8d7a-a3517228fe8a.png)

Versioning
----------

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

License
-------

The package is provided under the none-restrictive MIT license,
you are free to use it for any free or proprietary product/application,
without restrictions.

The templates for creating Symfony bundles were originally
copied from the SensioGeneratorBundle.

[LICENSE](LICENSE)
