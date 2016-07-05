<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Tests\Configuration;

use Rollerworks\Tools\SkeletonDancer\Configuration\AutomaticProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configuration\InteractiveProfileResolver;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\IO\InputStream\NullInputStream;
use Webmozart\Console\IO\OutputStream\NullOutputStream;

final class InteractiveProfileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var InteractiveProfileResolver
     */
    private $resolver;

    /**
     * @var IO
     */
    private $io;

    private $input;

    /**
     * @var StreamOutput
     */
    private $output;

    /**
     * @before
     */
    public function setUpResolver()
    {
        $this->io = new IO(new Input(new NullInputStream()), new Output(new NullOutputStream()), new Output(new NullOutputStream()));
        $this->io->setInteractive(true);

        $this->config = new Config(['current_dir_relative' => 'src/Bundle/MyBundle']);
        $this->config->set('profiles', ['bundle' => [], 'library' => []]);
    }

    /** @test */
    public function it_uses_the_auto_guessed_profile_as_default()
    {
        $this->config->set(
            'profile_resolver',
            [
                'src/Bundle/' => 'bundle',
            ]
        );

        $this->createResolver(["\n"]);

        $this->assertEquals('bundle', $this->resolver->resolve());
    }

    /** @test */
    public function it_accepts_the_given_choice_as_profile()
    {
        $this->config->set(
            'profile_resolver',
            [
                'src/Bundle/' => 'bundle',
            ]
        );

        $this->createResolver(['0']);

        $this->assertEquals('bundle', $this->resolver->resolve());
    }

    /** @test */
    public function it_informs_when_passed_profile_is_unregistered()
    {
        $this->createResolver(['0']);

        $this->resolver->resolve('foo');

        $this->assertOutputMatches(
            'Profile "foo" is not registered, please use one of the following: bundle, library.'
        );
    }

    private function createResolver(array $input = [])
    {
        $this->resolver = new InteractiveProfileResolver(
            $this->config,
            $this->createStyle($input),
            $this->io,
            new AutomaticProfileResolver($this->config)
        );
    }

    /**
     * @param array $input
     *
     * @return SymfonyStyle
     */
    private function createStyle(array $input = [])
    {
        $this->input = new ArrayInput([]);
        $this->input->setStream($this->getInputStream($input));
        $this->input->setInteractive(true);

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        $this->output->setDecorated(false);

        return new SymfonyStyle($this->input, $this->output);
    }

    private function getInputStream(array $input)
    {
        $input = implode(PHP_EOL, $input);

        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }

    /**
     * Gets the display returned by the last execution of the command.
     *
     * @return string The display
     */
    private function getDisplay()
    {
        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());
        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }

    private function assertOutputMatches($expectedLines, $regex = false)
    {
        $output = preg_replace('/\s!\s/', ' ', trim($this->getDisplay()));
        $expectedLines = (array) $expectedLines;

        foreach ($expectedLines as $matchLine) {
            if (is_array($matchLine)) {
                list($line, $lineRegex) = $matchLine;
            } else {
                $line = $matchLine;
                $lineRegex = $regex;
            }

            if (!$lineRegex) {
                $line = preg_replace('#\s+#', '\\s+', preg_quote($line, '#'));
            }

            $this->assertRegExp('#'.$line.'#m', $output);
        }
    }
}
