<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer;

use Rollerworks\Tools\SkeletonDancer\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class GenerateCommand extends Command
{
    private static $types = [
        'library',
        'extension',
        'symfony-integration-bundle',
        'symfony-package-bundle',
        'project',
    ];

    protected function configure()
    {
        $this
            ->setName('generate')
            ->setAliases(['dance', 'new'])
            ->setDescription('Generates an empty project in the current directory')
            ->addOption('no-git', null, InputOption::VALUE_NONE, 'Do not generate the .git configuration')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('SkeletonDancer - PHP Project bootstrapping');

        if (!$input->isInteractive()) {
            $style->error('This command can only be run in interactive mode.');

            return 1;
        }

        $iterator = new \FilesystemIterator(getcwd());

        if ($iterator->valid()) {
            $style->warning(['The working directory is not empty!', 'If you continue you may loose existing files.']);

            if (!$style->confirm('Do you want to continue?', false)) {
                $style->error('Aborted.');

                return 1;
            }
        }

        // Todo:
        // * Detect if Bundle suffix is already present for the Bundle-name; ParkManagerRouteAutoWiringBundleBundle
        // * Auto enable Symfony testing bridge for bundles.

        $style->block('Before we start I need to know something about your project, please fill in all the questions.');

        $information = [];
        $information['type'] = $style->choice('Type', self::$types);
        $information['name'] = $style->ask('Name');

        if (preg_match('/^(?P<vendor>[a-z0-9_.-]+)\s+(?P<name>[a-z0-9_.-]+)$/i', $information['name'], $regs)) {
            $packageName = strtolower($this->humanize($regs[1]).'/'.$this->humanize($regs[2]));
        } else {
            $packageName = '';
        }

        $information['package-name'] = $style->ask(
            'Package name (<vendor>/<name>)',
            $packageName,
            function ($name) {
                if (!preg_match('{^[a-z0-9_.-]+/[a-z0-9_.-]+$}', $name)) {
                    throw new \InvalidArgumentException(
                        'The package name '.
                        $name.
                        ' is invalid, it should be lowercase and have a vendor name, a forward slash, and a package name, matching: [a-z0-9_.-]+/[a-z0-9_.-]+'
                    );
                }

                return $name;
            }
        );

        $information['namespace'] = $style->ask('PHP Namespace');
        $information['author'] = $style->ask('Author of the project', 'Sebastiaan Stok <s.stok@rollerscapes.net>');
        $information['php-min'] = $style->ask('Php-min', '7.0');
        $information['doc-format'] = $style->choice('Documentation format', ['rst', 'markdown', 'none']);

        // Testing frameworks
        $information['enable-phpunit'] = $style->confirm('Enable PHPUnit?');
        $information['enable-phpspec'] = $style->confirm('Enable PHPSpec?', false);
        $information['enable-behat'] = $style->confirm('Enable Behat?', false);

        if ($this->isSfBundle($information['type'])) {
            $style->block('Please provide the additional information for the symfony bundle.');

            $bundleName = strtr($information['namespace'], ['\\Bundle\\' => '', '\\' => '']);
            $bundleName .= substr($bundleName, -6) === 'Bundle' ? '' : 'Bundle';

            $information['bundle-name'] = $style->ask('Bundle name', $bundleName);
            $information['bundle-config-format'] = $style->choice('Configuration format', ['yml', 'xml'], 'xml');
        }

        if ($information['enable-phpunit']) {
            if ($this->isSfBundle($information['type'])) {
                $information['enable-sf-test-bridge'] = true;
            } elseif ($style->askQuestion(new ConfirmationQuestion('Enable Symfony PHPUnit bridge'))) {
                $information['enable-sf-test-bridge'] = true;
            }
        } else {
            $information['enable-sf-test-bridge'] = false;
        }

        $filesystem = new Filesystem();
        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem(__DIR__.'/../Resources/Templates'),
            [
                'debug' => true,
                'cache' => new \Twig_Cache_Filesystem(__DIR__.'/../temp'),
                'strict_variables' => true
            ]
        );

        $twig->addFunction(
            new \Twig_SimpleFunction(
                'doc_header',
                function ($value, $format) {
                    return $value."\n".str_repeat($format, strlen($value));
                }
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'normalizeNamespace',
                function ($value) {
                    return str_replace('\\\\', '\\', $value);
                },
                ['is_safe' => ['html', 'yml']]
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'escape_namespace',
                'addslashes',
                ['is_safe' => ['html', 'yml']]
            )
        );

        $workingDir = getcwd();

        $style = new SymfonyStyle($input, $output);
        $style->text('Start dancing, this may take a while...');

        (new Generator\ComposerGenerator($twig, $filesystem))->generate(
            $information['package-name'],
            $information['namespace'],
            $information['type'],
            'MIT',
            $information['author'],
            $information['php-min'],
            $information['enable-sf-test-bridge'],
            $information['enable-phpunit'],
            $information['enable-phpspec'],
            $information['enable-behat'],
            $workingDir
        );

        (new Generator\ReadMeGenerator($twig, $filesystem))->generate(
            $information['name'],
            $information['package-name'],
            $information['php-min'],
            $workingDir
        );

        (new Generator\LicenseGenerator($twig, $filesystem))->generate(
            $information['name'],
            $information['author'],
            'MIT',
            $workingDir
        );

        (new Generator\PhpCsGenerator($twig, $filesystem))->generate(
            $information['name'],
            $information['author'],
            'MIT',
            $workingDir
        );

        if (!$input->getOption('no-git')) {
            (new Generator\GushConfigGenerator($twig, $filesystem))->generate(
                $information['name'],
                $information['author'],
                'MIT',
                $workingDir
            );

            (new Generator\GitConfigGenerator($twig, $filesystem))->generate(
                $information['enable-phpunit'],
                $information['enable-phpspec'],
                $information['enable-behat'],
                $information['doc-format'],
                $workingDir
            );
        }

        (new Generator\TestingConfigGenerator($twig, $filesystem))->generate(
            $information['name'],
            $information['namespace'],
            $information['enable-phpunit'],
            $information['enable-phpspec'],
            $information['enable-behat'],
            $workingDir
        );

        (new Generator\TravisConfigGenerator($twig, $filesystem))->generate(
            $information['php-min'],
            $information['enable-phpunit'],
            $information['enable-phpspec'],
            $information['enable-behat'],
            $workingDir
        );

        if ('rst' === $information['doc-format']) {
            (new Generator\SphinxConfigGenerator($twig, $filesystem))->generate(
                $information['name'],
                $workingDir
            );
        }

        if ($this->isSfBundle($information['type'])) {
            (new Generator\SfBundleGenerator($twig, $filesystem))->generate(
                $information['name'],
                $information['namespace'],
                $information['bundle-name'],
                $information['bundle-config-format'],
                $workingDir
            );
        }

        $style->success('Done, enjoy!');
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function isSfBundle($type)
    {
        return 0 === strpos($type, 'symfony-');
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function humanize($text)
    {
        return trim(ucfirst(trim(strtolower(preg_replace(['/((?<![-._])[A-Z])/', '/[\s]+/'], ['-$1', '-'], $text)))), '-');
    }
}
